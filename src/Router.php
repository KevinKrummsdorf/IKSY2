<?php
namespace App;

class Router
{
    private array $routes = [];

    public function add(string $path, callable $handler): void
    {
        $this->routes[trim($path, '/')] = $handler;
    }

    public function dispatch(string $path): void
    {
        $path = trim($path, '/');
        if (array_key_exists($path, $this->routes)) {
            ($this->routes[$path])();
            return;
        }
        if (array_key_exists('404', $this->routes)) {
            ($this->routes['404'])();
        } else {
            header('Location: /error/404');
        }
    }
}
