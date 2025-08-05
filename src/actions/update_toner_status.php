<?php
require_once __DIR__ . '/../db/connection.php';

if (!extension_loaded('snmp')) {
    die("❌ Erro: A extensão SNMP do PHP não está habilitada. Ative no php.ini\n");
}

echo "🔄 Iniciando a atualização do status do toner das impressoras...\n";

$community_string = 'public';

try {
    $stmt = $pdo->query("SELECT id, modelo, ip_address FROM impressoras WHERE ip_address IS NOT NULL");
    $impressoras = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($impressoras)) {
        echo "⚠️ Nenhuma impressora encontrada com endereço IP.\n";
        exit;
    }

    foreach ($impressoras as $impressora) {
        $id = $impressora['id'];
        $modelo = strtoupper($impressora['modelo']);
        $ip = $impressora['ip_address'];

        echo "\n🖨️ Processando: $modelo (IP: $ip)\n";

        if (strpos($modelo, 'L8180') !== false) {
            try {
                // Mapeamento das descrições para nomes internos (copiado do teste.php)
                $color_map = [
                    'photo black ink bottle' => 'ink_photo_black',
                    'black ink bottle'       => 'ink_black',
                    'cyan ink bottle'        => 'ink_cyan',
                    'magenta ink bottle'     => 'ink_magenta',
                    'yellow ink bottle'      => 'ink_yellow',
                    'gray ink bottle'        => 'ink_gray',
                ];

                // OIDs padrão SNMP para suprimentos (copiado do teste.php)
                $oid_descr = '1.3.6.1.2.1.43.11.1.1.6.1'; // descrição do toner
                $oid_level = '1.3.6.1.2.1.43.11.1.1.9.1'; // nível atual
                $oid_max   = '1.3.6.1.2.1.43.11.1.1.8.1'; // capacidade total

                // Lê descrições dos suprimentos (copiado do teste.php)
                $descriptions = @snmpwalk($ip, $community_string, $oid_descr);
                $levels       = @snmpwalk($ip, $community_string, $oid_level);
                $capacities   = @snmpwalk($ip, $community_string, $oid_max);

                // Debug: Verifica se recebeu algo
                if (!$descriptions || !$levels || !$capacities) {
                    echo "❌ Erro: Falha ao coletar informações SNMP para Epson. Verifique IP ou permissões SNMP.\n";
                    continue;
                }

                $ink_levels = [];
                foreach ($descriptions as $i => $desc_raw) {
                    preg_match('/STRING: \"(.*)\"/i', $desc_raw, $match);
                    $desc = strtolower(trim($match[1] ?? ''));

                    if (isset($color_map[$desc])) {
                        $key = $color_map[$desc];

                        // Extrai os números
                        preg_match('/INTEGER: (\d+)/', $levels[$i] ?? '', $level_match);
                        preg_match('/INTEGER: (\d+)/', $capacities[$i] ?? '', $max_match);

                        $level = isset($level_match[1]) ? (int)$level_match[1] : 0;
                        $max   = isset($max_match[1])   ? (int)$max_match[1]   : 100;

                        $percent = $max > 0 ? round(($level / $max) * 100) : 0;

                        $ink_levels[$key] = $percent;
                    }
                }

                if (!empty($ink_levels)) {
                    $cols = [];
                    $vals = [];
                    foreach ($ink_levels as $col => $val) {
                        $cols[] = "$col = ?";
                        $vals[] = $val;
                    }
                    $vals[] = $id;

                    $sql = "UPDATE impressoras SET " . implode(', ', $cols) . " WHERE id = ?";
                    $pdo->prepare($sql)->execute($vals);
                    echo "💾 Níveis de tinta da Epson atualizados no banco de dados.\n";
                } else {
                    echo "⚠️ Nenhum nível de tinta foi coletado para a Epson.\n";
                }

            } catch (Exception $e) {
                echo "❌ Exceção SNMP para a impressora Epson $ip: " . $e->getMessage() . "\n";
            }

        } elseif (strpos($modelo, 'BROTHER') !== false) {
            try {
                $oid = '1.3.6.1.4.1.2435.2.3.9.4.2.1.5.5.8.0';
                $result = @snmpget($ip, $community_string, $oid);

                if ($result !== false) {
                    $hex = preg_replace('/^.*?:\\s*/', '', $result);
                    $hex = str_replace(' ', '', $hex);
                    $identifier = '81';

                    $pos = strpos($hex, $identifier);
                    if ($pos !== false) {
                        $toner_hex = substr($hex, $pos + 12, 2);
                        $toner = hexdec($toner_hex);

                        $pdo->prepare("UPDATE impressoras SET toner_status = ? WHERE id = ?")
                            ->execute([$toner, $id]);

                        echo "✅ Toner Brother atualizado: $toner%\n";
                    } else {
                        echo "⚠️ Não foi possível localizar o identificador do toner na string.\n";
                    }
                } else {
                    echo "❌ SNMP falhou para Brother $ip.\n";
                }
            } catch (Exception $e) {
                 echo "❌ Exceção SNMP para a impressora Brother $ip: " . $e->getMessage() . "\n";
            }

        } else {
            try {
                $oid = '1.3.6.1.2.1.43.11.1.1.9.1.1';
                $result = @snmpget($ip, $community_string, $oid);

                if ($result !== false && preg_match('/\d+/', $result, $match)) {
                    $toner = (int)$match[0];
                    $pdo->prepare("UPDATE impressoras SET toner_status = ? WHERE id = ?")
                        ->execute([$toner, $id]);

                    echo "✅ Toner atualizado: $toner%\n";
                } else {
                    echo "⚠️ Não foi possível obter o nível de toner para modelo genérico.\n";
                }
            } catch (Exception $e) {
                echo "❌ Exceção SNMP para a impressora $ip: " . $e->getMessage() . "\n";
            }
        }
    }

    echo "\n✅ Atualização finalizada.\n";

} catch (PDOException $e) {
    die("❌ Erro no banco de dados: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("❌ Erro inesperado: " . $e->getMessage() . "\n");
}
