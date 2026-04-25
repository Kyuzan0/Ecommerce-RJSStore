<?php

class Auth
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login(array $user): void
    {
        $guestCart = $_SESSION['cart'] ?? [];
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['cart']      = $guestCart;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public function check(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public function user(): ?array
    {
        if (!$this->check()) {
            return null;
        }
        return [
            'id'   => (int) $_SESSION['user_id'],
            'role' => $_SESSION['user_role'],
            'name' => $_SESSION['user_name'],
        ];
    }

    public function id(): ?int
    {
        return $this->check() ? (int) $_SESSION['user_id'] : null;
    }

    public function isAdmin(): bool
    {
        return $this->check() && $_SESSION['user_role'] === 'admin';
    }

    public function isCustomer(): bool
    {
        return $this->check() && $_SESSION['user_role'] === 'customer';
    }
}
