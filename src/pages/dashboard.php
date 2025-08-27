<?php
require_once __DIR__.'/../../layout/header.php';
use App\Services\StatsService;
$service = new StatsService($pdo);
$selectedPeriod = isset($_GET['period']) ? preg_replace('/[^0-9\-]/','',$_GET['period']) : null;
$data = $service->dashboardData($selectedPeriod);
$stats = $data['summary'];
$tonerBuckets = $data['tonerBuckets'];
$suprimentosPorTipo = $data['suppliesByType'];
$statusSummary = $data['status'];
$dates = array_map(fn($l)=>date('Y-m-d', strtotime(str_replace('/','-',$l))), $data['exchanges']['labels']);
$counts = $data['exchanges']['values'];
$lowPrinters = $data['lowPrinters'];
$criticalSup = $data['criticalSupplies'];
?>
<div class="flex items-center justify-between mb-4">
  <div class="flex items-start gap-3">
    <div class="h-10 w-10 rounded-md bg-brand-50 text-brand-700 dark:bg-brand-500/20 dark:text-brand-200 flex items-center justify-center">📊</div>
    <div>
      <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight text-gray-800 dark:text-gray-100">Dashboard</h1>
      <p class="text-sm text-gray-500 dark:text-gray-400">Visão geral dos toners, suprimentos e últimas trocas.</p>
    </div>
  </div>
  <form method="get" class="flex items-center gap-2">
    <label class="text-sm text-gray-600 dark:text-gray-300">Período</label>
    <?php $currentPeriod = isset($_GET['period']) ? preg_replace('/[^0-9\-]/','',$_GET['period']) : date('Y-m'); ?>
    <input name="period" type="month" value="<?= htmlspecialchars($currentPeriod) ?>" class="form-input !py-2 !px-3 max-w-[180px]" />
    <button class="primary-btn !py-2 !px-4">Aplicar</button>
    <button id="dashRefresh" type="button" class="neutral-btn !px-3 !py-2" title="Atualizar agora">Atualizar</button>
  </form>
</div>
<?php 
  $withData = (int)$stats['totalPrinters'] - (int)$statusSummary['sem_dado'];
  $typesCount = count($suprimentosPorTipo);
  $exTotal = array_sum($counts);
?>
<div class="grid grid-cols-12 gap-4 mb-4">
  <div class="col-span-12 sm:col-span-6 xl:col-span-3 soft-card-green p-4">
    <div class="text-xs text-gray-500">Impressoras</div>
    <div class="text-2xl font-extrabold text-gray-800 dark:text-gray-100 mt-1"><?= $stats['totalPrinters'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Com dado: <?= $withData ?> • Sem dado: <?= (int)$statusSummary['sem_dado'] ?></div>
  </div>
  <div class="col-span-12 sm:col-span-6 xl:col-span-3 soft-card-red p-4">
    <div class="text-xs text-gray-500">Toners Baixos</div>
    <div class="text-2xl font-extrabold text-red-600 dark:text-red-300 mt-1"><?= $stats['lowToner'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Vazios: <?= (int)$stats['emptyToner'] ?> • ≤15%: <?= (int)$stats['lowToner'] ?></div>
  </div>
  <div class="col-span-12 sm:col-span-6 xl:col-span-3 soft-card-blue p-4">
    <div class="text-xs text-gray-500">Trocas no Período</div>
    <div class="text-2xl font-extrabold text-brand-600 mt-1"><?= $exTotal ?></div>
    <div class="text-xs text-gray-500 mt-1">Período: <?= htmlspecialchars($selectedPeriod ?? 'últimos 14 dias') ?></div>
  </div>
  <div class="col-span-12 sm:col-span-6 xl:col-span-3 soft-card-purple p-4">
    <div class="text-xs text-gray-500">Suprimentos</div>
    <div class="text-2xl font-extrabold text-purple-600 dark:text-purple-300 mt-1"><?= $stats['totalSupplies'] ?></div>
    <div class="text-xs text-gray-500 mt-1">Tipos: <?= $typesCount ?> • Críticos: <?= (int)$stats['lowStockSupplies'] ?></div>
  </div>
</div>

<div class="grid grid-cols-12 gap-4 mb-4">
  <div class="col-span-12 xl:col-span-3 soft-card p-4">
    <div class="text-xs text-gray-500">Insights</div>
    <ul class="mt-2 text-sm text-gray-700 dark:text-gray-300 list-disc pl-4 space-y-1">
      <?php foreach(($data['insights']??[]) as $ins): ?>
      <li><?= htmlspecialchars($ins) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <div class="col-span-12 xl:col-span-9 soft-card p-4">
    <div class="card-header mb-1"><h3 class="card-title text-base">Gastos por Categoria (Distribuição de Toner)</h3></div>
    <div class="h-[180px]"><canvas id="chartTonerDist" class="w-full h-full" role="img" aria-label="Distribuição"></canvas></div>
  </div>
</div>

<div class="grid grid-cols-12 gap-4 items-stretch">
  <div class="col-span-12 md:col-span-6 xl:col-span-3 soft-card p-3">
    <div class="card-header mb-1"><h2 class="card-title text-base">Últimas Trocas</h2></div>
    <ul class="space-y-1.5 text-sm max-h-40 overflow-auto pr-1">
      <?php if(empty($stats['lastExchanges'])): ?>
        <li class="text-gray-500 dark:text-gray-400">Sem registros ainda.</li>
      <?php else: $i=0; foreach($stats['lastExchanges'] as $ex): if($i++>=5) break; ?>
        <li class="flex items-center justify-between">
          <span class="font-medium text-gray-700 dark:text-gray-200 truncate"><?= htmlspecialchars($ex['codigo']) ?></span>
          <span class="text-xs text-gray-500 dark:text-gray-400 ml-3 shrink-0"><?= date('d/m H:i', strtotime($ex['data_troca'])) ?></span>
        </li>
      <?php endforeach; endif; ?>
    </ul>
  </div>
  <div class="col-span-12 md:col-span-6 xl:col-span-3 glass-card p-3">
    <div class="card-header mb-1"><h3 class="card-title text-base">Mais Críticas</h3><span class="badge">Toner baixo</span></div>
    <ul class="space-y-1.5 text-sm max-h-40 overflow-auto pr-1">
      <?php if(empty($lowPrinters)): ?>
        <li class="text-gray-500 dark:text-gray-400">Sem impressoras críticas.</li>
      <?php else: foreach($lowPrinters as $lp): ?>
        <li class="flex items-center justify-between">
          <span class="truncate font-medium"><?= htmlspecialchars($lp['codigo']) ?> — <?= htmlspecialchars($lp['modelo']) ?></span>
          <span class="ml-3 text-xs px-2 py-0.5 rounded-full <?= ((int)$lp['toner_status']<=5?'bg-red-500/20 text-red-600 dark:text-red-300':'bg-yellow-500/20 text-yellow-600 dark:text-yellow-300') ?>"><?= (int)$lp['toner_status'] ?>%</span>
        </li>
      <?php endforeach; endif; ?>
    </ul>
  </div>
  <div class="col-span-12 md:col-span-6 xl:col-span-3 soft-card p-3">
    <div class="card-header mb-1"><h3 class="card-title text-base">Estoque por Tipo</h3><span class="badge">Suprimentos</span></div>
    <div class="h-[120px]"><canvas id="chartSupplies" class="w-full h-full" aria-label="Gráfico de estoque por tipo" role="img"></canvas></div>
  </div>
  <div class="col-span-12 md:col-span-6 xl:col-span-3 soft-card p-3">
    <div class="card-header mb-1"><h3 class="card-title text-base">Status das Impressoras</h3><span class="badge">Health</span></div>
    <div class="h-[120px]"><canvas id="chartPrinterStatus" class="w-full h-full" aria-label="Gráfico de status das impressoras" role="img"></canvas></div>
  </div>
  <div class="col-span-12 md:col-span-6 xl:col-span-3 soft-card p-3">
    <div class="card-header mb-1"><h3 class="card-title text-base">Trocas (7 dias)</h3><span class="badge">Tendência</span></div>
    <div class="h-[140px]"><canvas id="chartExchanges" class="w-full h-full" aria-label="Gráfico de trocas nos últimos 7 dias" role="img"></canvas></div>
  </div>
  <div class="col-span-12 md:col-span-6 xl:col-span-3 soft-card p-3">
    <div class="card-header mb-1"><h3 class="card-title text-base">Suprimentos Críticos</h3><span class="badge">Estoque</span></div>
    <ul class="space-y-1.5 text-sm max-h-40 overflow-auto pr-1">
      <?php if(empty($criticalSup)): ?>
        <li class="text-gray-500 dark:text-gray-400">Nenhum suprimento crítico.</li>
      <?php else: foreach($criticalSup as $cs): ?>
        <li class="flex justify-between"><span class="truncate"><?= htmlspecialchars($cs['modelo']) ?> (<?= htmlspecialchars($cs['tipo']) ?>)</span><span class="text-xs px-2 py-0.5 rounded-full <?= ($cs['quantidade']==0?'bg-red-500/20 text-red-600 dark:text-red-300':'bg-orange-500/20 text-orange-600 dark:text-orange-300') ?>"><?= (int)$cs['quantidade'] ?></span></li>
      <?php endforeach; endif; ?>
    </ul>
  </div>
</div>

<script>
window.addEventListener('DOMContentLoaded',()=>{
  const script=document.createElement('script');
  script.src='https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js';
  script.onload=()=>initCharts();
  document.head.appendChild(script);
  // Refresh button (simple full reload; could be improved to partial in future)
  document.getElementById('dashRefresh')?.addEventListener('click',()=>{ location.reload(); });
});
function initCharts(){
  const tonerData = <?= json_encode(array_values($tonerBuckets), JSON_NUMERIC_CHECK) ?>;
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
  const commonSmall = {
    plugins:{ legend:{ display:false } },
    scales:{ x:{ display:false, grid:{ color:baseGrid } }, y:{ display:false, grid:{ color:baseGrid } } },
    responsive:true,
    maintainAspectRatio:false
  };
  new Chart(document.getElementById('chartTonerDist'),{type:'doughnut',data:{
      labels:['Vazio','Baixo (1-15%)','Médio (16-50%)','Alto (51%+)'],
      datasets:[{data:tonerData,backgroundColor:['#ef4444','#f59e0b','#3b82f6','#10b981'],borderWidth:0}]
    },options:{plugins:{legend:{display:false}},cutout:'55%',responsive:true,maintainAspectRatio:false}});
  new Chart(document.getElementById('chartSupplies'),{type:'bar',data:{labels:suppliesLabels,datasets:[{label:'Qtd',data:suppliesData,backgroundColor:'#1f5fff'}]},options:commonSmall});
  new Chart(document.getElementById('chartPrinterStatus'),{type:'bar',data:{
      labels:['Sem Dado','Vazio','Baixo','OK'],
      datasets:[{label:'Impressoras',data:statusData,backgroundColor:['#6b7280','#ef4444','#f59e0b','#10b981']}]
    },options:commonSmall});
  const exchangeOpts = {
    plugins:{ legend:{ display:false } },
    scales:{ x:{ display:true, ticks:{ color:textCol, maxTicksLimit:10 }, grid:{ color:baseGrid } }, y:{ display:true, ticks:{ color:textCol, precision:0 }, grid:{ color:baseGrid } } },
    responsive:true, maintainAspectRatio:false
  };
  new Chart(document.getElementById('chartExchanges'),{type:'line',data:{labels:exchangesLabels,datasets:[{label:'Trocas',data:exchangesData,fill:true,borderColor:'#1f5fff',backgroundColor:'rgba(31,95,255,0.15)',tension:.35}]},options:exchangeOpts});
}
</script>
<?php require_once __DIR__.'/../../layout/footer.php'; ?>
