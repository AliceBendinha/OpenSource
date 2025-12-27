<?php
class ProductModel extends Model {
    public function listByPharmacy($farmacia_id){
        $stmt=$this->pdo->prepare('SELECT * FROM produtos WHERE farmacia_id=?');
        $stmt->execute([$farmacia_id]);
        return $stmt->fetchAll();
    }
    public function all(){
        $stmt=$this->pdo->query('SELECT p.*, f.nome as farmacia FROM produtos p JOIN farmacias f ON f.id=p.farmacia_id');
        return $stmt->fetchAll();
    }
    public function find($id){
        $stmt=$this->pdo->prepare('SELECT p.*, c.nome as categoria FROM produtos p LEFT JOIN categorias c ON c.id=p.categoria_id WHERE p.id=?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function update($id, $data){
        $stmt = $this->pdo->prepare('UPDATE produtos SET nome=?, preco=?, quantidade=?, categoria_id=?, farmacia_id=? WHERE id=?');
        $stmt->execute([$data['nome'], $data['preco'], $data['quantidade'], $data['categoria_id'], $data['farmacia_id'], $id]);
        return $stmt->rowCount() > 0;
    }
    public function delete($id){
        $stmt = $this->pdo->prepare('DELETE FROM produtos WHERE id=?');
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
}
