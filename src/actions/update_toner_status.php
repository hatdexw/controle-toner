<?php
require_once __DIR__ . '/../db/connection.php';

// Verifica se a extensão SNMP está habilitada
if (!extension_loaded('snmp')) {
    die("Erro: A extensão SNMP do PHP não está habilitada. Por favor, habilite-a no seu php.ini.\n");
}

echo "Iniciando a atualização do status do toner das impressoras...\n";

// OID para o nível do toner (exemplo: 1.3.6.1.2.1.43.11.1.1.9.1.1)
$oid_toner_level = '1.3.6.1.2.1.43.11.1.1.9.1.1';
$community_string = 'public'; // Community string padrão

try {
    // 1. Obter todas as impressoras do banco de dados
    $stmt = $pdo->query('SELECT id, modelo, ip_address FROM impressoras WHERE ip_address IS NOT NULL');
    $impressoras = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($impressoras)) {
        echo "Nenhuma impressora encontrada com endereço IP.\n";
        exit;
    }

    foreach ($impressoras as $impressora) {
        $impressora_id = $impressora['id'];
        $modelo = $impressora['modelo'];
        $ip_address = $impressora['ip_address'];

        echo "\nProcessando impressora: {$modelo} (IP: {$ip_address})...\n";

        // Tenta obter o status do toner via SNMP
        $toner_level = null;
        try {
            // snmpget(hostname, community, oid, timeout, retries)
            $result = snmpget($ip_address, $community_string, $oid_toner_level, 1000000, 1); // Timeout em microssegundos (1 segundo)

            if ($result !== false) {
                // O resultado pode vir como 'INTEGER: 50' ou 'STRING: 50'
                // Extrai apenas o valor numérico
                if (preg_match('/(\d+)/', $result, $matches)) {
                    $toner_level = (int)$matches[1];
                    echo "Status do Toner obtido: {$toner_level}%\n";
                } else {
                    echo "Não foi possível extrair o nível numérico do toner de: {$result}\n";
                }
            } else {
                echo "Erro ao obter status do Toner via SNMP para {$ip_address}. Verifique o IP, community string ou se a impressora está online.\n";
            }
        } catch (Exception $e) {
            echo "Exceção SNMP para {$ip_address}: " . $e->getMessage() . "\n";
        }

        // 2. Atualizar o status do toner no banco de dados
        if ($toner_level !== null) {
            $update_stmt = $pdo->prepare('UPDATE impressoras SET toner_status = ? WHERE id = ?');
            $update_stmt->execute([$toner_level, $impressora_id]);
            echo "Status do Toner atualizado no banco de dados para {$toner_level}% para a impressora ID {$impressora_id}.\n";
        } else {
            echo "Status do Toner não atualizado para a impressora ID {$impressora_id} devido a falha na leitura SNMP.\n";
        }
    }

    echo "\nAtualização do status do toner concluída.\n";

} catch (PDOException $e) {
    die("Erro de banco de dados: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("Erro inesperado: " . $e->getMessage() . "\n");
}

?>