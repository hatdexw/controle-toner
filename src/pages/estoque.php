<?php
// Processa ações (POST) antes de enviar HTML
require_once __DIR__ . '/../actions/estoque_actions.php';
require_once __DIR__ . '/../../layout/header.php';

$stmt = $pdo->query('SELECT * FROM suprimentos ORDER BY modelo');
$suprimentos = $stmt->fetchAll();
if (isset($_SESSION['message'])) { $message_type = $_SESSION['message']['type']; $message_text = $_SESSION['message']['text']; echo "<script>window.appMessage = { type: '{$message_type}', text: '{$message_text}' };</script>"; unset($_SESSION['message']); }
?>
<h1 class="section-title">Estoque de Suprimentos</h1>
<div class="glass-card p-8 mb-8 anim-hover-lift fade-in">
  <div class="card-header"><h2 class="card-title">Adicionar Novo Suprimento</h2><span class="badge">Cadastro</span></div>
  <form action="/controle-toner/estoque" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 items-end">
    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
    <div><label for="modelo" class="field-label">Modelo</label><input type="text" name="modelo" id="modelo" placeholder="Ex: Toner HP 85A" class="form-input" required></div>
    <div><label for="tipo" class="field-label">Tipo</label><select name="tipo" id="tipo" class="form-select" required><option value="Toner">Toner</option><option value="Fotocondutor">Fotocondutor</option><option value="Cartucho">Cartucho</option><option value="Tinta">Tinta</option></select></div>
    <div><label for="quantidade" class="field-label">Quantidade</label><input type="number" name="quantidade" id="quantidade" placeholder="0" class="form-input" required min="0"></div>
    <div><label class="field-label">&nbsp;</label><button type="submit" name="add_suprimento" class="primary-btn w-full justify-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/></svg>Adicionar</button></div>
  </form>
</div>
<div class="glass-card p-8 fade-in anim-hover-lift">
  <div class="card-header mb-2"><h2 class="card-title">Estoque Atual</h2><span class="badge">Lista</span></div>
  <div class="mb-4"><input type="text" id="searchSuprimento" placeholder="Buscar suprimento por modelo ou tipo..." class="form-input"></div>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="suprimentosGrid">
    <?php foreach ($suprimentos as $suprimento): ?>
      <div class="suprimento-card glass-card flex flex-col justify-between anim-hover-lift fade-in">
        <div class="p-6 pb-4"><h3 class="text-base font-semibold text-gray-800 dark:text-gray-100 mb-1 truncate"><?= htmlspecialchars($suprimento['modelo']) ?></h3><p class="text-xs text-gray-500 dark:text-gray-400 mb-2 flex items-center gap-2"><span class="badge !px-2 !py-0.5 !text-[10px]"><?= htmlspecialchars($suprimento['tipo']) ?></span></p></div>
        <div class="bg-gray-50 dark:bg-gray-800/50 p-4 border-t border-gray-100 dark:border-white/5 flex items-center">
          <form action="/controle-toner/estoque" method="POST" class="flex items-center justify-between space-x-2 w-full">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token(); ?>">
            <input type="hidden" name="id" value="<?= $suprimento['id'] ?>">
            <div class="flex-grow flex items-center gap-2">
              <button type="button" class="neutral-btn !px-3 !py-2" onclick="const i=document.getElementById('quantidade-<?= $suprimento['id'] ?>'); i.value=Math.max(0,parseInt(i.value||0)-1)">-</button>
              <label for="quantidade-<?= $suprimento['id'] ?>" class="sr-only">Quantidade</label>
              <input type="number" name="quantidade" id="quantidade-<?= $suprimento['id'] ?>" value="<?= $suprimento['quantidade'] ?>" class="form-input !py-1 !px-2 text-center w-24">
              <button type="button" class="neutral-btn !px-3 !py-2" onclick="const i=document.getElementById('quantidade-<?= $suprimento['id'] ?>'); i.value=parseInt(i.value||0)+1">+</button>
            </div>
            <div class="flex space-x-2">
              <button type="submit" name="update_suprimento" class="primary-btn !px-3 !py-2 rounded-full"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"/><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd"/></svg></button>
              <button type="submit" name="delete_suprimento" class="danger-btn !px-3 !py-2 rounded-full delete-suprimento" data-id="<?= $suprimento['id'] ?>"><svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg></button>
            </div>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<script>
// Busca client-side
const searchInput = document.getElementById('searchSuprimento');
const grid = document.getElementById('suprimentosGrid');
if(searchInput && grid){
  const cards = Array.from(grid.querySelectorAll('.suprimento-card'));
  searchInput.addEventListener('keyup',()=>{
    const f = searchInput.value.toLowerCase();
    cards.forEach(c=>{
      const modelo = c.querySelector('h3').textContent.toLowerCase();
      const tipo = c.querySelector('p').textContent.toLowerCase();
      c.style.display = (modelo.includes(f) || tipo.includes(f)) ? 'flex':'none';
    });
  });
}
</script>
<?php require_once __DIR__ . '/../../layout/footer.php'; ?>