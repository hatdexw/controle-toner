<?php

// Carrega o autoloader do Composer
require_once __DIR__ . '/vendor/autoload.php';

// Carrega a conexão com o banco de dados e o CSRF
require_once __DIR__ . '/src/db/connection.php';
require_once __DIR__ . '/src/core/csrf.php';

use App\Core\Router;

// Inicia a sessão se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cria uma instância do roteador
$router = new Router($pdo);

// Adiciona as rotas
// A rota '/' agora aponta para o antigo 'index.php', que moveremos para 'home.php'
$router->add('/controle-toner/', 'home.php');
$router->add('/controle-toner/estoque', 'estoque.php');
$router->add('/controle-toner/impressoras', 'impressoras.php');
$router->add('/controle-toner/historico', 'historico.php');
$router->add('/controle-toner/trocar', 'trocar.php');
$router->add('/controle-toner/editar_impressora', 'editar_impressora.php');
$router->add('/controle-toner/gerenciar_compatibilidade', 'gerenciar_compatibilidade.php');

// Obtém a URI da requisição
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normaliza a URI para lidar com variações como /index
if (substr($uri, -6) === '/index') {
    $uri = substr($uri, 0, -6); // Remove /index
}
// Garante que a URI que aponta para a raiz do app termine com uma barra
if ($uri === '/controle-toner') {
    $uri = '/controle-toner/';
}
// Remove a barra final de todas as outras URIs para consistência
if ($uri !== '/controle-toner/') {
    $uri = rtrim($uri, '/');
}

// Despacha a rota
$router->dispatch($uri);
