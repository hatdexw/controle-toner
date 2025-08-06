<?php
require_once __DIR__ . '/../../layout/header.php';
require_once __DIR__ . '/../actions/editar_impressora_actions.php';

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

<h1 class="text-4xl font-extrabold text-gray-900 mb-8 text-center">Editar Impressora</h1>

<?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
        <span class="block sm:inline font-medium"><?= $error ?></span>
    </div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-lg p-8 mb-8 border border-gray-200">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Detalhes da Impressora</h2>
    <form method="POST" action="/controle-toner/editar_impressora?id=<?= $impressora['id'] ?>" class="grid grid-cols-1 md:grid-cols-3 gap-6 items-end">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
        <div>
            <label for="codigo" class="block text-sm font-semibold text-gray-700 mb-1">Codigo</label>
            <input type="text" name="codigo" id="codigo" value="<?= htmlspecialchars($impressora['codigo']) ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3" required>
        </div>
        <div>
            <label for="modelo" class="block text-sm font-semibold text-gray-700 mb-1">Modelo</label>
            <input type="text" name="modelo" id="modelo" value="<?= htmlspecialchars($impressora['modelo']) ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3" required>
        </div>
        <div>
            <label for="localizacao" class="block text-sm font-semibold text-gray-700 mb-1">Localizacao</label>
            <input type="text" name="localizacao" id="localizacao" value="<?= htmlspecialchars($impressora['localizacao']) ?>" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3" required>
        </div>
        <div class="md:col-span-3 flex justify-end space-x-4 mt-4">
            <button type="submit" class="inline-flex justify-center items-center py-3 px-6 border border-transparent shadow-sm text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Atualizar Impressora
            </button>
            <a href="/controle-toner/impressoras" class="inline-flex justify-center items-center py-3 px-6 border border-gray-300 shadow-sm text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0L6 12.414l-1.293 1.293a1 1 0 01-1.414-1.414l2-2a1 1 0 011.414 0l2 2 1.293-1.293a1 1 0 011.414 1.414L9.707 14.707z" clip-rule="evenodd" />
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM10 2a8 8 0 100 16 8 8 0 000-16z" clip-rule="evenodd" />
                </svg>
                Cancelar
            </a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../layout/footer.php'; ?>