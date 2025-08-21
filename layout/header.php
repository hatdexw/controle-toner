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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
      (function(){
        try { const stored = localStorage.getItem('theme');
          if(stored==='dark'||(!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark');
          else document.documentElement.classList.remove('dark');
        } catch(e){}
      })();
    </script>
</head>
<body class="min-h-screen flex flex-col pt-24 selection:bg-brand-500/90">
    <nav class="fixed top-0 inset-x-0 z-50 backdrop-blur-xl bg-gray-900/80 dark:bg-gray-950/70 border-b border-white/10">
        <div class="app-container h-20 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="/controle-toner/" class="group inline-flex items-center gap-2">
                    <span class="relative inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-600 text-white shadow-brand-sm ring-1 ring-white/20">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-6 w-6">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                        </svg>
                    </span>
                    <span class="text-xl font-bold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-brand-300 via-white to-brand-200">Controle de Toners</span>
                </a>
            </div>
            <button id="mobileMenuBtn" class="lg:hidden neutral-btn !px-3 !py-2" aria-label="Abrir menu">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
            </button>
            <div id="desktopNav" class="hidden lg:flex items-center gap-2">
                <?php
                $current_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                if (substr($current_uri, -6) === '/index') { $current_uri = substr($current_uri, 0, -6);} 
                if ($current_uri === '/controle-toner') { $current_uri = '/controle-toner/'; }
                if ($current_uri !== '/controle-toner/') { $current_uri = rtrim($current_uri, '/'); }
                $nav_links = [
                    '/controle-toner/' => 'Impressoras',
                    '/controle-toner/estoque' => 'Estoque',
                    '/controle-toner/impressoras' => 'Gerenciar',
                    '/controle-toner/historico' => 'Histórico',
                ];
                foreach ($nav_links as $url => $text) {
                    $active = ($current_uri === $url) ? 'nav-link-active' : 'nav-link';
                    echo '<a href="'.$url.'" class="'.$active.'">'.$text.'</a>';
                }
                ?>
                <button id="themeToggle" class="nav-link !px-3" aria-label="Alternar tema">
                    <span class="theme-icon-sun block dark:hidden">🌞</span>
                    <span class="theme-icon-moon hidden dark:block">🌙</span>
                </button>
            </div>
        </div>
        <div id="mobilePanel" class="lg:hidden hidden px-4 pb-6 origin-top animate-scale-in">
            <div class="glass-card p-4 flex flex-col gap-2">
                <?php foreach ($nav_links as $url => $text) { $active = ($current_uri === $url) ? 'nav-link-active' : 'nav-link'; echo '<a href="'.$url.'" class="'.$active.'">'.$text.'</a>'; } ?>
                <button id="themeToggleMobile" class="nav-link !justify-start !px-3" aria-label="Alternar tema (mobile)">
                    <span class="theme-icon-sun block dark:hidden">🌞 Tema Claro</span>
                    <span class="theme-icon-moon hidden dark:block">🌙 Tema Escuro</span>
                </button>
            </div>
        </div>
    </nav>
    <main class="flex-grow app-container py-10">