<?php

$ip = '192.168.88.39';
echo "🖨️ Processando: EPSON L8180 (IP: $ip)\n";
echo "🔍 Modelo Epson L8180 detectado. Iniciando leitura SNMP...\n";

// Mapeamento das descrições para nomes internos
$color_map = [
    'photo black ink bottle' => 'ink_photo_black',
    'black ink bottle'       => 'ink_black',
    'cyan ink bottle'        => 'ink_cyan',
    'magenta ink bottle'     => 'ink_magenta',
    'yellow ink bottle'      => 'ink_yellow',
    'gray ink bottle'        => 'ink_gray',
];

// OIDs padrão SNMP para suprimentos
$oid_descr = '1.3.6.1.2.1.43.11.1.1.6.1'; // descrição do toner
$oid_level = '1.3.6.1.2.1.43.11.1.1.9.1'; // nível atual
$oid_max   = '1.3.6.1.2.1.43.11.1.1.8.1'; // capacidade total

// Lê descrições dos suprimentos
$descriptions = @snmpwalk($ip, 'public', $oid_descr);
$levels       = @snmpwalk($ip, 'public', $oid_level);
$capacities   = @snmpwalk($ip, 'public', $oid_max);

// Debug: Verifica se recebeu algo
if (!$descriptions || !$levels || !$capacities) {
    echo "❌ Erro: Falha ao coletar informações SNMP. Verifique IP ou permissões SNMP.\n";
    exit;
}

echo "🔎 Inspecionando descrições dos suprimentos:\n";

$data = [];
foreach ($descriptions as $i => $desc_raw) {
    preg_match('/STRING: "(.*)"/', $desc_raw, $match);
    $desc = strtolower(trim($match[1] ?? ''));

    if (isset($color_map[$desc])) {
        $key = $color_map[$desc];

        // Extrai os números
        preg_match('/INTEGER: (\d+)/', $levels[$i] ?? '', $level_match);
        preg_match('/INTEGER: (\d+)/', $capacities[$i] ?? '', $max_match);

        $level = isset($level_match[1]) ? (int)$level_match[1] : 0;
        $max   = isset($max_match[1])   ? (int)$max_match[1]   : 100;

        $percent = $max > 0 ? round(($level / $max) * 100) : 0;

        $data[$key] = $percent;
        echo "✅ '{$match[1]}' mapeada como '{$key}' - $percent%\n";
    } else {
        echo "⚠️ Descrição desconhecida: '{$match[1]}' - adicione ao \$color_map se necessário.\n";
    }
}

if (empty($data)) {
    echo "⚠️ Nenhuma cor mapeada encontrada! Copie os nomes acima e atualize o array \$color_map.\n";
    exit;
}

echo "\n📊 Níveis de tinta detectados:\n";
foreach ($data as $cor => $porcentagem) {
    echo "   - $cor: $porcentagem%\n";
}

?>
