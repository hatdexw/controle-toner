<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../core/csrf.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Falha na verificação de segurança.'];
        header('Location: /controle-toner/estoque');
        exit;
    }

    // Ação: Adicionar Suprimento
    if (isset($_POST['add_suprimento'])) {
        try {
            $stmt = $pdo->prepare('INSERT INTO suprimentos (modelo, tipo, quantidade) VALUES (?, ?, ?)');
            $stmt->execute([$_POST['modelo'], $_POST['tipo'], $_POST['quantidade']]);
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Suprimento adicionado com sucesso!'];
        } catch (PDOException $e) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Erro ao adicionar suprimento.'];
        }
    }

    // Ação: Atualizar Quantidade
    elseif (isset($_POST['update_suprimento'])) {
        try {
            $stmt = $pdo->prepare('UPDATE suprimentos SET quantidade = ? WHERE id = ?');
            $stmt->execute([$_POST['quantidade'], $_POST['id']]);
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Quantidade atualizada com sucesso!'];
        } catch (PDOException $e) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Erro ao atualizar quantidade.'];
        }
    }

    // Ação: Excluir Suprimento
    elseif (isset($_POST['delete_suprimento'])) {
        try {
            $stmt = $pdo->prepare('DELETE FROM suprimentos WHERE id = ?');
            $stmt->execute([$_POST['id']]);
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Suprimento excluído com sucesso!'];
        } catch (PDOException $e) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Erro ao excluir suprimento.'];
        }
    }

    header('Location: /controle-toner/estoque');
    exit;
}