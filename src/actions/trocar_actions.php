<?php
require_once __DIR__ . '/../db/connection.php';
require_once __DIR__ . '/../core/csrf.php';

if (!isset($_GET['impressora_id'])) {
    header('Location: index');
    exit;
}

$impressora_id = $_GET['impressora_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token();

    if (empty($_POST['suprimento_id'])) {
        $error = "Por favor, selecione um suprimento.";
    } else {
        $suprimento_id = $_POST['suprimento_id'];
        $data_troca = date('Y-m-d H:i:s'); // Data e hora atuais

        $pdo->beginTransaction();

        try {
            // 1. Registrar a troca no historico_trocas
            $stmt = $pdo->prepare('INSERT INTO historico_trocas (impressora_id, suprimento_id, data_troca) VALUES (?, ?, ?)');
            $stmt->execute([$impressora_id, $suprimento_id, $data_troca]);

            // 2. Decrementar a quantidade do suprimento no estoque
            $stmt = $pdo->prepare('UPDATE suprimentos SET quantidade = quantidade - 1 WHERE id = ? AND quantidade > 0');
            $stmt->execute([$suprimento_id]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Erro: Suprimento insuficiente ou nao encontrado.');
            }

            $pdo->commit();
            header('Location: index');
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}