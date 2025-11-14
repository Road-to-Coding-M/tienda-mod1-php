<?php
namespace services;

class SessionService {
    private static $instance = null;
    private $expireAfterSeconds = 3600;

    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // Expiración simple por inactividad
        $now = time();
        if (isset($_SESSION['last_activity']) && ($now - $_SESSION['last_activity']) > $this->expireAfterSeconds) {
            $this->logout();
        }
        $_SESSION['last_activity'] = $now;
        if (!isset($_SESSION['visits'])) $_SESSION['visits'] = 0;
        $_SESSION['visits']++;
    }

    public static function getInstance(): SessionService {
        if (!self::$instance) self::$instance = new SessionService();
        return self::$instance;
    }

    public function login($user) {
        $_SESSION['loggedIn'] = true;
        $_SESSION['user'] = [
            'id' => $user->id,
            'username' => $user->username,
            'nombre' => $user->nombre,
            'roles' => $user->roles
        ];
        $_SESSION['last_login'] = date('Y-m-d H:i:s');
    }

    public function logout() {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
    }

    public function isLogged(): bool { return !empty($_SESSION['loggedIn']); }
    public function getUsername(): string { return $this->isLogged() ? ($_SESSION['user']['username'] ?? 'Invitado') : 'Invitado'; }
    public function getNombre(): string { return $this->isLogged() ? ($_SESSION['user']['nombre'] ?? 'Invitado') : 'Invitado'; }
    public function hasRole($role): bool {
        if (!$this->isLogged()) return false;
        $roles = $_SESSION['user']['roles'] ?? [];
        return in_array($role, $roles);
    }
    public function getVisits(): int { return $_SESSION['visits'] ?? 0; }
    public function getLastLogin(): ?string { return $_SESSION['last_login'] ?? null; }

    public function setRoles(array $roles): void
    {
        if (!isset($_SESSION['user'])) $_SESSION['user'] = [];
        $_SESSION['user']['roles'] = array_values($roles);
    }

    public function addRole(string $role): void
    {
        if (!isset($_SESSION['user'])) $_SESSION['user'] = [];
        $roles = $_SESSION['user']['roles'] ?? [];
        if (!in_array($role, $roles, true)) {
            $roles[] = $role;
        }
        $_SESSION['user']['roles'] = $roles;
    }

    public function removeRole(string $role): void
    {
        if (!isset($_SESSION['user'])) $_SESSION['user'] = [];
        $roles = array_values(array_filter($_SESSION['user']['roles'] ?? [], fn($r) => $r !== $role));
        $_SESSION['user']['roles'] = $roles;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('ADMIN');
    }

    public function toggleAdmin(): bool
    {
        if ($this->isAdmin()) {
            $this->removeRole('ADMIN');
            return false;
        } else {
            $this->addRole('ADMIN');
            return true;
        }
    }

}
