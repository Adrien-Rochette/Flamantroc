<?php
declare(strict_types=1);

final class Router
{
    private array $routes = [
        'GET' => [],
        'POST' => [],
    ];

    public function get(string $path, callable|array $handler): void
    {
        $this->routes['GET'][$this->normalize($path)] = $handler;
    }

    public function post(string $path, callable|array $handler): void
    {
        $this->routes['POST'][$this->normalize($path)] = $handler;
    }

    public function dispatch(): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path = $this->currentPath();
        $handler = $this->routes[$method][$path] ?? null;

        if ($handler === null) {
            $this->sendNotFound();
            return;
        }

        if (is_callable($handler)) {
            call_user_func($handler);
            return;
        }

        if (is_array($handler) && count($handler) === 2) {
            [$controllerClass, $action] = $handler;
            $controller = new $controllerClass();

            if (!method_exists($controller, $action)) {
                throw new RuntimeException('Action introuvable: ' . $action);
            }

            $controller->$action();
            return;
        }

        throw new RuntimeException('Route invalide: ' . $path);
    }

    private function currentPath(): string
    {
        if (isset($_GET['route']) && is_string($_GET['route'])) {
            return $this->normalize($_GET['route']);
        }

        $uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $uriPath = '/' . trim($uriPath, '/');
        $uriPath = $uriPath === '/' ? '/' : rtrim($uriPath, '/');

        if (BASE_URL !== '' && str_starts_with($uriPath, BASE_URL)) {
            $uriPath = substr($uriPath, strlen(BASE_URL));
            $uriPath = $uriPath === '' ? '/' : $uriPath;
        }

        return $this->normalize($uriPath);
    }

    private function normalize(string $path): string
    {
        $normalized = '/' . trim($path, '/');
        return $normalized === '/' ? '/' : rtrim($normalized, '/');
    }

    private function sendNotFound(): void
    {
        http_response_code(404);
        echo '<h1>404 - Page non trouvee</h1>';
    }
}
