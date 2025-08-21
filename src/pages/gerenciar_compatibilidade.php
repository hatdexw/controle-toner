<?php
// Lógica de ação antes do header para evitar problemas de redirecionamento
require_once __DIR__ . '/../actions/gerenciar_compatibilidade_actions.php';
require_once __DIR__ . '/../../layout/header.php';

// Prepare toast messages
if (isset($_SESSION['message'])) {
    $message_type = $_SESSION['message']['type'];
    $message_text = $_SESSION['message']['text'];
    echo "<script>window.appMessage = { type: '$message_type', text: '$message_text' };</script>";
    unset($_SESSION['message']);
}
?>

<h1 class="section-title">Gerenciar Suprimentos Compativeis</h1>

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
        <div>
            <h2 class="card-title flex items-center gap-2">Impressora <span class="badge !bg-brand-600 !text-white"><?= htmlspecialchars($impressora['codigo']) ?></span></h2>
            <p class="card-subtitle mt-1">Modelo: <?= htmlspecialchars($impressora['modelo']) ?></p>
        </div>
    </div>
    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-brand-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" /></svg>
        Suprimentos Disponiveis
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php if (empty($todos_suprimentos)) : ?>
            <p class="text-gray-500 dark:text-gray-400">Nenhum suprimento cadastrado.</p>
        <?php else : ?>
            <?php foreach ($todos_suprimentos as $suprimento) : ?>
                <div class="glass-card p-4 shadow-sm anim-hover-lift border border-transparent hover:border-brand-400/40 transition relative">
                    <div class="flex flex-col gap-2">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-gray-100 leading-tight"><?= htmlspecialchars($suprimento['modelo']) ?></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide"><?= htmlspecialchars($suprimento['tipo']) ?></p>
                        </div>
                        <div class="mt-1">
                            <?php if (in_array($suprimento['id'], $suprimentos_associados)) : ?>
                                <form method="POST" action="/controle-toner/gerenciar_compatibilidade?impressora_id=<?= $impressora['id'] ?>" class="inline-flex">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
                                    <input type="hidden" name="suprimento_id" value="<?= $suprimento['id'] ?>">
                                    <input type="hidden" name="action" value="remove">
                                    <button type="submit" class="danger-btn !px-3 !py-1.5 !text-xs">
                                        Remover
                                    </button>
                                </form>
                            <?php else : ?>
                                <form method="POST" action="/controle-toner/gerenciar_compatibilidade?impressora_id=<?= $impressora['id'] ?>" class="inline-flex">
                                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
                                    <input type="hidden" name="suprimento_id" value="<?= $suprimento['id'] ?>">
                                    <input type="hidden" name="action" value="add">
                                    <button type="submit" class="primary-btn !px-3 !py-1.5 !text-xs">
                                        Adicionar
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (in_array($suprimento['id'], $suprimentos_associados)) : ?>
                        <span class="absolute top-2 right-2 inline-flex items-center gap-1 rounded-full bg-green-500/20 text-green-600 dark:text-green-300 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide">Ativo</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="mt-8 text-right">
        <a href="/controle-toner/impressoras" class="neutral-btn">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0L2.586 11l3.707-3.707a1 1 0 011.414 1.414L5.414 10H17a1 1 0 110 2H5.414l1.293 1.293a1 1 0 010 1.414z" clip-rule="evenodd" /></svg>
            <span>Voltar</span>
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>