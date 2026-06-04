<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    /** @var array<string, array<string, array{0: class-string, 1: string}>> */
    private array $routes = [];

    public function get(string $uri, string $controller, string $method): void
    {
        $this->routes['GET'][$uri] = [$controller, $method];
    }

    public function post(string $uri, string $controller, string $method): void
    {
        $this->routes['POST'][$uri] = [$controller, $method];
    }

    public function put(string $uri, string $controller, string $method): void
    {
        $this->routes['PUT'][$uri] = [$controller, $method];
    }

    public function delete(string $uri, string $controller, string $method): void
    {
        $this->routes['DELETE'][$uri] = [$controller, $method];
    }

    public function dispatch(string $method, string $uri): void
    {
        // Strip query string from URI.
        $uri = parse_url($uri, PHP_URL_PATH) ?: '/';

        if (isset($this->routes[$method][$uri])) {
            [$class, $action] = $this->routes[$method][$uri];
            (new $class())->$action();
            return;
        }

        http_response_code(404);
        echo '404 Not Found';
    }
}
