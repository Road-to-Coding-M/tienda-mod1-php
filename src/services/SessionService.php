<?php
namespace services;

class SessionService {
    // Only one SessionService instance exists
    private static $instance = null;
    // Set session expiration to 3600 seconds (1 hour).
    private $expireAfterSeconds = 3600;

    // After logging in, the session remembers it across all pages.
    private function __construct() {
        if (session_status() === PHP_SESSION_NONE) session_start();
        // Expiración >3600s (inactividad)
        $now = time();
        if (isset($_SESSION['last_activity']) && ($now - $_SESSION['last_activity']) > $this->expireAfterSeconds) {
            $this->logout();
        }
        
        // If the time since last activity exceeds the expiration limit,
        // the user is automatically logged out.
        $_SESSION['last_activity'] = $now;
        // If 'visits' is not set yet, initialize it to 0.
        if (!isset($_SESSION['visits'])) $_SESSION['visits'] = 0;
        $_SESSION['visits']++;   // Increase the page-visit counter for this session.
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

    
    // clear PHP session data + clear the browser's session cookie.
    public function logout() {
        // Clear all data stored in the PHP session array.
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
    }

    // Check if $_SESSION['loggedIn'] has a value.
    public function isLogged(): bool { return !empty($_SESSION['loggedIn']); }
    public function getUsername(): string { return $this->isLogged() ? ($_SESSION['user']['username'] ?? 'Invitado') : 'Invitado'; }
    public function getNombre(): string { return $this->isLogged() ? ($_SESSION['user']['nombre'] ?? 'Invitado') : 'Invitado'; }
    public function hasRole($role): bool {
        if (!$this->isLogged()) return false;
        $roles = $_SESSION['user']['roles'] ?? [];
        return in_array($role, $roles);
    }

    // getVisits(): return the visit counter stored in the session.
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
