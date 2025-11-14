<?php
namespace services;

use models\User;
use PDO;
use Throwable;

require_once __DIR__ . '/../models/User.php';

class UsersService {
    private PDO $db;
    public function __construct(PDO $db){ $this->db = $db; }

    /** 驗證：支援 pgcrypto 產生的 $2a$（轉成 $2y$ 後再驗證） */
    public function authenticate(string $username, string $password): User {
        $user = $this->findUserByUsername($username);
        if (!$user) {
            throw new \RuntimeException('invalid_credentials');
        }

        $hash = (string)($user->password ?? '');
        if (strncmp($hash, '$2a$', 4) === 0) {
            $hash = '$2y$' . substr($hash, 4); // 兼容 $2a$ → $2y$
        }

        if (!password_verify($password, $hash)) {
            throw new \RuntimeException('invalid_credentials');
        }
        return $user;
    }

    public function findUserByUsername(string $username): ?User {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE username = :u AND is_deleted = FALSE");
        $stmt->execute([':u' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;

        $rolesStmt = $this->db->prepare("SELECT roles FROM user_roles WHERE user_id = :id");
        $rolesStmt->execute([':id' => $row['id']]);
        $roles = array_map(fn($r) => $r['roles'], $rolesStmt->fetchAll(PDO::FETCH_ASSOC));

        // 依你的 User 模型順序建立物件：id, username, password, nombre, apellidos, email, roles, createdAt, updatedAt, isDeleted
        return new User(
            $row['id'] ?? null,
            $row['username'] ?? null,
            $row['password'] ?? null,
            $row['nombre'] ?? null,
            $row['apellidos'] ?? null,
            $row['email'] ?? null,
            $roles,
            $row['created_at'] ?? null,
            $row['updated_at'] ?? null,
            (bool)($row['is_deleted'] ?? false)
        );
    }
}
