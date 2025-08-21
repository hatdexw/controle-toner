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

// Rotas páginas (GET)
$router->add('GET','/controle-toner/','home.php');
$router->add('GET','/controle-toner/estoque','estoque.php');
$router->add('POST','/controle-toner/estoque','estoque.php');
$router->add('GET','/controle-toner/impressoras','impressoras.php');
$router->add('POST','/controle-toner/impressoras','impressoras.php');
$router->add('GET','/controle-toner/historico','historico.php');
$router->add('GET','/controle-toner/trocar','trocar.php');
$router->add('POST','/controle-toner/trocar','trocar.php');
$router->add('GET','/controle-toner/editar_impressora','editar_impressora.php');
$router->add('POST','/controle-toner/editar_impressora','editar_impressora.php');
$router->add('GET','/controle-toner/gerenciar_compatibilidade','gerenciar_compatibilidade.php');
$router->add('POST','/controle-toner/gerenciar_compatibilidade','gerenciar_compatibilidade.php');
$router->add('GET','/controle-toner/export_pdf','export_pdf.php');

// API simples (JSON)
$router->add('GET','/controle-toner/api/printers', function() use ($pdo){
    header('Content-Type: application/json');
    $data = $pdo->query('SELECT id,codigo,modelo,localizacao,toner_status FROM impressoras ORDER BY codigo')->fetchAll(\PDO::FETCH_ASSOC);
    return json_encode(['data'=>$data]);
});
$router->add('GET','/controle-toner/status', function() use ($pdo){
    header('Content-Type: application/json');
    $ok = true; $db= 'ok';
    try { $pdo->query('SELECT 1'); } catch(\Throwable $e){ $ok=false; $db='fail'; }
    return json_encode(['status'=>$ok?'ok':'degraded','db'=>$db,'time'=>date('c')]);
});
$router->add('GET','/controle-toner/dashboard','dashboard.php');

// Rota detalhada (exemplo param):
$router->add('GET','/controle-toner/api/printers/{id}', function($params) use ($pdo){
    header('Content-Type: application/json');
    $st = $pdo->prepare('SELECT * FROM impressoras WHERE id=?');
    $st->execute([$params['id']]);
    $row = $st->fetch(\PDO::FETCH_ASSOC);
    if(!$row){ http_response_code(404); return json_encode(['error'=>'Not found']); }
    return json_encode($row);
});

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
$router->dispatch($_SERVER['REQUEST_METHOD'],$uri);

// Logging simples
$logDir = __DIR__.'/logs'; if(!is_dir($logDir)) @mkdir($logDir,0777,true);
@file_put_contents($logDir.'/access.log',sprintf("[%s] %s %s %s\n",date('c'),$_SERVER['REMOTE_ADDR']??'-',$_SERVER['REQUEST_METHOD'],$uri),FILE_APPEND);
