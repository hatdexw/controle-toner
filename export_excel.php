<?php
require 'db.php';

// Fetch data from the database
$stmt = $pdo->query(
    'SELECT h.data_troca, i.codigo, i.modelo as impressora_modelo, i.localizacao, s.modelo as suprimento_modelo, s.tipo
     FROM historico_trocas h
     JOIN impressoras i ON h.impressora_id = i.id
     JOIN suprimentos s ON h.suprimento_id = s.id
     ORDER BY h.data_troca DESC'
);
$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Set headers for Excel download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=historico_trocas.csv');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Output the column headings
fputcsv($output, array('Data', 'Código Imp.', 'Modelo Imp.', 'Localização', 'Suprimento', 'Tipo'));

// Loop through the data and output each row
foreach ($historico as $row) {
    // Format the date for better readability in Excel
    $row['data_troca'] = date('d/m/Y H:i', strtotime($row['data_troca']));
    fputcsv($output, $row);
}

fclose($output);
exit();
?>