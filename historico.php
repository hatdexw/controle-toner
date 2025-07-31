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

$stmt = $pdo->query(
    'SELECT h.data_troca, i.codigo, i.modelo as impressora_modelo, i.localizacao, s.modelo as suprimento_modelo, s.tipo
     FROM historico_trocas h
     JOIN impressoras i ON h.impressora_id = i.id
     JOIN suprimentos s ON h.suprimento_id = s.id
     ORDER BY h.data_troca DESC'
);
$historico = $stmt->fetchAll();
?>

<h1 class="text-4xl font-extrabold text-gray-900 mb-8 text-center">Historico de Trocas</h1>

<div class="mb-6 flex space-x-4 justify-end">
    <!-- <a href="export_excel.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M3 17a1 1 0 01-1-1V6a1 1 0 011-1h14a1 1 0 011 1v10a1 1 0 01-1 1H3zm5-9a1 1 0 00-1 1v3a1 1 0 102 0V9a1 1 0 00-1-1zm4 0a1 1 0 00-1 1v3a1 1 0 102 0V9a1 1 0 00-1-1z" clip-rule="evenodd" />
        </svg>
        Exportar para Excel
    </a> -->
    <a href="export_pdf" target="_blank" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
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
            </tbody>
        </table>
    </div>
</div>

<?php require 'layout/footer.php'; ?>