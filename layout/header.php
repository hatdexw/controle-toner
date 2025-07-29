<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Toners</title>
    <link href="./dist/output.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen flex flex-col">
    <nav class="bg-gray-800 shadow-lg py-4 px-6">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-white text-2xl font-bold tracking-tight">Controle de Toners</a>
            <div class="flex space-x-4">
                <?php
                $current_page = basename($_SERVER['PHP_SELF']);
                $nav_links = [
                    'index.php' => 'Impressoras',
                    'estoque.php' => 'Estoque',
                    'impressoras.php' => 'Gerenciar',
                    'historico.php' => 'Historico',
                ];

                foreach ($nav_links as $url => $text) {
                    $active_class = ($current_page === $url) ? 'bg-gray-700 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white';
                    echo '<a href="' . $url . '" class="' . $active_class . ' px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200">' . $text . '</a>';
                }
                ?>
            </div>
        </div>
    </nav>
    <main class="flex-grow container mx-auto p-6 mt-4">