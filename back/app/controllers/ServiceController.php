<?php
class ServiceController { private $model; public function __construct($pdo){ $this->model=new ServiceModel($pdo);} public function store($data){ if(empty($_SESSION['user'])){ http_response_code(401); echo json_encode(['error'=>'Login necessário']); return; } echo json_encode(['success'=>true]); } }
