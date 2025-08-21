<?php
require_once __DIR__.'/../../layout/header.php';
use App\Services\StatsService; 
$service = new StatsService($pdo); 
$stats = $service->summary();
?>
<h1 class="section-title">Dashboard</h1>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
  <div class="glass-card p-6 anim-hover-lift"><p class="text-sm text-gray-500 dark:text-gray-400">Impressoras</p><p class="text-3xl font-bold mt-2"><?= $stats['totalPrinters'] ?></p></div>
  <div class="glass-card p-6 anim-hover-lift"><p class="text-sm text-gray-500 dark:text-gray-400">Toner Baixo (≤15%)</p><p class="text-3xl font-bold mt-2 text-yellow-500"><?= $stats['lowToner'] ?></p></div>
  <div class="glass-card p-6 anim-hover-lift"><p class="text-sm text-gray-500 dark:text-gray-400">Toner Vazio</p><p class="text-3xl font-bold mt-2 text-red-500"><?= $stats['emptyToner'] ?></p></div>
  <div class="glass-card p-6 anim-hover-lift"><p class="text-sm text-gray-500 dark:text-gray-400">Suprimentos</p><p class="text-3xl font-bold mt-2"><?= $stats['totalSupplies'] ?></p></div>
  <div class="glass-card p-6 anim-hover-lift"><p class="text-sm text-gray-500 dark:text-gray-400">Suprimentos Críticos (≤2)</p><p class="text-3xl font-bold mt-2 text-orange-500"><?= $stats['lowStockSupplies'] ?></p></div>
  <div class="glass-card p-6 anim-hover-lift"><p class="text-sm text-gray-500 dark:text-gray-400">Atualização</p><p class="text-lg font-medium mt-2"><?= date('d/m/Y H:i') ?></p></div>
</div>
<div class="glass-card p-8 anim-hover-lift">
  <div class="card-header mb-4"><h2 class="card-title">Últimas Trocas</h2></div>
  <div class="overflow-x-auto">
    <table class="table-base">
      <thead class="table-head-row"><tr><th class="table-head-cell">Data</th><th class="table-head-cell">Impressora</th><th class="table-head-cell">Suprimento</th></tr></thead>
      <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        <?php if(empty($stats['lastExchanges'])): ?>
          <tr><td colspan="3" class="table-cell text-center text-gray-500">Sem registros ainda.</td></tr>
        <?php else: foreach($stats['lastExchanges'] as $ex): ?>
          <tr class="table-row"><td class="table-cell font-medium"><?= date('d/m/Y H:i', strtotime($ex['data_troca'])) ?></td><td class="table-cell"><?= htmlspecialchars($ex['codigo']) ?></td><td class="table-cell"><?= htmlspecialchars($ex['modelo']) ?></td></tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once __DIR__.'/../../layout/footer.php'; ?>
