<?php
require 'db.php';
require('fpdf/fpdf.php'); // Adjust path if FPDF is in a different location

class PDF extends FPDF
{
    // Page header
    function Header()
    {
        // Titulo principal
        $this->SetFont('Arial','B',18);
        $this->SetFillColor(235, 235, 235); // Cinza claro para o fundo do titulo
        $this->SetTextColor(50, 50, 50); // Cinza escuro para o texto do titulo
        $this->Cell(0,15,'Relatorio de Historico de Trocas',0,1,'C', true);
        
        // Subtitulo com data de geracao
        $this->SetFont('Arial','',10);
        $this->SetTextColor(120, 120, 120); // Cinza medio para o subtitulo
        $this->Cell(0,7,'Gerado em: ' . date('d/m/Y H:i:s'),0,1,'C');
        $this->Ln(10);
        
        // Resetar cores para o corpo do documento
        $this->SetTextColor(0, 0, 0);
    }

    // Page footer
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->SetTextColor(150, 150, 150); // Cinza claro para o rodape
        $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }

    // Tabela melhorada
    function ImprovedTable($header, $data)
    {
        // Cores para o cabecalho da tabela
        $this->SetFillColor(220, 220, 220); // Cinza um pouco mais escuro
        $this->SetTextColor(0, 0, 0); // Preto para o texto
        $this->SetDrawColor(200, 200, 200); // Bordas mais suaves
        $this->SetLineWidth(.2);
        $this->SetFont('Arial','B',9); // Fonte um pouco menor e negrito

        // Larguras das colunas (ajustadas para melhor visualizacao)
        $w = array(25, 25, 35, 35, 35, 20);
        
        // Definir a posicao X para centralizar o cabecalho da tabela
        $this->SetX(17.5);
        
        // Cabecalho da tabela
        for($i=0;$i<count($header);$i++)
            $this->Cell($w[$i],8,$header[$i],1,0,'C',true); // Aumentei a altura da celula
        $this->Ln();

        // Restaurar cores e fontes para os dados da tabela
        $this->SetFillColor(248, 248, 248); // Fundo para linhas pares (quase branco)
        $this->SetTextColor(60, 60, 60); // Cinza escuro para o texto dos dados
        $this->SetFont('Arial','',8); // Fonte menor para os dados

        // Dados da tabela
        $fill = false;
        foreach($data as $row)
        {
            // Definir a posicao X para centralizar cada linha de dados
            $this->SetX(17.5);
            
            // Alterado o alinhamento de 'L' para 'C' para centralizar o texto
            $this->Cell($w[0],7,date('d/m/Y H:i', strtotime($row['data_troca'])),'LR',0,'C',$fill);
            $this->Cell($w[1],7,$row['codigo'],'LR',0,'C',$fill);
            $this->Cell($w[2],7,$row['impressora_modelo'],'LR',0,'C',$fill);
            $this->Cell($w[3],7,$row['localizacao'],'LR',0,'C',$fill);
            $this->Cell($w[4],7,$row['suprimento_modelo'],'LR',0,'C',$fill);
            $this->Cell($w[5],7,$row['tipo'],'LR',0,'C',$fill);
            $this->Ln();
            $fill = !$fill; // Alternar cor de fundo para linhas zebradas
        }
        // Linha de fechamento da tabela
        $this->SetX(17.5); // Adicionado para centralizar a linha de fechamento
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
$historico = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Titulos das colunas da tabela
$header = array('Data', 'Cod. Imp.', 'Mod. Imp.', 'Localizacao', 'Suprimento', 'Tipo');

$pdf->ImprovedTable($header, $historico);
$pdf->Output('I', 'historico_trocas.pdf');
exit();
?>