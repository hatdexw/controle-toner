<?php
// Inclui primeiro a lógica de ações para processar POST antes de qualquer saída
require_once __DIR__ . '/../actions/impressora_actions.php';
require_once __DIR__ . '/../../layout/header.php';


$page = max(1,(int)($_GET['p']??1));
$perPage = 20;
$offset = ($page-1)*$perPage;
$order = $_GET['order'] ?? 'codigo';
$allowedOrders = ['codigo', 'modelo', 'toner_status', 'ultima_troca'];
if (!in_array($order, $allowedOrders)) $order = 'codigo';

// Monta o campo de ordenação SQL
switch ($order) {
  case 'modelo':
    $orderBy = 'modelo';
    break;
  case 'toner_status':
    $orderBy = 'toner_status DESC';
    break;
  case 'ultima_troca':
    $orderBy = '(SELECT MAX(data_troca) FROM historico_trocas WHERE historico_trocas.codigo = impressoras.codigo) DESC';
    break;
  default:
    $orderBy = 'codigo';
}

$totalPrinters = (int)$pdo->query('SELECT COUNT(*) FROM impressoras')->fetchColumn();
$totalPages = max(1,(int)ceil($totalPrinters/$perPage));
$stmt = $pdo->prepare("SELECT * FROM impressoras ORDER BY $orderBy LIMIT :limit OFFSET :offset");
$stmt->bindValue(':limit',$perPage,PDO::PARAM_INT);
$stmt->bindValue(':offset',$offset,PDO::PARAM_INT);
$stmt->execute();
$impressoras = $stmt->fetchAll();
if (isset($_SESSION['message'])) { $message_type = $_SESSION['message']['type']; $message_text = $_SESSION['message']['text']; echo "<script>showToast('$message_type', '$message_text');</script>"; unset($_SESSION['message']); }
?>
<h1 class="section-title">Gerenciar Impressoras</h1>
<div class="glass-card p-8 mb-8 fade-in anim-hover-lift">
  <div class="card-header"><h2 class="card-title">Adicionar Nova Impressora</h2><span class="badge">Cadastro</span></div>
  <form method="POST" action="/controle-toner/impressoras" id="addImpressoraForm" class="grid grid-cols-1 md:grid-cols-5 gap-6 items-end">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
    <div><label for="codigo" class="field-label">Código</label><input type="text" name="codigo" id="codigo" placeholder="Ex: COM-00001.000" class="form-input" required></div>
    <div><label for="modelo" class="field-label">Modelo</label><input type="text" name="modelo" id="modelo" placeholder="Ex: HP LaserJet MFP M132fw" class="form-input" required></div>
    <div><label for="localizacao" class="field-label">Localização</label><input type="text" name="localizacao" id="localizacao" placeholder="Ex: Compras" class="form-input" required></div>
    <div><label class="field-label">&nbsp;</label><button type="submit" name="add_impressora" class="primary-btn w-full justify-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/></svg>Adicionar Impressora</button></div>
  </form>
</div>
<div class="glass-card p-8 fade-in anim-hover-lift">
  <div class="card-header mb-2 flex flex-col md:flex-row md:items-center md:justify-between gap-2">
    <div class="flex items-center gap-2">
      <h2 class="card-title">Impressoras Cadastradas</h2><span class="badge">Lista</span>
    </div>
    <div class="flex gap-2 mt-2 md:mt-0">
      <button class="neutral-btn !px-4 !py-1 text-sm" onclick="window.location.search='?order=codigo'<?= $page>1?'+&p='.$page:'' ?>">Ordenar Código</button>
      <button class="neutral-btn !px-4 !py-1 text-sm" onclick="window.location.search='?order=toner_status'<?= $page>1?'+&p='.$page:'' ?>">Ordenar Toner</button>
      <button class="neutral-btn !px-4 !py-1 text-sm" onclick="window.location.search='?order=ultima_troca'<?= $page>1?'+&p='.$page:'' ?>">Ordenar Última Troca</button>
    </div>
  </div>
  <div class="mb-4"><input type="text" id="searchImpressora" placeholder="Buscar impressora por código, modelo ou localização..." class="form-input"></div>
  <div class="overflow-x-auto">
  <table class="table-base responsive-stack" id="impressorasTable">
      <thead class="table-head-row">
        <tr>
          <th scope="col" class="table-head-cell">Código</th>
          <th scope="col" class="table-head-cell">Modelo</th>
          <th scope="col" class="table-head-cell">Localização</th>
          <th scope="col" class="table-head-cell text-center">Ações</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
        <?php foreach ($impressoras as $impressora): ?>
        <tr class="table-row">
          <td class="table-cell font-medium"><?= htmlspecialchars($impressora['codigo']) ?></td>
          <td class="table-cell"><?= htmlspecialchars($impressora['modelo']) ?></td>
          <td class="table-cell"><?= htmlspecialchars($impressora['localizacao']) ?></td>
          <td class="table-cell text-center flex flex-wrap gap-2 justify-center">
            <a href="/controle-toner/editar_impressora?id=<?= $impressora['id'] ?>" class="primary-btn !px-3 !py-2 text-xs"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.38-2.828-2.829z"/></svg>Editar</a>
            <a href="#" class="danger-btn !px-3 !py-2 text-xs delete-impressora" data-id="<?= $impressora['id'] ?>"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>Excluir</a>
            <a href="/controle-toner/gerenciar_compatibilidade?impressora_id=<?= $impressora['id'] ?>" class="neutral-btn !px-3 !py-2 text-xs"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>Suprimentos</a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="flex items-center justify-between mt-6">
    <span class="text-xs text-gray-500 dark:text-gray-400">Total: <?= $totalPrinters ?> impressoras</span>
    <div class="flex gap-2 items-center">
      <?php if($page>1): ?><a href="?p=<?= $page-1 ?>&order=<?= htmlspecialchars($order) ?>" class="neutral-btn !px-3 !py-1 text-xs">Anterior</a><?php endif; ?>
      <span class="text-xs text-gray-500 dark:text-gray-400">Página <?= $page ?> / <?= $totalPages ?></span>
      <?php if($page<$totalPages): ?><a href="?p=<?= $page+1 ?>&order=<?= htmlspecialchars($order) ?>" class="neutral-btn !px-3 !py-1 text-xs">Próxima</a><?php endif; ?>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/../../layout/footer.php'; ?>