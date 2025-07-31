<?php
require_once __DIR__ . '/../db/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_suprimento'])) {
        $modelo = $_POST['modelo'];
        $tipo = $_POST['tipo'];
        $quantidade = $_POST['quantidade'];

        $stmt = $pdo->prepare('INSERT INTO suprimentos (modelo, tipo, quantidade) VALUES (?, ?, ?)');
        $stmt->execute([$modelo, $tipo, $quantidade]);
    } elseif (isset($_POST['update_suprimento'])) {
        $id = $_POST['id'];
        $quantidade = $_POST['quantidade'];

        $stmt = $pdo->prepare('UPDATE suprimentos SET quantidade = ? WHERE id = ?');
        $stmt->execute([$quantidade, $id]);
    }
    header('Location: estoque');
    exit;
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM suprimentos WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: estoque');
    exit;
}
