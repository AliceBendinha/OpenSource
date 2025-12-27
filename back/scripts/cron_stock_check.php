<?php
// scripts/cron_stock_check.php
// Run this daily (php scripts/cron_stock_check.php) or via web cron
require __DIR__ . '/../config/bootstrap.php';

class StockChecker {
    private $pdo;
    public function __construct($pdo){ $this->pdo = $pdo; }
    public function run(){
        $stmt = $this->pdo->query('SELECT * FROM produtos WHERE stock <= stock_minimo');
        $alertModel = new AlertModel($this->pdo);
        while ($p = $stmt->fetch()) {
            // create alert if none unresolved exists
            $exists = $alertModel->unresolvedByProduct($p['id']);
            if (!$exists) {
                $alertModel->create($p['id'], 'Stock abaixo do mínimo (CRON)');
                echo "Alerta criado para produto {$p['id']}\n";
            }
        }
    }
}

$checker = new StockChecker($pdo);
$checker->run();
