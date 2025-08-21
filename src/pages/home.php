<?php
require __DIR__ . '/../../layout/header.php';

// Mensagens de feedback
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
<h1 class="section-title">Impressoras</h1>
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 fade-in">
    <div class="flex gap-2 flex-wrap">
        <button data-sort="codigo" class="neutral-btn !py-2 !px-4 sort-btn" aria-label="Ordenar por código">Ordenar Código</button>
        <button data-sort="toner" class="neutral-btn !py-2 !px-4 sort-btn" aria-label="Ordenar por nível de toner">Ordenar Toner</button>
        <button data-sort="troca" class="neutral-btn !py-2 !px-4 sort-btn" aria-label="Ordenar por última troca">Ordenar Última Troca</button>
    </div>
    <div class="flex gap-3 items-center">
        <label class="field-label mb-0">Filtro</label>
        <input type="text" id="printerFilter" placeholder="Filtrar por código/modelo/localização" class="form-input w-72" />
    </div>
</div>
<div id="printersGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 auto-rows-fr stagger">
<?php while ($impressora = $stmt->fetch()) { ?>
<?php
    $toner_status = $impressora['toner_status'];
    $modelo_upper = strtoupper($impressora['modelo']);
    $is_special_hp = ($modelo_upper === 'IMPRESSORA HP 4003DW PRO');
    $is_toner_printer = !$is_special_hp && (strpos($modelo_upper, 'HP') !== false || strpos($modelo_upper, 'BROTHER') !== false);
    $is_l8180 = strpos($modelo_upper, 'L8180') !== false;
    $is_empty = $is_toner_printer && $toner_status !== null && $toner_status == 0;
    $is_low = $is_toner_printer && !$is_empty && $toner_status !== null && $toner_status <= 15;
    if ($is_special_hp) { $card_class_root = 'glass-card-warning'; $icon_classes = 'text-yellow-500'; }
    elseif ($is_empty) { $card_class_root = 'glass-card-critical'; $icon_classes = 'text-red-500'; }
    elseif ($is_low) { $card_class_root = 'glass-card-warning'; $icon_classes = 'text-brand-500'; }
    else { $card_class_root = 'glass-card'; $icon_classes = 'text-brand-500'; }
    $toner_bar_class = $is_empty ? 'toner-empty' : '';
    // Cor dinâmica da barra (verde>amarelo>laranja>vermelho)
    $bar_color = 'from-green-500 to-green-400';
    if ($toner_status !== null) {
        if ($toner_status <= 15) $bar_color = 'from-red-600 to-red-500';
        elseif ($toner_status <= 30) $bar_color = 'from-orange-500 to-orange-400';
        elseif ($toner_status <= 50) $bar_color = 'from-yellow-400 to-yellow-300';
    }
?>
    <div class="<?= $card_class_root ?> p-6 flex flex-col anim-hover-lift fade-in" data-codigo="<?= htmlspecialchars($impressora['codigo']) ?>" data-toner="<?= (int)($toner_status ?? 0) ?>" data-troca="<?= $impressora['data_troca'] ? strtotime($impressora['data_troca']) : 0 ?>">
        <div class="flex items-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mr-4 <?= $icon_classes ?>" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            <div>
                <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100"><?= htmlspecialchars($impressora['codigo']) ?></h3>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Modelo: <span class="font-medium"><?= htmlspecialchars($impressora['modelo']) ?></span></p>
            </div>
        </div>
        <p class="text-gray-700 dark:text-gray-300 text-sm mb-4">Localização: <span class="font-semibold"><?= htmlspecialchars($impressora['localizacao']) ?></span></p>
        <?php if ($is_special_hp): ?>
            <div class="mb-4 p-4 bg-yellow-100 rounded-lg">
                <p class="text-center font-semibold text-yellow-800 text-sm">Toner não original encontrado</p>
            </div>
        <?php elseif ($is_l8180): 
            $inks = [
                'Preto Foto' => ['level' => $impressora['ink_photo_black'], 'color' => 'bg-gray-700'],
                'Preto' => ['level' => $impressora['ink_black'], 'color' => 'bg-black'],
                'Ciano' => ['level' => $impressora['ink_cyan'], 'color' => 'bg-cyan-500'],
                'Magenta' => ['level' => $impressora['ink_magenta'], 'color' => 'bg-fuchsia-600'],
                'Amarelo' => ['level' => $impressora['ink_yellow'], 'color' => 'bg-yellow-400'],
                'Cinza' => ['level' => $impressora['ink_gray'], 'color' => 'bg-gray-400'],
            ]; ?>
            <div class="mb-4 flex justify-center">
                <div class="flex space-x-4 h-28 items-end">
                    <?php foreach ($inks as $name => $details): $level = $details['level'] !== null ? $details['level'] : 0; $color = $details['color']; ?>
                    <div class="flex flex-col items-center h-full justify-end">
                        <div class="ink-column">
                            <div class="ink-fill <?= $color ?>" style="height: <?= max(1,$level) ?>%"></div>
                        </div>
                        <p class="text-[10px] font-medium text-gray-600 dark:text-gray-400 mt-2 text-center"><?= $name ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php elseif ($is_toner_printer): ?>
            <div class="mb-4">
                <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1 flex items-center gap-2">Toner
                  <span class="badge" title="Nível de toner"><?= $toner_status ?? 0 ?>%</span>
                </p>
                <div class="toner-bar-wrapper gradient-border" title="<?= $toner_status ?? 0 ?>% restante(s)">
                    <div class="toner-bar <?= $toner_bar_class ?> bg-gradient-to-r <?= $bar_color ?>" style="width: <?= $toner_status ?? 0 ?>%"></div>
                </div>
                <?php if ($is_empty): ?><p class="text-red-600 dark:text-red-400 font-bold text-xs mt-1 text-center animate-pulse">Toner Vazio!</p><?php elseif ($is_low): ?><p class="text-yellow-500 dark:text-yellow-400 text-[10px] mt-1 text-center">Baixo - programe troca</p><?php endif; ?>
            </div>
        <?php endif; ?>
        <div class="bg-gray-50 dark:bg-gray-800/40 p-4 rounded-lg mb-6 flex-grow border border-gray-100 dark:border-white/5">
            <?php if ($impressora['data_troca']): ?>
                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Última troca: <span class="font-medium text-brand-600 dark:text-brand-400"><?= date('d/m/Y H:i', strtotime($impressora['data_troca'])) ?></span></p>
                <p class="text-xs text-gray-600 dark:text-gray-400">Suprimento: <span class="font-medium text-brand-600 dark:text-brand-400"><?= htmlspecialchars($impressora['suprimento_modelo']) ?> (<?= htmlspecialchars($impressora['tipo']) ?>)</span></p>
            <?php else: ?>
                <p class="text-xs text-gray-500 dark:text-gray-400 italic">Nenhuma troca registrada ainda.</p>
            <?php endif; ?>
        </div>
        <div class="mt-auto text-right flex justify-between items-center gap-3">
            <span class="text-[10px] text-gray-500 dark:text-gray-500 tracking-wide">ID #<?= $impressora['id'] ?></span>
            <a href="/controle-toner/trocar?impressora_id=<?= $impressora['id'] ?>" class="primary-btn mt-2" aria-label="Registrar troca para <?= htmlspecialchars($impressora['codigo']) ?>">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V3a1 1 0 00-1-1h-6z" /><path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" /></svg>
                Registrar Troca
            </a>
        </div>
    </div>
<?php } ?>
</div>
<script>
// Sorting & filtering (client side)
document.addEventListener('DOMContentLoaded',()=>{
    const grid = document.getElementById('printersGrid');
    const cards = Array.from(grid.children);
    function applyFilterAndSort(sort){
        const term = (document.getElementById('printerFilter').value||'').toLowerCase();
        let filtered = cards.filter(c=>{
            const codigo = c.getAttribute('data-codigo').toLowerCase();
            return !term || codigo.includes(term) || c.textContent.toLowerCase().includes(term);
        });
        if(sort==='codigo') filtered.sort((a,b)=> a.getAttribute('data-codigo').localeCompare(b.getAttribute('data-codigo')));
        if(sort==='toner') filtered.sort((a,b)=> parseInt(b.getAttribute('data-toner'))-parseInt(a.getAttribute('data-toner')));
        if(sort==='troca') filtered.sort((a,b)=> parseInt(b.getAttribute('data-troca'))-parseInt(a.getAttribute('data-troca')));
        grid.innerHTML=''; filtered.forEach(el=>grid.appendChild(el));
    }
    let currentSort='codigo';
    document.querySelectorAll('.sort-btn').forEach(btn=>btn.addEventListener('click',()=>{currentSort=btn.dataset.sort;applyFilterAndSort(currentSort);}));
    document.getElementById('printerFilter').addEventListener('input',()=>applyFilterAndSort(currentSort));
});
</script>
<?php require __DIR__ . '/../../layout/footer.php'; ?>
