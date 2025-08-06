<?php

namespace App\Core;

class Router
{
    protected $routes = [];
    protected $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function add($uri, $controller)
    {
        $this->routes[$uri] = $controller;
    }

    public function dispatch($uri)
    {
        if (array_key_exists($uri, $this->routes)) {
            $pdo = $this->pdo; // Torna $pdo disponível no escopo do arquivo incluído
            require_once __DIR__ . '/../pages/' . $this->routes[$uri];
        } else {
            http_response_code(404);
            require_once __DIR__ . '/../pages/404.php';
        }
    }
}
