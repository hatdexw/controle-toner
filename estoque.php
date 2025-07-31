<?php
require __DIR__ . '/src/db/connection.php';
require __DIR__ . '/src/actions/estoque_actions.php';
require_once __DIR__ . '/src/core/csrf.php';

$stmt = $pdo->query('SELECT * FROM suprimentos ORDER BY modelo');
$suprimentos = $stmt->fetchAll();

require 'layout/header.php';
?>

<h1 class="text-4xl font-extrabold text-gray-900 mb-8 text-center">Estoque de Suprimentos</h1>

<div class="bg-white rounded-xl shadow-lg p-8 mb-8 border border-gray-200">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Adicionar Novo Suprimento</h2>
    <form method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
        <div>
            <label for="modelo" class="block text-sm font-semibold text-gray-700 mb-1">Modelo do Suprimento</label>
            <input type="text" name="modelo" id="modelo" placeholder="Ex: Toner HP 85A" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3" required>
        </div>
        <div>
            <label for="tipo" class="block text-sm font-semibold text-gray-700 mb-1">Tipo</label>
            <select name="tipo" id="tipo" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3" required>
                <option value="Toner">Toner</option>
                <option value="Fotocondutor">Fotocondutor</option>
                <option value="Cartucho">Cartucho</option>
                <option value="Tinta">Tinta</option>
            </select>
        </div>
        <div>
            <label for="quantidade" class="block text-sm font-semibold text-gray-700 mb-1">Quantidade</label>
            <input type="number" name="quantidade" id="quantidade" placeholder="0" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-3" required min="0">
        </div>
        <button type="submit" name="add_suprimento" class="inline-flex justify-center items-center py-3 px-6 border border-transparent shadow-sm text-base font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Adicionar Suprimento
        </button>
    </form>
</div>

<div class="bg-white rounded-xl shadow-lg p-8 border border-gray-200">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Estoque Atual</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modelo</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantidade</th>
                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acoes</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($suprimentos as $suprimento) : ?>
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($suprimento['modelo']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= htmlspecialchars($suprimento['tipo']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <form method="POST" class="flex items-center space-x-2">
                                <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
                                <input type="hidden" name="id" value="<?= $suprimento['id'] ?>">
                                <input type="number" name="quantidade" value="<?= $suprimento['quantidade'] ?>" class="border border-gray-300 p-2 rounded-md w-20 text-center text-sm focus:border-blue-500 focus:ring-blue-500">
                                <button type="submit" name="update_suprimento" class="inline-flex items-center px-4 py-2 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L10 11.586l-2.293-2.293z" />
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12z" clip-rule="evenodd" />
                                    </svg>
                                    Atualizar
                                </button>
                            </form>
                            <?php if ($suprimento['quantidade'] <= 5) : ?>
                                <div class="mt-2 flex items-center text-xs text-red-600 font-semibold">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.21 3.03-1.742 3.03H4.42c-1.532 0-2.492-1.696-1.742-3.03l5.58-9.92zM10 13a1 1 0 110-2 1 1 0 010 2zm-1-4a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" />
                                    </svg>
                                    Estoque baixo!
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <a href="estoque?delete=<?= $suprimento['id'] ?>" onclick="return confirm('Tem certeza que deseja excluir este suprimento? Isso pode afetar o historico de trocas.')" class="inline-flex items-center px-4 py-2 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                Excluir
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require 'layout/footer.php'; ?>