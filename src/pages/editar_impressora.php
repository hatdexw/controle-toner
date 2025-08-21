<?php
// Executa lógica (POST/validacoes) antes de enviar qualquer saída HTML para evitar "headers already sent"
require_once __DIR__ . '/../actions/editar_impressora_actions.php';
require_once __DIR__ . '/../../layout/header.php';

$stmt = $pdo->prepare('SELECT * FROM impressoras WHERE id = ?');
$stmt->execute([$_GET['id']]);
$impressora = $stmt->fetch();

if (!$impressora) {
    header('Location: /controle-toner/impressoras');
    exit;
}

// Prepare toast messages
if (isset($_SESSION['message'])) {
    $message_type = $_SESSION['message']['type'];
    $message_text = $_SESSION['message']['text'];
    echo "<script>window.appMessage = { type: '$message_type', text: '$message_text' };</script>";
    unset($_SESSION['message']);
}
?>

<h1 class="section-title">Editar Impressora</h1>

<?php if (isset($error)): ?>
    <div class="glass-card border-red-300/40 dark:border-red-500/30 bg-red-50/70 dark:bg-red-900/20 p-5 mb-8 animate-fade-in">
        <div class="flex items-start gap-3">
            <div class="rounded-full bg-red-500/90 text-white w-8 h-8 flex items-center justify-center shadow">!</div>
            <p class="text-sm font-medium text-red-700 dark:text-red-300"><?= $error ?></p>
        </div>
    </div>
<?php endif; ?>

<div class="glass-card p-8 mb-10 anim-hover-lift">
    <div class="card-header mb-6">
        <h2 class="card-title">Detalhes da Impressora</h2>
    </div>
    <form method="POST" action="/controle-toner/editar_impressora?id=<?= $impressora['id'] ?>" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
        <div>
            <label for="codigo" class="field-label">Codigo</label>
            <input type="text" name="codigo" id="codigo" value="<?= htmlspecialchars($impressora['codigo']) ?>" class="form-input" required>
        </div>
        <div>
            <label for="modelo" class="field-label">Modelo</label>
            <input type="text" name="modelo" id="modelo" value="<?= htmlspecialchars($impressora['modelo']) ?>" class="form-input" required>
        </div>
        <div>
            <label for="localizacao" class="field-label">Localizacao</label>
            <input type="text" name="localizacao" id="localizacao" value="<?= htmlspecialchars($impressora['localizacao']) ?>" class="form-input" required>
        </div>
        <div class="md:col-span-3 flex justify-end gap-4 mt-4">
            <button type="submit" class="primary-btn">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                <span>Atualizar Impressora</span>
            </button>
            <a href="/controle-toner/impressoras" class="neutral-btn">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0L2.586 11l3.707-3.707a1 1 0 011.414 1.414L5.414 10H17a1 1 0 110 2H5.414l1.293 1.293a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
                <span>Cancelar</span>
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>