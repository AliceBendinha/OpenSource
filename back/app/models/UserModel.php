<?php
class UserModel extends Model { public function findByEmail($email){ $stmt=$this->pdo->prepare('SELECT * FROM usuarios WHERE email=? LIMIT 1'); $stmt->execute([$email]); return $stmt->fetch(); } }
