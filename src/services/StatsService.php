<?php
namespace App\Services;
use App\Core\Cache;
class StatsService {
  private \PDO $pdo; 
  public function __construct(\PDO $pdo){ $this->pdo=$pdo; }
  public function summary(): array {
    return Cache::remember('stats_summary',30,function(){
        $totalPrinters=(int)$this->pdo->query('SELECT COUNT(*) FROM impressoras')->fetchColumn();
        $lowToner=(int)$this->pdo->query('SELECT COUNT(*) FROM impressoras WHERE toner_status IS NOT NULL AND toner_status <= 15')->fetchColumn();
        $emptyToner=(int)$this->pdo->query('SELECT COUNT(*) FROM impressoras WHERE toner_status = 0')->fetchColumn();
        $totalSupplies=(int)$this->pdo->query('SELECT COUNT(*) FROM suprimentos')->fetchColumn();
        $lowStockSupplies=(int)$this->pdo->query('SELECT COUNT(*) FROM suprimentos WHERE quantidade <= 2')->fetchColumn();
        $lastExchanges=$this->pdo->query('SELECT h.data_troca,i.codigo,s.modelo FROM historico_trocas h JOIN impressoras i ON i.id=h.impressora_id JOIN suprimentos s ON s.id=h.suprimento_id ORDER BY h.data_troca DESC LIMIT 5')->fetchAll(\PDO::FETCH_ASSOC);
        return compact('totalPrinters','lowToner','emptyToner','totalSupplies','lowStockSupplies','lastExchanges');
    });
  }

  // Buckets de toner: vazio, baixo, medio, alto
  public function tonerBuckets(): array {
    return Cache::remember('stats_toner_buckets',30,function(){
      $sql = "SELECT 
          SUM(CASE WHEN toner_status = 0 THEN 1 ELSE 0 END) AS vazio,
          SUM(CASE WHEN toner_status BETWEEN 1 AND 15 THEN 1 ELSE 0 END) AS baixo,
          SUM(CASE WHEN toner_status BETWEEN 16 AND 50 THEN 1 ELSE 0 END) AS medio,
          SUM(CASE WHEN toner_status BETWEEN 51 AND 100 THEN 1 ELSE 0 END) AS alto
        FROM impressoras";
      return $this->pdo->query($sql)->fetch(\PDO::FETCH_ASSOC) ?: ['vazio'=>0,'baixo'=>0,'medio'=>0,'alto'=>0];
    });
  }

  // Suprimentos agrupados por tipo
  public function suppliesByType(): array {
    return Cache::remember('stats_supplies_by_type',30,function(){
      return $this->pdo->query('SELECT tipo, SUM(quantidade) AS total FROM suprimentos GROUP BY tipo ORDER BY tipo')->fetchAll(\PDO::FETCH_ASSOC);
    });
  }

  // Resumo de status das impressoras
  public function printerStatusSummary(): array {
    return Cache::remember('stats_printer_status',30,function(){
      $sql = "SELECT 
          SUM(CASE WHEN toner_status IS NULL THEN 1 ELSE 0 END) AS sem_dado,
          SUM(CASE WHEN toner_status = 0 THEN 1 ELSE 0 END) AS vazio,
          SUM(CASE WHEN toner_status BETWEEN 1 AND 15 THEN 1 ELSE 0 END) AS baixo,
          SUM(CASE WHEN toner_status > 15 THEN 1 ELSE 0 END) AS ok
        FROM impressoras";
      return $this->pdo->query($sql)->fetch(\PDO::FETCH_ASSOC) ?: ['sem_dado'=>0,'vazio'=>0,'baixo'=>0,'ok'=>0];
    });
  }

  // Série de trocas dos últimos N dias (default 14)
  public function exchangesSeries(int $days = 14): array {
    $key = 'stats_exchanges_series_'.$days;
    return Cache::remember($key,30,function() use ($days){
      $stmt = $this->pdo->prepare('SELECT DATE(data_troca) d, COUNT(*) c FROM historico_trocas WHERE data_troca >= DATE_SUB(CURDATE(), INTERVAL :delta DAY) GROUP BY DATE(data_troca) ORDER BY d ASC');
      // :delta must be integer literal in many MySQL versions; fallback using bound value
      $delta = max(0, $days - 1);
      $stmt->bindValue(':delta', $delta, \PDO::PARAM_INT);
      $stmt->execute();
      $pairs = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR) ?: [];
      $labels = [];$values=[];
      for($i=$days-1;$i>=0;$i--){
        $day = date('Y-m-d', strtotime("-$i day"));
        $labels[] = date('d/m', strtotime($day));
        $values[] = (int)($pairs[$day] ?? 0);
      }
      return ['labels'=>$labels,'values'=>$values];
    });
  }

  // Top N impressoras com toner mais baixo
  public function topLowTonerPrinters(int $limit = 5): array {
    $limit = max(1, min(20, $limit));
    $stmt = $this->pdo->prepare('SELECT id, codigo, modelo, COALESCE(toner_status,0) AS toner_status FROM impressoras WHERE toner_status IS NOT NULL ORDER BY toner_status ASC, codigo ASC LIMIT :lim');
    $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
  }

  // Suprimentos críticos (quantidade <= threshold)
  public function criticalSupplies(int $limit = 5, int $threshold = 2): array {
    $limit = max(1, min(50, $limit));
    $stmt = $this->pdo->prepare('SELECT id, modelo, tipo, quantidade FROM suprimentos WHERE quantidade <= :thr ORDER BY quantidade ASC, modelo ASC LIMIT :lim');
    $stmt->bindValue(':thr', $threshold, \PDO::PARAM_INT);
    $stmt->bindValue(':lim', $limit, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
  }

  // Pacote completo para o dashboard
  public function dashboardData(?string $period = null): array {
    $summary    = $this->summary();
    $buckets    = $this->tonerBuckets();
    $supByType  = $this->suppliesByType();
    $status     = $this->printerStatusSummary();
    $series     = $period ? $this->exchangesByMonth($period) : $this->exchangesSeries(14);
    $lowPrinters= $this->topLowTonerPrinters(5);
    $critical   = $this->criticalSupplies(5,2);
    $insights   = $this->basicInsights($summary,$status);
    return [
      'summary'=>$summary,
      'tonerBuckets'=>$buckets,
      'suppliesByType'=>$supByType,
      'status'=>$status,
      'exchanges'=>$series,
      'lowPrinters'=>$lowPrinters,
      'criticalSupplies'=>$critical,
      'insights'=>$insights,
      'period'=>$period,
      'generatedAt'=>date('c')
    ];
  }

  // Série diária para um mês específico no formato YYYY-MM
  public function exchangesByMonth(string $period): array {
    // validate period
    if(!preg_match('/^\d{4}-\d{2}$/',$period)) return $this->exchangesSeries(14);
    [$year,$month] = array_map('intval', explode('-', $period));
    if($month<1||$month>12) return $this->exchangesSeries(14);
    $first = sprintf('%04d-%02d-01',$year,$month);
    $last  = date('Y-m-t', strtotime($first));
    $stmt = $this->pdo->prepare('SELECT DATE(data_troca) d, COUNT(*) c FROM historico_trocas WHERE DATE(data_troca) BETWEEN :ini AND :fim GROUP BY DATE(data_troca) ORDER BY d ASC');
    $stmt->bindValue(':ini',$first); $stmt->bindValue(':fim',$last);
    $stmt->execute();
    $pairs = $stmt->fetchAll(\PDO::FETCH_KEY_PAIR) ?: [];
    $labels=[];$values=[]; $days=(int)date('t', strtotime($first));
    for($i=1;$i<=$days;$i++){
      $d = sprintf('%04d-%02d-%02d',$year,$month,$i);
      $labels[] = sprintf('%02d/%02d',$i,$month);
      $values[] = (int)($pairs[$d] ?? 0);
    }
    return ['labels'=>$labels,'values'=>$values];
  }

  private function basicInsights(array $summary, array $status): array {
    $total = max(1,(int)$summary['totalPrinters']);
    $pctLow = round(((int)$summary['lowToner'] / $total) * 100);
    $pctEmpty = round(((int)$summary['emptyToner'] / $total) * 100);
    $msg1 = $pctLow>0 ? "$pctLow% das impressoras estão com toner baixo." : 'Nenhuma impressora com toner baixo.';
    $msg2 = $pctEmpty>0 ? "$pctEmpty% sem toner (vazias)." : 'Nenhuma impressora vazia.';
    return [$msg1,$msg2];
  }
}
