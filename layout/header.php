<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Toners</title>
    <link href="./dist/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col pt-16">
    <nav class="bg-gray-800 shadow-lg py-4 px-6 fixed top-0 w-full z-50">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index" class="text-white text-2xl font-bold tracking-tight">Controle de Toners</a>
            <div class="flex space-x-4">
                <?php
                $current_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                // Normaliza a URI para corresponder às rotas do roteador
                if (substr($current_uri, -6) === '/index') {
                    $current_uri = substr($current_uri, 0, -6); // Remove /index
                }
                if ($current_uri === '/controle-toner') {
                    $current_uri = '/controle-toner/';
                }
                if ($current_uri !== '/controle-toner/') {
                    $current_uri = rtrim($current_uri, '/');
                }

                $nav_links = [
                    '/controle-toner/' => 'Impressoras',
                    '/controle-toner/estoque' => 'Estoque',
                    '/controle-toner/impressoras' => 'Gerenciar',
                    '/controle-toner/historico' => 'Historico',
                ];

                foreach ($nav_links as $url => $text) {
                    $active_class = ($current_uri === $url) ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white';
                    echo '<a href="' . $url . '" class="' . $active_class . ' px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">' . $text . '</a>';
                }
                ?>
            </div>
        </div>
    </nav>
    <main class="flex-grow container mx-auto p-6 flex items-center justify-center">