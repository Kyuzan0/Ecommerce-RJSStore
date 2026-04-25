<?php

class BaseController
{
    protected Database $db;
    protected Auth $auth;

    public function __construct()
    {
        $this->db   = Database::getInstance();
        $this->auth = new Auth();
    }

    protected function view(string $viewPath, array $data = [], string $layout = 'main'): void
    {
        extract($data);

        ob_start();
        require BASE_PATH . '/app/views/' . $viewPath . '.php';
        $content = ob_get_clean();

        require BASE_PATH . '/app/views/layouts/' . $layout . '.php';
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . url($path));
        exit;
    }

    protected function requireAuth(?string $role = null): void
    {
        if (!$this->auth->check()) {
            $next = $_SERVER['REQUEST_URI'] ?? '';
            $this->redirect('/auth/login' . ($next ? '?next=' . urlencode($next) : ''));
            return;
        }
        if ($role !== null && $this->auth->user()['role'] !== $role) {
            http_response_code(403);
            echo '403 Forbidden';
            exit;
        }
    }

    protected function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '405 Method Not Allowed';
            exit;
        }
    }

    protected function csrfValidate(): void
    {
        if (!csrf_validate()) {
            flash('error', 'Token keamanan tidak valid. Silakan coba lagi.');
            $referer = $_SERVER['HTTP_REFERER'] ?? url('/');
            header('Location: ' . $referer);
            exit;
        }
    }
}
