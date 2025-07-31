<?php
require __DIR__ . '/src/db/connection.php';
require __DIR__ . '/src/actions/trocar_actions.php';
require_once __DIR__ . '/src/core/csrf.php';

// Obter detalhes da impressora
$stmt = $pdo->prepare('SELECT id, codigo, modelo, localizacao FROM impressoras WHERE id = ?');
$stmt->execute([$impressora_id]);
$impressora = $stmt->fetch();

if (!$impressora) {
    header('Location: index');
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
$stmt->execute([$impressora_id]);
$suprimentos = $stmt->fetchAll();

require 'layout/header.php';

// Display toast messages
if (isset($_SESSION['message'])) {
    $message_type = $_SESSION['message']['type'];
    $message_text = $_SESSION['message']['text'];
    echo "<script>showToast('$message_type', '$message_text');</script>";
    unset($_SESSION['message']);
}
?>

<h1 class="text-4xl font-extrabold text-gray-900 mb-8 text-center">Registrar Troca de Suprimento</h1>

<?php if (isset($error)): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6" role="alert">
        <span class="block sm:inline font-medium"><?= $error ?></span>
    </div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-lg p-8 mb-8 border border-gray-200">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Impressora: <span class="text-indigo-600"><?= htmlspecialchars($impressora['codigo']) ?></span> - <?= htmlspecialchars($impressora['modelo']) ?> (<span class="text-gray-600"><?= htmlspecialchars($impressora['localizacao']) ?></span>)</h2>
    <form method="POST" action="trocar?impressora_id=<?= $impressora['id'] ?>" class="grid grid-cols-1 md:grid-cols-2 gap-6 items-end">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
        <div>
            <label for="suprimento_id" class="block text-sm font-semibold text-gray-700 mb-1">Suprimento a ser utilizado</label>
            <select name="suprimento_id" id="suprimento_id" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base p-3 leading-normal transition-all duration-200" required>
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
        <div class="flex space-x-4 mt-4">
            <button type="submit" class="inline-flex justify-center items-center py-3 px-6 border border-transparent shadow-sm text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200" <?= empty($suprimentos) ? 'disabled' : '' ?>>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Registrar Troca
            </button>
            <a href="index" class="inline-flex justify-center items-center py-3 px-6 border border-gray-300 shadow-sm text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0L6 12.414l-1.293 1.293a1 1 0 01-1.414-1.414l2-2a1 1 0 011.414 0l2 2 1.293-1.293a1 1 0 011.414 1.414L9.707 14.707z" clip-rule="evenodd" />
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM10 2a8 8 0 100 16 8 8 0 000-16z" clip-rule="evenodd" />
                </svg>
                Cancelar
            </a>
        </div>
    </form>
</div>

<?php require 'layout/footer.php'; ?>