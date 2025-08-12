<?php
require_once __DIR__ . '/../../src/db/connection.php';
require_once __DIR__ . '/../../vendor/autoload.php';

// Funcao para remover acentos e caracteres especiais
function remove_accents($string) {
    $unwanted_array = array(
        'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
        'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
        'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
        'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y'
    );
    return strtr($string, $unwanted_array);
}

class PDF extends FPDF
{
    // Cabecalho da Pagina
    function Header()
    {
        // Logo - Exemplo: Voce pode adicionar um logo se tiver um
        // $this->Image('logo.png',10,6,30);
        
        // Fonte e Cores
        $this->SetFont('Arial','B',20);
        $this->SetTextColor(34, 61, 109); // Azul Escuro
        
        // Titulo
        $this->Cell(0,15,'Relatorio de Historico de Trocas',0,1,'C');
        
        // Subtitulo com data
        $this->SetFont('Arial','',10);
        $this->SetTextColor(100, 100, 100); // Cinza
        $this->Cell(0,7,'Gerado em: ' . date('d/m/Y H:i:s'),0,1,'C');
        
        // Linha abaixo do cabecalho
        $this->SetDrawColor(34, 61, 109); // Azul Escuro
        $this->SetLineWidth(0.5);
        $this->Line(10, 35, 200, 35);
        
        // Espacamento
        $this->Ln(15);
    }

    // Rodape da Pagina
    function Footer()
    {
        $this->SetY(-15); // Posicao a 1.5 cm do final
        
        // Linha acima do rodape
        $this->SetDrawColor(34, 61, 109); // Azul Escuro
        $this->SetLineWidth(0.2);
        $this->Line(10, $this->GetY(), 200, $this->GetY());
        
        // Fonte e texto
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(150, 150, 150); // Cinza claro
        $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }

    // Tabela de dados aprimorada
    function FancyTable($header, $data)
    {
        // Cores, largura da linha e fonte em negrito
        $this->SetFillColor(52, 91, 164); // Azul para o cabecalho
        $this->SetTextColor(255); // Texto branco
        $this->SetDrawColor(34, 61, 109); // Bordas em azul escuro
        $this->SetLineWidth(.3);
        $this->SetFont('','B', 9);

        // Larguras das colunas (total 190mm para A4 com margens de 10mm)
        $w = array(28, 22, 50, 25, 50, 15);
        
        // Cabecalho
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],10,$header[$i],1,0,'C',true);
        $this->Ln();

        // Restauracao de cores e fontes
        $this->SetFillColor(240, 245, 255); // Fundo azul bem claro para linhas
        $this->SetTextColor(0); // Texto preto
        $this->SetFont('','', 7.5); // Reduzido o tamanho da fonte

        // Dados
        $fill = false;
        foreach($data as $row)
        {
            $this->Cell($w[0],8,date('d/m/Y H:i', strtotime($row['data_troca'])),'LR',0,'C',$fill);
            $this->Cell($w[1],8,$row['codigo'],'LR',0,'C',$fill);
            $this->Cell($w[2],8,$row['impressora_modelo'],'LR',0,'C',$fill);
            $this->Cell($w[3],8,$row['localizacao'],'LR',0,'C',$fill);
            $this->Cell($w[4],8,$row['suprimento_modelo'],'LR',0,'C',$fill);
            $this->Cell($w[5],8,$row['tipo'],'LR',0,'C',$fill);
            $this->Ln();
            $fill = !$fill;
        }
        // Linha de fechamento
        $this->Cell(array_sum($w),0,'','T');
    }
}

// Buscar dados do banco de dados
$stmt = $pdo->query(
    'SELECT h.data_troca, i.codigo, i.modelo as impressora_modelo, i.localizacao, s.modelo as suprimento_modelo, s.tipo
     FROM historico_trocas h
     JOIN impressoras i ON h.impressora_id = i.id
     JOIN suprimentos s ON h.suprimento_id = s.id
     ORDER BY h.data_troca DESC'
);
$historico_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Remover acentos dos dados
$historico_clean = array();
foreach ($historico_raw as $row) {
    $clean_row = $row;
    $clean_row['impressora_modelo'] = remove_accents($row['impressora_modelo']);
    $clean_row['localizacao'] = remove_accents($row['localizacao']);
    $clean_row['suprimento_modelo'] = remove_accents($row['suprimento_modelo']);
    $clean_row['tipo'] = remove_accents($row['tipo']);
    $historico_clean[] = $clean_row;
}


$pdf = new PDF('P', 'mm', 'A4');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',12);

// Titulos das colunas (sem acentos)
$header = array('Data', 'Cod. Imp.', 'Mod. Imp.', 'Localizacao', 'Suprimento', 'Tipo');

$pdf->FancyTable($header, $historico_clean);

$pdf->Output('I', 'historico_trocas.pdf');
exit();
?>