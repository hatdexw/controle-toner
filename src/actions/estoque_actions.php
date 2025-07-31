<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../core/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token();

    if (isset($_POST['add_suprimento'])) {
        if (empty($_POST['modelo']) || empty($_POST['tipo']) || !isset($_POST['quantidade'])) {
            $error = "Todos os campos sao obrigatorios.";
        } else {
            $modelo = trim($_POST['modelo']);
            $tipo = trim($_POST['tipo']);
            $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);

            if ($quantidade === false || $quantidade < 0) {
                $error = "A quantidade deve ser um numero inteiro valido.";
            } else {
                try {
                    $stmt = $pdo->prepare('INSERT INTO suprimentos (modelo, tipo, quantidade) VALUES (?, ?, ?)');
                    $stmt->execute([$modelo, $tipo, $quantidade]);
                    header('Location: estoque');
                    exit;
                } catch (PDOException $e) {
                    $error = "Erro de banco de dados ao adicionar suprimento.";
                }
            }
        }
    } elseif (isset($_POST['update_suprimento'])) {
        $id = $_POST['id'];
        $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);

        if ($quantidade === false || $quantidade < 0) {
            $error = "A quantidade deve ser um numero inteiro valido.";
        } else {
            $stmt = $pdo->prepare('UPDATE suprimentos SET quantidade = ? WHERE id = ?');
            $stmt->execute([$quantidade, $id]);
            header('Location: estoque');
            exit;
        }
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM suprimentos WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: estoque');
    exit;
}