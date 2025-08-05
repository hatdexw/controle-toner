<?php
require __DIR__ . '/src/db/connection.php';
require_once __DIR__ . '/src/core/csrf.php';

// A lógica de processamento foi movida para o arquivo de ações.

$stmt = $pdo->query('SELECT * FROM suprimentos ORDER BY modelo');
$suprimentos = $stmt->fetchAll();

require 'layout/header.php';

// Prepara a mensagem para o sistema de notificações global (main.js)
if (isset($_SESSION['message'])) {
    $message_type = $_SESSION['message']['type'];
    $message_text = $_SESSION['message']['text'];
    echo "<script>window.appMessage = { type: '{$message_type}', text: '{$message_text}' };</script>";
    unset($_SESSION['message']);
}
?>

<h1 class="text-4xl font-extrabold text-gray-900 mb-8 text-center">Estoque de Suprimentos</h1>

<!-- Formulário de Adicionar Novo Suprimento -->
<div class="bg-white rounded-xl shadow-lg p-8 mb-8 border border-gray-200">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Adicionar Novo Suprimento</h2>
    <form action="/controle-toner/src/actions/estoque_actions.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 items-end">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
        <div>
            <label for="modelo" class="block text-sm font-semibold text-gray-700 mb-1">Modelo</label>
            <input type="text" name="modelo" id="modelo" placeholder="Ex: Toner HP 85A" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3" required>
        </div>
        <div>
            <label for="tipo" class="block text-sm font-semibold text-gray-700 mb-1">Tipo</label>
            <select name="tipo" id="tipo" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3" required>
                <option value="Toner">Toner</option>
                <option value="Fotocondutor">Fotocondutor</option>
                <option value="Cartucho">Cartucho</option>
                <option value="Tinta">Tinta</option>
            </select>
        </div>
        <div>
            <label for="quantidade" class="block text-sm font-semibold text-gray-700 mb-1">Quantidade</label>
            <input type="number" name="quantidade" id="quantidade" placeholder="0" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3" required min="0">
        </div>
        <button type="submit" name="add_suprimento" class="w-full inline-flex justify-center items-center py-3 px-4 border border-transparent shadow-sm text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
            Adicionar
        </button>
    </form>
</div>

<!-- Estoque Atual -->
<div class="bg-white rounded-xl shadow-lg p-8 border border-gray-200">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Estoque Atual</h2>
    <div class="mb-4">
        <input type="text" id="searchSuprimento" placeholder="Buscar suprimento por modelo ou tipo..." class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 p-3">
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="suprimentosGrid">
        <?php foreach ($suprimentos as $suprimento) : ?>
            <div class="suprimento-card bg-white rounded-lg shadow-md border border-gray-200 flex flex-col justify-between transform hover:scale-105 transition-transform duration-300">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2 truncate"><?= htmlspecialchars($suprimento['modelo']) ?></h3>
                    <p class="text-sm text-gray-600 mb-4"><?= htmlspecialchars($suprimento['tipo']) ?></p>
                </div>
                <div class="bg-gray-50 p-4">
                    <form action="/controle-toner/src/actions/estoque_actions.php" method="POST" class="flex items-center justify-between space-x-2">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
                        <input type="hidden" name="id" value="<?= $suprimento['id'] ?>">
                        <div class="flex-grow">
                            <label for="quantidade-<?= $suprimento['id'] ?>" class="sr-only">Quantidade</label>
                            <input type="number" name="quantidade" id="quantidade-<?= $suprimento['id'] ?>" value="<?= $suprimento['quantidade'] ?>" class="border-gray-300 rounded-md p-2 w-full text-center shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex space-x-2">
                            <button type="submit" name="update_suprimento" class="p-2 text-white bg-blue-600 hover:bg-blue-700 rounded-full shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
                            </button>
                            <button type="submit" name="delete_suprimento" class="p-2 text-white bg-red-600 hover:bg-red-700 rounded-full shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" onclick="return confirm('Tem certeza que deseja excluir este item?');">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
// A lógica de notificação agora é tratada globalmente pelo main.js
// A busca continua aqui pois é específica desta página.
document.addEventListener('DOMContentLoaded', (event) => {
    const searchInput = document.getElementById('searchSuprimento');
    const suprimentosGrid = document.getElementById('suprimentosGrid');
    const cards = suprimentosGrid.querySelectorAll('.suprimento-card');

    if (searchInput) {
        searchInput.addEventListener('keyup', () => {
            const filter = searchInput.value.toLowerCase();
            cards.forEach(card => {
                const modelo = card.querySelector('h3').textContent.toLowerCase();
                const tipo = card.querySelector('p').textContent.toLowerCase();
                if (modelo.includes(filter) || tipo.includes(filter)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});
</script>

<?php require 'layout/footer.php'; ?>