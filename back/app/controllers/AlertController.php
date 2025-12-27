<?php
class AlertController { private $model; public function __construct($pdo){ $this->model=new AlertModel($pdo);} public function index(){ echo json_encode(['data'=>$this->model->listPending()]); } }
