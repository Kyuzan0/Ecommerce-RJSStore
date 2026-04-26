<?php

class Router
{
    private array $routes = [];

    public function get(string $path, string $controller, string $action): void
    {
        $this->routes[] = [
            'method'     => 'GET',
            'path'       => trim($path, '/'),
            'controller' => $controller,
            'action'     => $action,
        ];
    }

    public function post(string $path, string $controller, string $action): void
    {
        $this->routes[] = [
            'method'     => 'POST',
            'path'       => trim($path, '/'),
            'controller' => $controller,
            'action'     => $action,
        ];
    }

    public function dispatch(string $uri, string $method): void
    {
        // Strip query string
        $uri = parse_url($uri, PHP_URL_PATH);

        // Strip base path so routes are relative to the app root.
        // On shared hosting with a bridge index.php, SCRIPT_NAME may be
        // "/index.php" (basePath "/") or "/public/index.php" (basePath
        // "/public"). Only strip when the URI actually starts with it.
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath !== '/' && $basePath !== '\\' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        $uri = trim($uri, '/');

        // 1. Try explicit routes first
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchRoute($route['path'], $uri);
            if ($params !== false) {
                $this->callController($route['controller'], $route['action'], $params);
                return;
            }
        }

        // 2. Convention-based routing
        $segments = $uri === '' ? [] : explode('/', $uri);

        $controllerSlug = $segments[0] ?? '';
        $action         = $segments[1] ?? 'index';
        $params         = array_slice($segments, 2);

        // Convert slug to controller class name
        // e.g. 'admin-produk' → 'AdminProdukController'
        // e.g. 'auth' → 'AuthController'
        // e.g. '' → 'HomeController'
        if ($controllerSlug === '') {
            $controllerClass = 'HomeController';
        } else {
            $parts = explode('-', $controllerSlug);
            $controllerClass = implode('', array_map('ucfirst', $parts)) . 'Controller';
        }

        $this->callController($controllerClass, $action, $params);
    }

    private function matchRoute(string $routePath, string $uri): array|false
    {
        $routeParts = explode('/', $routePath);
        $uriParts   = explode('/', $uri);

        // Handle empty route matching empty URI
        if ($routePath === '' && $uri === '') {
            return [];
        }

        if (count($routeParts) !== count($uriParts)) {
            return false;
        }

        $params = [];
        for ($i = 0; $i < count($routeParts); $i++) {
            if (str_starts_with($routeParts[$i], ':')) {
                $params[] = $uriParts[$i];
            } elseif ($routeParts[$i] !== $uriParts[$i]) {
                return false;
            }
        }

        return $params;
    }

    private function callController(string $controllerClass, string $action, array $params = []): void
    {
        $file = BASE_PATH . '/app/controllers/' . $controllerClass . '.php';

        if (!file_exists($file)) {
            $this->notFound();
            return;
        }

        require_once $file;

        if (!class_exists($controllerClass)) {
            $this->notFound();
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            $this->notFound();
            return;
        }

        call_user_func_array([$controller, $action], $params);
    }

    private function notFound(): void
    {
        http_response_code(404);
        echo '<!DOCTYPE html><html><head><title>404 Not Found</title>';
        echo '<script src="https://cdn.tailwindcss.com"></script>';
        echo '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">';
        echo '</head><body class="bg-gray-50 min-h-screen flex items-center justify-center" style="font-family:Inter,sans-serif">';
        echo '<div class="text-center"><h1 class="text-6xl font-bold text-gray-300 mb-4">404</h1>';
        echo '<p class="text-gray-500 mb-6">Halaman tidak ditemukan</p>';
        echo '<a href="' . url('/') . '" class="inline-block px-6 py-3 text-white rounded-xl font-semibold hover:opacity-90 transition" style="background:#42B549">Kembali ke Beranda</a>';
        echo '</div></body></html>';
        exit;
    }
}
