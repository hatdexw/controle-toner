<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../core/csrf.php';

if (!isset($_GET['id'])) {
    header('Location: impressoras');
    exit;
}

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token();

    if (empty($_POST['codigo']) || empty($_POST['modelo']) || empty($_POST['localizacao'])) {
        $error = "Todos os campos sao obrigatorios.";
    } else {
        $codigo = trim($_POST['codigo']);
        $modelo = trim($_POST['modelo']);
        $localizacao = trim($_POST['localizacao']);

        try {
            $stmt = $pdo->prepare('UPDATE impressoras SET codigo = ?, modelo = ?, localizacao = ? WHERE id = ?');
            $stmt->execute([$codigo, $modelo, $localizacao, $id]);
            header('Location: impressoras');
            exit;
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) { // Duplicate entry
                $error = "Erro ao atualizar impressora: O codigo '$codigo' ja existe.";
            } else {
                $error = "Erro de banco de dados ao atualizar impressora.";
            }
        }
    }
}