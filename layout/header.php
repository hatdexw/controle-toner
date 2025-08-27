<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Controle de Toners</title>
    <?php $cssVersion = @filemtime(__DIR__.'/../dist/output.css') ?: time(); ?>
    <link href="/controle-toner/dist/output.css?v=<?= $cssVersion ?>" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" />
    <meta name="color-scheme" content="light dark">
        <link rel="manifest" href="/controle-toner/manifest.webmanifest">
    <meta name="theme-color" content="#2563eb" />
        <link rel="apple-touch-icon" href="/controle-toner/icons/icon-192.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
      (function(){
        try { const stored = localStorage.getItem('theme');
          if(stored==='dark'||(!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark');
          else document.documentElement.classList.remove('dark');
        } catch(e){}
      })();
    </script>
        <script>
            if('serviceWorker' in navigator){
                window.addEventListener('load',()=>{
                    navigator.serviceWorker.register('/controle-toner/sw.js').catch(()=>{});
                });
            }
        </script>
</head>
<body class="min-h-screen flex flex-col pt-24 selection:bg-brand-500/90">
    <nav class="fixed top-0 inset-x-0 z-50 backdrop-blur-xl bg-white/70 dark:bg-gray-950/70 border-b border-black/5 dark:border-white/10">
        <div class="app-container h-20 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="/controle-toner/" class="group inline-flex items-center gap-2">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-md bg-brand-600 text-white font-bold">CT</span>
                    <span class="text-xl font-bold tracking-tight text-gray-800 dark:text-gray-100">Controle de Toners</span>
                </a>
            </div>
            <button id="mobileMenuBtn" class="lg:hidden neutral-btn !px-3 !py-2" aria-label="Abrir menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
            </button>
            <div id="desktopNav" class="hidden lg:flex items-center gap-4">
                <?php
                $current_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                if (substr($current_uri, -6) === '/index') { $current_uri = substr($current_uri, 0, -6);} 
                if ($current_uri === '/controle-toner') { $current_uri = '/controle-toner/'; }
                if ($current_uri !== '/controle-toner/') { $current_uri = rtrim($current_uri, '/'); }
                $nav_links = [
                    '/controle-toner/dashboard' => ['Dashboard','M4 3h12a1 1 0 011 1v2H3V4a1 1 0 011-1zm-1 5h16v7a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm5 2h2v3H8v-3z'],
                    '/controle-toner/' => ['Impressoras','M6 4h12a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2V6a2 2 0 012-2zm2 3h8v6H8V7z'],
                    '/controle-toner/estoque' => ['Estoque','M4 6h16M4 12h16M4 18h16'],
                    '/controle-toner/impressoras' => ['Gerenciar','M12 6v12m6-6H6'],
                    '/controle-toner/historico' => ['Histórico','M8 7V3m8 4V3M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z'],
                ];
                    foreach ($nav_links as $url => $info) {
                        [$text,$path] = $info; $isActive = ($current_uri === $url);
                        $cls = $isActive ? 'px-3 py-2 rounded-full bg-brand-600 text-white text-sm font-medium shadow-brand-sm inline-flex items-center gap-2' : 'px-3 py-2 rounded-full text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 inline-flex items-center gap-2';
                        $icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4"><path d="'.$path.'"/></svg>';
                        echo '<a href="'.$url.'" class="'.$cls.'">'.$icon.'<span>'.htmlspecialchars($text).'</span></a>';
                }
                ?>
                <?php $periodBadge = isset($_GET['period']) ? preg_replace('/[^0-9\-]/','',$_GET['period']) : date('Y-m'); ?>
                <span class="hidden md:inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-500/20 dark:text-yellow-300">Mês <?= htmlspecialchars($periodBadge) ?> · Aberto</span>
                <button id="themeToggle" class="px-3 py-2 rounded-full text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800" aria-label="Alternar tema">
                    <span class="theme-icon-sun block dark:hidden">🌞</span>
                    <span class="theme-icon-moon hidden dark:block">🌙</span>
                </button>
                <button id="pwaInstallBtn" class="px-3 py-2 rounded-full text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hidden" aria-label="Instalar Aplicativo">Instalar</button>
            </div>
        </div>
        <div id="mobilePanel" class="lg:hidden hidden px-4 pb-6 origin-top animate-scale-in">
            <div class="glass-card p-4 flex flex-col gap-2">
                <?php foreach ($nav_links as $url => $info) { [$text,$path] = $info; $isActive = ($current_uri === $url); $cls = $isActive ? 'px-3 py-2 rounded-lg bg-brand-600 text-white text-sm font-medium' : 'px-3 py-2 rounded-lg text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'; echo '<a href="'.$url.'" class="'.$cls.'">'.htmlspecialchars($text).'</a>'; } ?>
                <button id="themeToggleMobile" class="nav-link !justify-start !px-3" aria-label="Alternar tema (mobile)">
                    <span class="theme-icon-sun block dark:hidden">🌞 Tema Claro</span>
                    <span class="theme-icon-moon hidden dark:block">🌙 Tema Escuro</span>
                </button>
            </div>
        </div>
    </nav>
    <main class="flex-grow app-container py-8">