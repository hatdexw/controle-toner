<?php
require_once __DIR__.'/../../layout/header.php';
use App\Services\StatsService; 
$service = new StatsService($pdo); 
$stats = $service->summary();
$lowPrinters = $pdo->query("SELECT codigo, modelo, toner_status FROM impressoras WHERE toner_status IS NOT NULL AND toner_status <= 15 ORDER BY toner_status ASC LIMIT 5")->fetchAll();
$criticalSup = $pdo->query("SELECT modelo, tipo, quantidade FROM suprimentos WHERE quantidade <= 2 ORDER BY quantidade ASC LIMIT 5")->fetchAll();
// Aggregated data for charts
// Toner distribution buckets
$tonerBuckets = $pdo->query("SELECT 
  SUM(CASE WHEN toner_status = 0 THEN 1 ELSE 0 END) AS vazio,
  SUM(CASE WHEN toner_status BETWEEN 1 AND 15 THEN 1 ELSE 0 END) AS baixo,
  SUM(CASE WHEN toner_status BETWEEN 16 AND 50 THEN 1 ELSE 0 END) AS medio,
  SUM(CASE WHEN toner_status BETWEEN 51 AND 100 THEN 1 ELSE 0 END) AS alto
FROM impressoras")->fetch(PDO::FETCH_ASSOC) ?: ['vazio'=>0,'baixo'=>0,'medio'=>0,'alto'=>0];

// Supplies stock by type
$suprimentosPorTipo = $pdo->query("SELECT tipo, SUM(quantidade) AS total FROM suprimentos GROUP BY tipo ORDER BY tipo")->fetchAll(PDO::FETCH_ASSOC);

// Printer status summary
$statusSummary = $pdo->query("SELECT 
  SUM(CASE WHEN toner_status IS NULL THEN 1 ELSE 0 END) AS sem_dado,
  SUM(CASE WHEN toner_status = 0 THEN 1 ELSE 0 END) AS vazio,
  SUM(CASE WHEN toner_status BETWEEN 1 AND 15 THEN 1 ELSE 0 END) AS baixo,
  SUM(CASE WHEN toner_status > 15 THEN 1 ELSE 0 END) AS ok
FROM impressoras")->fetch(PDO::FETCH_ASSOC);

// Exchanges last 7 days timeline
$historicoStmt = $pdo->prepare("SELECT DATE(data_troca) d, COUNT(*) c FROM historico_trocas WHERE data_troca >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(data_troca) ORDER BY d ASC");
$historicoStmt->execute();
$historicoSeriesRaw = $historicoStmt->fetchAll(PDO::FETCH_KEY_PAIR); // date => count
$dates = [];$counts=[];for($i=6;$i>=0;$i--){$day=date('Y-m-d',strtotime("-$i day"));$dates[]=$day;$counts[]=(int)($historicoSeriesRaw[$day]??0);} 
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
<div class="mt-12 grid grid-cols-1 xl:grid-cols-2 gap-10">
  <div class="glass-card p-6 anim-hover-lift">
    <div class="card-header mb-4"><h3 class="card-title text-lg">Distribuição de Toner</h3><span class="badge">Níveis</span></div>
    <canvas id="chartTonerDist" height="200" aria-label="Gráfico de distribuição de níveis de toner" role="img"></canvas>
  </div>
  <div class="glass-card p-6 anim-hover-lift">
    <div class="card-header mb-4"><h3 class="card-title text-lg">Estoque por Tipo</h3><span class="badge">Suprimentos</span></div>
    <canvas id="chartSupplies" height="200" aria-label="Gráfico de estoque por tipo" role="img"></canvas>
  </div>
  <div class="glass-card p-6 anim-hover-lift">
    <div class="card-header mb-4"><h3 class="card-title text-lg">Status das Impressoras</h3><span class="badge">Health</span></div>
    <canvas id="chartPrinterStatus" height="200" aria-label="Gráfico de status das impressoras" role="img"></canvas>
  </div>
  <div class="glass-card p-6 anim-hover-lift">
    <div class="card-header mb-4"><h3 class="card-title text-lg">Trocas (7 dias)</h3><span class="badge">Tendência</span></div>
    <canvas id="chartExchanges" height="200" aria-label="Gráfico de trocas nos últimos 7 dias" role="img"></canvas>
  </div>
</div>
<script>
// Defer Chart.js loading only on dashboard
window.addEventListener('DOMContentLoaded',()=>{
  const script=document.createElement('script');
  script.src='https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
  script.onload=()=>initCharts();
  document.head.appendChild(script);
});
function initCharts(){
  const tonerData = <?= json_encode(array_values($tonerBuckets), JSON_NUMERIC_CHECK) ?>; // [vazio, baixo, medio, alto]
  const suppliesData = <?= json_encode(array_map(fn($r)=>(int)$r['total'],$suprimentosPorTipo), JSON_NUMERIC_CHECK) ?>;
  const suppliesLabels = <?= json_encode(array_map(fn($r)=>$r['tipo'],$suprimentosPorTipo)) ?>;
  const statusData = [
    <?= (int)$statusSummary['sem_dado'] ?>,
    <?= (int)$statusSummary['vazio'] ?>,
    <?= (int)$statusSummary['baixo'] ?>,
    <?= (int)$statusSummary['ok'] ?>
  ];
  const exchangesLabels = <?= json_encode(array_map(fn($d)=>date('d/m',strtotime($d)),$dates)) ?>;
  const exchangesData = <?= json_encode($counts, JSON_NUMERIC_CHECK) ?>;
  const dark = document.documentElement.classList.contains('dark');
  const baseGrid = dark? 'rgba(255,255,255,0.08)':'rgba(0,0,0,0.06)';
  const textCol = dark? '#e5e7eb':'#374151';
  const common = {plugins:{legend:{labels:{color:textCol}}},scales:{x:{grid:{color:baseGrid},ticks:{color:textCol}},y:{grid:{color:baseGrid},ticks:{color:textCol}}}};
  new Chart(document.getElementById('chartTonerDist'),{type:'doughnut',data:{
      labels:['Vazio','Baixo (1-15%)','Médio (16-50%)','Alto (51%+)'],
      datasets:[{data:tonerData,backgroundColor:['#ef4444','#f59e0b','#3b82f6','#10b981'],borderWidth:0}]
    },options:{plugins:{legend:{position:'bottom'}},cutout:'55%'}});
  new Chart(document.getElementById('chartSupplies'),{type:'bar',data:{labels:suppliesLabels,datasets:[{label:'Quantidade',data:suppliesData,backgroundColor:'#1f5fff'}]},options:common});
  new Chart(document.getElementById('chartPrinterStatus'),{type:'bar',data:{
      labels:['Sem Dado','Vazio','Baixo','OK'],
      datasets:[{label:'Impressoras',data:statusData,backgroundColor:['#6b7280','#ef4444','#f59e0b','#10b981']}]
    },options:common});
  new Chart(document.getElementById('chartExchanges'),{type:'line',data:{labels:exchangesLabels,datasets:[{label:'Trocas',data:exchangesData,fill:true,borderColor:'#1f5fff',backgroundColor:'rgba(31,95,255,0.15)',tension:.35}]},options:common});
}
</script>
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-10">
  <div class="glass-card p-6 anim-hover-lift">
    <div class="card-header mb-2"><h3 class="card-title text-lg">Alertas Toner Baixo</h3><span class="badge">Top 5</span></div>
    <ul class="space-y-2 text-sm">
      <?php if(empty($lowPrinters)): ?>
        <li class="text-gray-500 dark:text-gray-400">Nenhuma impressora em alerta.</li>
      <?php else: foreach($lowPrinters as $lp): ?>
        <li class="flex justify-between"><span class="font-medium text-gray-700 dark:text-gray-200"><?= htmlspecialchars($lp['codigo']) ?></span><span class="text-xs px-2 py-0.5 rounded-full <?= ($lp['toner_status']<=5?'bg-red-500/20 text-red-600 dark:text-red-300':'bg-yellow-500/20 text-yellow-600 dark:text-yellow-300') ?>"><?= (int)$lp['toner_status'] ?>%</span></li>
      <?php endforeach; endif; ?>
    </ul>
  </div>
  <div class="glass-card p-6 anim-hover-lift">
    <div class="card-header mb-2"><h3 class="card-title text-lg">Suprimentos Críticos</h3><span class="badge">Estoque</span></div>
    <ul class="space-y-2 text-sm">
      <?php if(empty($criticalSup)): ?>
        <li class="text-gray-500 dark:text-gray-400">Nenhum suprimento crítico.</li>
      <?php else: foreach($criticalSup as $cs): ?>
        <li class="flex justify-between"><span><?= htmlspecialchars($cs['modelo']) ?> (<?= htmlspecialchars($cs['tipo']) ?>)</span><span class="text-xs px-2 py-0.5 rounded-full <?= ($cs['quantidade']==0?'bg-red-500/20 text-red-600 dark:text-red-300':'bg-orange-500/20 text-orange-600 dark:text-orange-300') ?>"><?= (int)$cs['quantidade'] ?></span></li>
      <?php endforeach; endif; ?>
    </ul>
  </div>
</div>
<?php require_once __DIR__.'/../../layout/footer.php'; ?>
