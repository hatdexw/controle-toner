<?php

namespace App\Core;

class Router
{
    /** @var array<string,array<string,callable|string>> */
    protected $routes = [];
    protected $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Registra rota
     * @param string $method GET|POST|PUT|DELETE|PATCH
     * @param string $pattern ex: /controle-toner/api/printers/{id}
     * @param callable|string $handler page (string) ou callable($params,$pdo)
     */
    public function add(string $method, string $pattern, $handler): void
    {
        $method = strtoupper($method);
        $this->routes[$method][$pattern] = $handler;
    }

    public function match(string $method, string $uri)
    {
        $method = strtoupper($method);
        $routes = $this->routes[$method] ?? [];
        foreach ($routes as $pattern => $handler) {
            $regex = preg_replace('#\{([^/]+)\}#','(?P<$1>[^/]+)',$pattern);
            if (preg_match('#^'.$regex.'$#',$uri,$m)) {
                $params = array_filter($m,'is_string',ARRAY_FILTER_USE_KEY);
                return [$handler,$params];
            }
        }
        return null;
    }

    public function dispatch(string $method, string $uri): void
    {
        $matched = $this->match($method,$uri);
        if ($matched) {
            [$handler,$params] = $matched;
            $pdo = $this->pdo;
            if (is_callable($handler)) {
                echo $handler($params,$pdo);
            } else {
                // page include
                require __DIR__.'/../pages/'.$handler;
            }
            return;
        }
        http_response_code(404);
        require __DIR__ . '/../pages/404.php';
    }
}
