<?php
require_once __DIR__ . '/../../layout/header.php';
?>
<div class="glass-card p-10 max-w-xl mx-auto mt-10 text-center animate-fade-in">
  <h1 class="text-3xl font-extrabold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-brand-500 to-brand-300">Você está offline</h1>
  <p class="text-gray-600 dark:text-gray-400 mb-6">Não foi possível carregar os dados da rede. Quando a conexão retornar, esta página será atualizada automaticamente.</p>
  <div class="flex justify-center mb-8">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-brand-500 animate-pulse" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12a9 9 0 0118 0M5.64 16.36A5 5 0 0112 9m0 0a5 5 0 016.36 7.36M12 9v3"/></svg>
  </div>
  <button onclick="location.reload()" class="primary-btn">Tentar novamente</button>
</div>
<script>window.addEventListener('online',()=>location.replace('/controle-toner/'));</script>
<?php require_once __DIR__ . '/../../layout/footer.php'; ?>
