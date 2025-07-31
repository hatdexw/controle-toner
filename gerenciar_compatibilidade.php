<?php
require __DIR__ . '/src/db/connection.php';
require __DIR__ . '/src/actions/gerenciar_compatibilidade_actions.php';
require_once __DIR__ . '/src/core/csrf.php';

require 'layout/header.php';

// Prepare toast messages
if (isset($_SESSION['message'])) {
    $message_type = $_SESSION['message']['type'];
    $message_text = $_SESSION['message']['text'];
    echo "<script>window.appMessage = { type: '$message_type', text: '$message_text' };</script>";
    unset($_SESSION['message']);
}
?>

<h1 class="text-4xl font-extrabold text-gray-900 mb-8 text-center">Gerenciar Suprimentos Compativeis</h1>

<?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
        <span class="block sm:inline font-medium"><?= $error ?></span>
    </div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-lg p-8 mb-8 border border-gray-200">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Impressora: <span class="text-indigo-600"><?= htmlspecialchars($impressora['codigo']) ?></span> - <?= htmlspecialchars($impressora['modelo']) ?></h2>

    <h3 class="text-xl font-bold text-gray-700 mb-4">Suprimentos Disponiveis</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <?php if (empty($todos_suprimentos)) : ?>
            <p class="text-gray-600">Nenhum suprimento cadastrado.</p>
        <?php else : ?>
            <?php foreach ($todos_suprimentos as $suprimento) : ?>
                <div class="flex items-center justify-between bg-gray-50 p-4 rounded-lg shadow-sm border border-gray-200">
                    <div>
                        <p class="font-medium text-gray-900"><?= htmlspecialchars($suprimento['modelo']) ?></p>
                        <p class="text-sm text-gray-600"><?= htmlspecialchars($suprimento['tipo']) ?></p>
                    </div>
                    <?php if (in_array($suprimento['id'], $suprimentos_associados)) : ?>
                        <form method="POST" action="gerenciar_compatibilidade?impressora_id=<?= $impressora['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
                            <input type="hidden" name="suprimento_id" value="<?= $suprimento['id'] ?>">
                            <input type="hidden" name="action" value="remove">
                            <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                Remover
                            </button>
                        </form>
                    <?php else : ?>
                        <form method="POST" action="gerenciar_compatibilidade?impressora_id=<?= $impressora['id'] ?>">
                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
                            <input type="hidden" name="suprimento_id" value="<?= $suprimento['id'] ?>">
                            <input type="hidden" name="action" value="add">
                            <button type="submit" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                                Adicionar
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="mt-8 text-right">
        <a href="impressoras" class="inline-flex justify-center items-center py-3 px-6 border border-gray-300 shadow-sm text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0L6 12.414l-1.293 1.293a1 1 0 01-1.414-1.414l2-2a1 1 0 011.414 0l2 2 1.293-1.293a1 1 0 011.414 1.414L9.707 14.707z" clip-rule="evenodd" />
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM10 2a8 8 0 100 16 8 8 0 000-16z" clip-rule="evenodd" />
            </svg>
            Voltar para Impressoras
        </a>
    </div>
</div>

<?php require 'layout/footer.php'; ?>