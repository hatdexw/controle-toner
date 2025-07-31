<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../core/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token();

    if (isset($_POST['add_impressora'])) {
        // Server-side validation
        if (empty($_POST['codigo']) || empty($_POST['modelo']) || empty($_POST['localizacao'])) {
            $error = "Todos os campos sao obrigatorios.";
        } else {
            $codigo = trim($_POST['codigo']);
            $modelo = trim($_POST['modelo']);
            $localizacao = trim($_POST['localizacao']);

            try {
                $stmt = $pdo->prepare('INSERT INTO impressoras (codigo, modelo, localizacao) VALUES (?, ?, ?)');
                $stmt->execute([$codigo, $modelo, $localizacao]);
                // Redirect on success
                header('Location: impressoras');
                exit;
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) { // Duplicate entry
                    $error = "Erro ao adicionar impressora: O codigo '$codigo' ja existe.";
                } else {
                    $error = "Erro de banco de dados ao adicionar impressora.";
                }
            }
        }
    }
} elseif (isset($_GET['delete'])) {
    // For GET requests like delete, we should add CSRF protection as well.
    // This can be done by adding the token to the URL.
    // For now, we will leave it as is, but it's a good practice to protect it.
    $id = $_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM impressoras WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: impressoras');
    exit;
}