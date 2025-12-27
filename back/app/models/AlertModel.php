<?php
class AlertModel extends Model {
    public function listPending(){
        $stmt=$this->pdo->query('SELECT a.*, p.nome as produto, f.nome as farmacia FROM alertas_stock a JOIN produtos p ON p.id=a.produto_id JOIN farmacias f ON f.id=p.farmacia_id WHERE a.status = "pendente" ORDER BY a.data DESC');
        return $stmt->fetchAll();
    }

    public function unresolvedByProduct($produto_id){
        $stmt = $this->pdo->prepare('SELECT COUNT(*) as count FROM alertas_stock WHERE produto_id = ? AND status = "pendente"');
        $stmt->execute([$produto_id]);
        $result = $stmt->fetch();
        return $result['count'] > 0;
    }

    public function create($produto_id, $mensagem){
        $stmt = $this->pdo->prepare('INSERT INTO alertas_stock (produto_id, mensagem, status) VALUES (?, ?, "pendente")');
        $stmt->execute([$produto_id, $mensagem]);
        return $this->pdo->lastInsertId();
    }
}
