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
}
