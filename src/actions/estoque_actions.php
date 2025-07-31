<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../core/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token();

    if (isset($_POST['add_suprimento'])) {
        if (empty($_POST['modelo']) || empty($_POST['tipo']) || !isset($_POST['quantidade']) || !isset($_POST['quantidade_minima'])) {
            $_SESSION['message'] = ['type' => 'error', 'text' => "Todos os campos sao obrigatorios."];
        } else {
            $modelo = trim($_POST['modelo']);
            $tipo = trim($_POST['tipo']);
            $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);
            $quantidade_minima = filter_input(INPUT_POST, 'quantidade_minima', FILTER_VALIDATE_INT);

            if ($quantidade === false || $quantidade < 0) {
                $_SESSION['message'] = ['type' => 'error', 'text' => "A quantidade deve ser um numero inteiro valido."];
            } elseif ($quantidade_minima === false || $quantidade_minima < 0) {
                $_SESSION['message'] = ['type' => 'error', 'text' => "A quantidade minima deve ser um numero inteiro valido."];
            } else {
                try {
                    $stmt = $pdo->prepare('INSERT INTO suprimentos (modelo, tipo, quantidade, quantidade_minima) VALUES (?, ?, ?, ?)');
                    $stmt->execute([$modelo, $tipo, $quantidade, $quantidade_minima]);
                    $_SESSION['message'] = ['type' => 'success', 'text' => "Suprimento adicionado com sucesso!"];
                } catch (PDOException $e) {
                    $_SESSION['message'] = ['type' => 'error', 'text' => "Erro de banco de dados ao adicionar suprimento."];
                }
            }
        }
    } elseif (isset($_POST['update_suprimento'])) {
        $id = $_POST['id'];
        $quantidade = filter_input(INPUT_POST, 'quantidade', FILTER_VALIDATE_INT);

        if ($quantidade === false || $quantidade < 0) {
            $_SESSION['message'] = ['type' => 'error', 'text' => "A quantidade deve ser um numero inteiro valido."];
        } else {
            try {
                $stmt = $pdo->prepare('UPDATE suprimentos SET quantidade = ? WHERE id = ?');
                $stmt->execute([$quantidade, $id]);
                $_SESSION['message'] = ['type' => 'success', 'text' => "Quantidade do suprimento atualizada com sucesso!"];
            } catch (PDOException $e) {
                $_SESSION['message'] = ['type' => 'error', 'text' => "Erro de banco de dados ao atualizar suprimento."];
            }
        }
    }
    header('Location: estoque');
    exit;
} elseif (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare('DELETE FROM suprimentos WHERE id = ?');
        $stmt->execute([$id]);
        $_SESSION['message'] = ['type' => 'success', 'text' => "Suprimento excluido com sucesso!"];
    } catch (PDOException $e) {
        $_SESSION['message'] = ['type' => 'error', 'text' => "Erro ao excluir suprimento."];
    }
    header('Location: estoque');
    exit;
}