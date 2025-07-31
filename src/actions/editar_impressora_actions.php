<?php
require_once __DIR__ . '/../db/connection.php';

if (!isset($_GET['id'])) {
    header('Location: impressoras');
    exit;
}

$id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo = $_POST['codigo'];
    $modelo = $_POST['modelo'];
    $localizacao = $_POST['localizacao'];

    try {
        $stmt = $pdo->prepare('UPDATE impressoras SET codigo = ?, modelo = ?, localizacao = ? WHERE id = ?');
        $stmt->execute([$codigo, $modelo, $localizacao, $id]);
        header('Location: impressoras');
        exit;
    } catch (PDOException $e) {
        $error = "Erro ao atualizar impressora. Verifique se o codigo ja existe.";
    }
}
