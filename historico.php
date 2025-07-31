<?php
require __DIR__ . '/src/db/connection.php';

require 'layout/header.php';

// Display toast messages
if (isset($_SESSION['message'])) {
    $message_type = $_SESSION['message']['type'];
    $message_text = $_SESSION['message']['text'];
    echo "<script>showToast('$message_type', '$message_text');</script>";
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
$pdf_export_link = 'export_pdf' . (!empty($pdf_query_string) ? '?' . $pdf_query_string : '');

?>

<h1 class="text-4xl font-extrabold text-gray-900 mb-8 text-center">Historico de Trocas</h1>

<div class="bg-white rounded-xl shadow-lg p-8 mb-8 border border-gray-200">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Filtrar Historico</h2>
    <form method="GET" action="historico" class="grid grid-cols-1 md:grid-cols-5 gap-6 items-end">
        <div>
            <label for="data_inicio" class="block text-sm font-semibold text-gray-700 mb-1">Data Inicio</label>
            <input type="date" name="data_inicio" id="data_inicio" value="<?= htmlspecialchars($_GET['data_inicio'] ?? '') ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3">
        </div>
        <div>
            <label for="data_fim" class="block text-sm font-semibold text-gray-700 mb-1">Data Fim</label>
            <input type="date" name="data_fim" id="data_fim" value="<?= htmlspecialchars($_GET['data_fim'] ?? '') ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3">
        </div>
        <div>
            <label for="impressora_id" class="block text-sm font-semibold text-gray-700 mb-1">Impressora</label>
            <select name="impressora_id" id="impressora_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3">
                <option value="">Todas</option>
                <?php foreach ($impressoras_filtro as $imp) : ?>
                    <option value="<?= $imp['id'] ?>" <?= (isset($_GET['impressora_id']) && $_GET['impressora_id'] == $imp['id']) ? 'selected' : '' ?>><?= htmlspecialchars($imp['codigo']) ?> - <?= htmlspecialchars($imp['modelo']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="suprimento_id" class="block text-sm font-semibold text-gray-700 mb-1">Suprimento</label>
            <select name="suprimento_id" id="suprimento_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3">
                <option value="">Todos</option>
                <?php foreach ($suprimentos_filtro as $sup) : ?>
                    <option value="<?= $sup['id'] ?>" <?= (isset($_GET['suprimento_id']) && $_GET['suprimento_id'] == $sup['id']) ? 'selected' : '' ?>><?= htmlspecialchars($sup['modelo']) ?> (<?= htmlspecialchars($sup['tipo']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="inline-flex justify-center items-center py-3 px-6 border border-transparent shadow-sm text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v14a1 1 0 01-1 1H4a1 1 0 01-1-1V3zm2 0v14h10V3H5zm2 2a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm0 4a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1zm0 4a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z" clip-rule="evenodd" />
            </svg>
            Filtrar
        </button>
    </form>
</div>

<div class="mb-6 flex space-x-4 justify-end">
    <a href="<?= $pdf_export_link ?>" target="_blank" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0113 3.414L16.586 7A2 2 0 0117 8.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm0 3a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1z" clip-rule="evenodd" />
        </svg>
        Exportar para PDF
    </a>
</div>

<div class="bg-white rounded-xl shadow-lg p-8 border border-gray-200">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Registros de Trocas</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Codigo Imp.</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modelo Imp.</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Localizacao</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suprimento</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($historico)) : ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Nenhum registro encontrado com os filtros aplicados.</td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($historico as $troca) : ?>
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= date('d/m/Y H:i', strtotime($troca['data_troca'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($troca['codigo']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($troca['impressora_modelo']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($troca['localizacao']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($troca['suprimento_modelo']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($troca['tipo']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'layout/footer.php'; ?>