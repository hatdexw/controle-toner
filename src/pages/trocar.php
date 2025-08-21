<?php
// Processa POST antes do header
require_once __DIR__ . '/../actions/trocar_actions.php';
require_once __DIR__ . '/../../layout/header.php';

// Obter detalhes da impressora
$stmt = $pdo->prepare('SELECT id, codigo, modelo, localizacao FROM impressoras WHERE id = ?');
$stmt->execute([$_GET['impressora_id']]);
$impressora = $stmt->fetch();

if (!$impressora) {
    header('Location: /controle-toner/');
    exit;
}

// Obter suprimentos disponiveis (quantidade > 0) e compativeis com a impressora
$stmt = $pdo->prepare(
    'SELECT s.id, s.modelo, s.tipo 
     FROM suprimentos s
     JOIN impressora_suprimento_compativel isc ON s.id = isc.suprimento_id
     WHERE s.quantidade > 0 AND isc.impressora_id = ?
     ORDER BY s.modelo'
);
$stmt->execute([$_GET['impressora_id']]);
$suprimentos = $stmt->fetchAll();

// Prepare toast messages
if (isset($_SESSION['message'])) {
    $message_type = $_SESSION['message']['type'];
    $message_text = $_SESSION['message']['text'];
    echo "<script>window.appMessage = { type: '$message_type', text: '$message_text' };</script>";
    unset($_SESSION['message']);
}
?>

<h1 class="section-title">Registrar Troca de Suprimento</h1>

<?php if (isset($error)): ?>
    <div class="glass-card border-red-300/40 dark:border-red-500/30 bg-red-50/70 dark:bg-red-900/20 p-5 mb-8 animate-fade-in">
        <div class="flex items-start gap-3">
            <div class="rounded-full bg-red-500/90 text-white w-8 h-8 flex items-center justify-center shadow">!</div>
            <p class="text-sm font-medium text-red-700 dark:text-red-300"><?= $error ?></p>
        </div>
    </div>
<?php endif; ?>

<div class="glass-card p-8 mb-10 anim-hover-lift fade-in">
    <div class="card-header mb-6">
        <div>
            <h2 class="card-title flex items-center gap-2">Impressora: <span class="badge !bg-brand-600 !text-white"><?= htmlspecialchars($impressora['codigo']) ?></span></h2>
            <p class="card-subtitle mt-1">Modelo: <?= htmlspecialchars($impressora['modelo']) ?> · <span class="text-gray-600 dark:text-gray-400"><?= htmlspecialchars($impressora['localizacao']) ?></span></p>
        </div>
    </div>
    <form method="POST" action="/controle-toner/trocar?impressora_id=<?= $impressora['id'] ?>" class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
        <div class="col-span-1 md:col-span-2">
            <label for="suprimento_id" class="field-label">Suprimento a ser utilizado</label>
            <select name="suprimento_id" id="suprimento_id" class="form-select" required <?= empty($suprimentos) ? 'disabled' : '' ?>>
                <?php if (empty($suprimentos)) : ?>
                    <option value="">Nenhum suprimento disponivel</option>
                <?php else : ?>
                    <option value="">Selecione um suprimento</option>
                    <?php foreach ($suprimentos as $suprimento) : ?>
                        <option value="<?= $suprimento['id'] ?>"><?= htmlspecialchars($suprimento['modelo']) ?> (<?= htmlspecialchars($suprimento['tipo']) ?>)</option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="flex gap-4 mt-2 col-span-1 md:col-span-2">
            <button type="submit" class="primary-btn" <?= empty($suprimentos) ? 'disabled' : '' ?>>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                <span>Registrar Troca</span>
            </button>
            <a href="/controle-toner/" class="neutral-btn">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0L2.586 11l3.707-3.707a1 1 0 011.414 1.414L5.414 10H17a1 1 0 110 2H5.414l1.293 1.293a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
                <span>Cancelar</span>
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>