<?php
require_once __DIR__ . '/../db/connection.php';

// Lógica para adicionar ou deletar impressora
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_impressora'])) {
        $codigo = $_POST['codigo'];
        $modelo = $_POST['modelo'];
        $localizacao = $_POST['localizacao'];

        try {
            $stmt = $pdo->prepare('INSERT INTO impressoras (codigo, modelo, localizacao) VALUES (?, ?, ?)');
            $stmt->execute([$codigo, $modelo, $localizacao]);
        } catch (PDOException $e) {
            // Tratar erro de codigo duplicado, se necessario
            $error = "Erro ao adicionar impressora. Verifique se o codigo ja existe.";
        }
    }
    
    header('Location: impressoras');
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM impressoras WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: impressoras');
    exit;
}
