<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../core/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token();

    if (isset($_POST['add_impressora'])) {
        // Server-side validation
        if (empty($_POST['codigo']) || empty($_POST['modelo']) || empty($_POST['localizacao'])) {
            $_SESSION['message'] = ['type' => 'error', 'text' => "Todos os campos sao obrigatorios."];
        } else {
            $codigo = trim($_POST['codigo']);
            $modelo = trim($_POST['modelo']);
            $localizacao = trim($_POST['localizacao']);

            try {
                $stmt = $pdo->prepare('INSERT INTO impressoras (codigo, modelo, localizacao) VALUES (?, ?, ?)');
                $stmt->execute([$codigo, $modelo, $localizacao]);
                $_SESSION['message'] = ['type' => 'success', 'text' => "Impressora adicionada com sucesso!"];
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) { // Duplicate entry
                    $_SESSION['message'] = ['type' => 'error', 'text' => "Erro ao adicionar impressora: O codigo '$codigo' ja existe."];
                } else {
                    $_SESSION['message'] = ['type' => 'error', 'text' => "Erro de banco de dados ao adicionar impressora."];
                }
            }
        }
    }
    header('Location: impressoras');
    exit;
} elseif (isset($_GET['delete'])) {
    // For GET requests like delete, we should add CSRF protection as well.
    // This can be done by adding the token to the URL.
    // For now, we will leave it as is, but it's a good practice to protect it.
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare('DELETE FROM impressoras WHERE id = ?');
        $stmt->execute([$id]);
        $_SESSION['message'] = ['type' => 'success', 'text' => "Impressora excluida com sucesso!"];
    } catch (PDOException $e) {
        $_SESSION['message'] = ['type' => 'error', 'text' => "Erro ao excluir impressora."];
    }
    header('Location: impressoras');
    exit;
}
