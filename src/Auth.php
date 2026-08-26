<?php

declare(strict_types=1);

final class Auth
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function login(string $email, string $password): bool
    {
        $sql = 'SELECT id, full_name, email, password_hash, role, is_active FROM users WHERE email = :email LIMIT 1';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || (int) $user['is_active'] !== 1) {
            return false;
        }

        if (!password_verify($password, (string) $user['password_hash'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id' => (int) $user['id'],
            'full_name' => (string) $user['full_name'],
            'email' => (string) $user['email'],
            'role' => (string) $user['role'],
        ];

        return true;
    }

    public static function isLoggedIn(): bool
    {
        return isset($_SESSION['user']['id']);
    }

    public static function requireLogin(): void
    {
        if (!self::isLoggedIn()) {
            header('Location: /index.php');
            exit;
        }
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }
}
