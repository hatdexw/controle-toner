<?php
require __DIR__ . '/src/db/connection.php';

require 'layout/header.php';

// Prepare toast messages
if (isset($_SESSION['message'])) {
    $message_type = $_SESSION['message']['type'];
    $message_text = $_SESSION['message']['text'];
    echo "<script>window.appMessage = { type: '$message_type', text: '$message_text' };</script>";
    unset($_SESSION['message']);
}

$stmt = $pdo->query(
    'SELECT i.id, i.codigo, i.modelo, i.localizacao, i.toner_status, 
            i.ink_black, i.ink_cyan, i.ink_magenta, i.ink_yellow, i.ink_photo_black, i.ink_gray, 
            h.data_troca, s.modelo as suprimento_modelo, s.tipo 
     FROM impressoras i 
     LEFT JOIN (SELECT impressora_id, MAX(data_troca) as max_data FROM historico_trocas GROUP BY impressora_id) as ht 
     ON i.id = ht.impressora_id
     LEFT JOIN historico_trocas h ON h.impressora_id = i.id AND h.data_troca = ht.max_data
     LEFT JOIN suprimentos s ON s.id = h.suprimento_id
     ORDER BY i.codigo'
);

?>

<h1 class="text-4xl font-extrabold text-gray-900 mb-8 text-center">Impressoras</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php while ($impressora = $stmt->fetch()) {
    ?>
        <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200 flex flex-col transform transition-transform duration-300 hover:scale-105">
            <div class="flex items-center mb-4">
                <!-- Icone de Impressora (SVG simples) -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-indigo-600 mr-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                <div>
                    <h3 class="text-2xl font-bold text-gray-800"><?= htmlspecialchars($impressora['codigo']) ?></h3>
                    <p class="text-gray-600 text-sm">Modelo: <span class="font-medium"><?= htmlspecialchars($impressora['modelo']) ?></span></p>
                </div>
            </div>
            
            <p class="text-gray-700 text-base mb-4">Localizacao: <span class="font-semibold"><?= htmlspecialchars($impressora['localizacao']) ?></span></p>

            <?php 
            $modelo_upper = strtoupper($impressora['modelo']);
            // Lógica para Epson L8180
            if (strpos($modelo_upper, 'L8180') !== false) : 
                $inks = [
                    'Preto Foto' => ['level' => $impressora['ink_photo_black'], 'color' => 'bg-gray-700'],
                    'Preto' => ['level' => $impressora['ink_black'], 'color' => 'bg-black'],
                    'Ciano' => ['level' => $impressora['ink_cyan'], 'color' => 'bg-cyan-500'],
                    'Magenta' => ['level' => $impressora['ink_magenta'], 'color' => 'bg-fuchsia-600'],
                    'Amarelo' => ['level' => $impressora['ink_yellow'], 'color' => 'bg-yellow-400'],
                    'Cinza' => ['level' => $impressora['ink_gray'], 'color' => 'bg-gray-400'],
                ];
            ?>
                <div class="mb-4 flex justify-center">
                    <div class="flex space-x-4 h-24 items-end">
                        <?php foreach ($inks as $name => $details): ?>
                            <?php 
                            $level = $details['level'] !== null ? $details['level'] : 0; 
                            $color = $details['color'];
                            ?>
                                <div class="flex flex-col items-center h-full justify-end">
                                    <div class="relative w-6 bg-gray-200 h-full overflow-hidden flex flex-col justify-end">
                                        <div class="<?= $color ?> w-full flex items-center justify-center" style="height: <?= max(1, $level) ?>%">
                                        </div>
                                    </div>
                                    <p class="text-xs font-medium text-gray-600 mt-2 text-center"><?= $name ?></p>
                                </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php 
            // Lógica para HP e Brother
            elseif (strpos($modelo_upper, 'HP') !== false || strpos($modelo_upper, 'BROTHER') !== false) : 
            ?>
                <div class="mb-4">
                    <p class="text-sm font-semibold text-gray-700 mb-1">Toner:</p>
                    <div class="relative w-full bg-gray-200 rounded-full h-4 dark:bg-gray-700">
                        <div class="absolute top-0 right-0 h-full bg-blue-600 rounded-full" style="width: <?= $impressora['toner_status'] ?>%;"></div>
                        <div class="relative flex items-center justify-center h-full">
                            <span class="font-bold text-xs text-white"><?= $impressora['toner_status'] ?>%</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="bg-gray-50 p-4 rounded-lg mb-6 flex-grow">
                <?php if ($impressora['data_troca']) : ?>
                    <p class="text-sm text-gray-700 mb-1">Ultima troca: <span class="font-medium text-indigo-700"><?= date('d/m/Y H:i', strtotime($impressora['data_troca'])) ?></span></p>
                    <p class="text-sm text-gray-700">Suprimento: <span class="font-medium text-indigo-700"><?= htmlspecialchars($impressora['suprimento_modelo']) ?> (<?= htmlspecialchars($impressora['tipo']) ?>)</span></p>
                <?php else : ?>
                    <p class="text-sm text-gray-500 italic">Nenhuma troca registrada ainda.</p>
                <?php endif; ?>
            </div>
            <div class="mt-auto text-right">
                <a href="trocar?impressora_id=<?= $impressora['id'] ?>" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V3a1 1 0 00-1-1h-6z" />
                        <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                    </svg>
                    Registrar Troca
                </a>
            </div>
        </div>
    <?php } ?>
</div>

<?php require 'layout/footer.php'; ?>