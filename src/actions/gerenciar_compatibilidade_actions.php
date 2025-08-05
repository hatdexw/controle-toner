<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../core/csrf.php';

if (!isset($_GET['impressora_id'])) {
    header('Location: impressoras');
    exit;
}

$impressora_id = $_GET['impressora_id'];

// Processar adicao/remocao de compatibilidade
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Falha na verificação de segurança.'];
    } else {
        if (isset($_POST['suprimento_id'])) {
            $suprimento_id = $_POST['suprimento_id'];

            if (isset($_POST['action']) && $_POST['action'] === 'add') {
                try {
                    $stmt = $pdo->prepare('INSERT INTO impressora_suprimento_compativel (impressora_id, suprimento_id) VALUES (?, ?)');
                    $stmt->execute([$impressora_id, $suprimento_id]);
                    $_SESSION['message'] = ['type' => 'success', 'text' => "Compatibilidade adicionada com sucesso!"];
                } catch (PDOException $e) {
                    // Ignorar erro de duplicidade (se ja for compativel)
                    if ($e->errorInfo[1] != 1062) { // 1062 is duplicate entry error code
                        $_SESSION['message'] = ['type' => 'error', 'text' => "Erro ao adicionar compatibilidade."];
                    }
                }
            } elseif (isset($_POST['action']) && $_POST['action'] === 'remove') {
                try {
                    $stmt = $pdo->prepare('DELETE FROM impressora_suprimento_compativel WHERE impressora_id = ? AND suprimento_id = ?');
                    $stmt->execute([$impressora_id, $suprimento_id]);
                    $_SESSION['message'] = ['type' => 'success', 'text' => "Compatibilidade removida com sucesso!"];
                } catch (PDOException $e) {
                    $_SESSION['message'] = ['type' => 'error', 'text' => "Erro ao remover compatibilidade."];
                }
            }
        }
    }
    // Redirecionar para evitar reenvio do formulario
    header('Location: gerenciar_compatibilidade?impressora_id=' . $impressora_id);
    exit;
}

// Obter detalhes da impressora
$stmt = $pdo->prepare('SELECT id, codigo, modelo FROM impressoras WHERE id = ?');
$stmt->execute([$impressora_id]);
$impressora = $stmt->fetch();

if (!$impressora) {
    header('Location: impressoras');
    exit;
}

// Obter todos os suprimentos
$stmt = $pdo->query('SELECT id, modelo, tipo FROM suprimentos ORDER BY modelo');
$todos_suprimentos = $stmt->fetchAll();

// Obter suprimentos ja associados a esta impressora
$stmt = $pdo->prepare('SELECT suprimento_id FROM impressora_suprimento_compativel WHERE impressora_id = ?');
$stmt->execute([$impressora_id]);
$suprimentos_associados = $stmt->fetchAll(PDO::FETCH_COLUMN);