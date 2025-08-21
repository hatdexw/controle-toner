<?php
require_once __DIR__ . '/../../layout/header.php';

// Prepare toast messages
if (isset($_SESSION['message'])) {
    $message_type = $_SESSION['message']['type'];
    $message_text = $_SESSION['message']['text'];
    echo "<script>window.appMessage = { type: '$message_type', text: '$message_text' };</script>";
    unset($_SESSION['message']);
}


// Obter todas as impressoras para o filtro
$impressoras_filtro = $pdo->query('SELECT id, codigo, modelo FROM impressoras ORDER BY codigo')->fetchAll();

// Obter todos os suprimentos para o filtro
$suprimentos_filtro = $pdo->query('SELECT id, modelo, tipo FROM suprimentos ORDER BY modelo')->fetchAll();

// Construir a consulta base
$sql = 'SELECT h.data_troca, i.codigo, i.modelo as impressora_modelo, i.localizacao, s.modelo as suprimento_modelo, s.tipo
        FROM historico_trocas h
        JOIN impressoras i ON h.impressora_id = i.id
        JOIN suprimentos s ON h.suprimento_id = s.id';

$conditions = [];
$params = [];

// Aplicar filtros
if (isset($_GET['data_inicio']) && !empty($_GET['data_inicio'])) {
    $conditions[] = 'h.data_troca >= ?';
    $params[] = $_GET['data_inicio'] . ' 00:00:00';
}
if (isset($_GET['data_fim']) && !empty($_GET['data_fim'])) {
    $conditions[] = 'h.data_troca <= ?';
    $params[] = $_GET['data_fim'] . ' 23:59:59';
}
if (isset($_GET['impressora_id']) && !empty($_GET['impressora_id'])) {
    $conditions[] = 'h.impressora_id = ?';
    $params[] = $_GET['impressora_id'];
}
if (isset($_GET['suprimento_id']) && !empty($_GET['suprimento_id'])) {
    $conditions[] = 'h.suprimento_id = ?';
    $params[] = $_GET['suprimento_id'];
}

if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$sql .= ' ORDER BY h.data_troca DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$historico = $stmt->fetchAll();

// Construir query string para exportacao PDF
$pdf_query_string = http_build_query($_GET);
$pdf_export_link = '/controle-toner/export_pdf' . (!empty($pdf_query_string) ? '?' . $pdf_query_string : '');

?>

<h1 class="section-title">Historico de Trocas</h1>

<div class="glass-card p-8 mb-8 anim-hover-lift">
    <div class="card-header mb-4">
        <h2 class="card-title">Filtrar Historico</h2>
    </div>
    <form method="GET" action="/controle-toner/historico" class="grid grid-cols-1 md:grid-cols-5 gap-6 items-end">
        <div>
            <label for="data_inicio" class="field-label">Data Início</label>
            <input type="date" name="data_inicio" id="data_inicio" value="<?= htmlspecialchars($_GET['data_inicio'] ?? '') ?>" class="form-input">
        </div>
        <div>
            <label for="data_fim" class="field-label">Data Fim</label>
            <input type="date" name="data_fim" id="data_fim" value="<?= htmlspecialchars($_GET['data_fim'] ?? '') ?>" class="form-input">
        </div>
        <div>
            <label for="impressora_id" class="field-label">Impressora</label>
            <select name="impressora_id" id="impressora_id" class="form-select">
                <option value="">Todas</option>
                <?php foreach ($impressoras_filtro as $imp) : ?>
                    <option value="<?= $imp['id'] ?>" <?= (isset($_GET['impressora_id']) && $_GET['impressora_id'] == $imp['id']) ? 'selected' : '' ?>><?= htmlspecialchars($imp['codigo']) ?> - <?= htmlspecialchars($imp['modelo']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="suprimento_id" class="field-label">Suprimento</label>
            <select name="suprimento_id" id="suprimento_id" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($suprimentos_filtro as $sup) : ?>
                    <option value="<?= $sup['id'] ?>" <?= (isset($_GET['suprimento_id']) && $_GET['suprimento_id'] == $sup['id']) ? 'selected' : '' ?>><?= htmlspecialchars($sup['modelo']) ?> (<?= htmlspecialchars($sup['tipo']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex gap-3 md:col-span-1 mt-1">
            <button type="submit" class="primary-btn w-full justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2H3V4zm0 4h14v2H3V8zm0 4h14v2a1 1 0 01-1 1H4a1 1 0 01-1-1v-2z" clip-rule="evenodd" /></svg>
                <span>Filtrar</span>
            </button>
        </div>
    </form>
</div>

<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
    <p class="text-sm text-gray-500 dark:text-gray-400"><?= count($historico) ?> registro(s) encontrado(s)</p>
    <a href="<?= $pdf_export_link ?>" target="_blank" class="primary-btn">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 2a2 2 0 00-2 2v12a2 2 0 002 2h5v-2H4V4h12v5h2V4a2 2 0 00-2-2H4zm9 9V7h2l-3-4-3 4h2v4h2zm-5 2v2h2v-2H8z" clip-rule="evenodd" /></svg>
        <span>Exportar PDF</span>
    </a>
</div>

<div class="glass-card p-8 overflow-hidden">
    <div class="card-header mb-4">
        <h2 class="card-title">Registros de Trocas</h2>
    </div>
    <div class="overflow-x-auto rounded-xl ring-1 ring-black/5 dark:ring-white/10">
        <table class="table-base">
            <thead class="table-head-row">
                <tr>
                    <th class="table-head-cell">Data</th>
                    <th class="table-head-cell">Codigo Imp.</th>
                    <th class="table-head-cell">Modelo Imp.</th>
                    <th class="table-head-cell">Localizacao</th>
                    <th class="table-head-cell">Suprimento</th>
                    <th class="table-head-cell">Tipo</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                <?php if (empty($historico)) : ?>
                    <tr>
                        <td colspan="6" class="table-cell text-center text-gray-500 dark:text-gray-400">Nenhum registro encontrado com os filtros aplicados.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($historico as $troca) : ?>
                        <tr class="table-row">
                            <td class="table-cell font-medium text-gray-900 dark:text-gray-100"><?= date('d/m/Y H:i', strtotime($troca['data_troca'])) ?></td>
                            <td class="table-cell"><?= htmlspecialchars($troca['codigo']) ?></td>
                            <td class="table-cell"><?= htmlspecialchars($troca['impressora_modelo']) ?></td>
                            <td class="table-cell"><?= htmlspecialchars($troca['localizacao']) ?></td>
                            <td class="table-cell"><?= htmlspecialchars($troca['suprimento_modelo']) ?></td>
                            <td class="table-cell"><?= htmlspecialchars($troca['tipo']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>