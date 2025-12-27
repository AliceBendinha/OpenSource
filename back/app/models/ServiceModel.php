<?php
class ServiceModel extends Model { public function listByPharmacy($farmacia_id){ $stmt=$this->pdo->prepare('SELECT * FROM servicos WHERE farmacia_id=?'); $stmt->execute([$farmacia_id]); return $stmt->fetchAll(); } }
