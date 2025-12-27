<?php
class ProductController {
    private $model;
    public function __construct($pdo){
        $this->model=new ProductModel($pdo);
    }
    public function index($farmacia_id=null){
        if($farmacia_id) $data=$this->model->listByPharmacy($farmacia_id);
        else $data=$this->model->all();
        echo json_encode(['data'=>$data]);
    }
    public function show($id){
        $p=$this->model->find($id);
        if(!$p){
            http_response_code(404);
            echo json_encode(['error'=>'Produto não encontrado']);
            return;
        }
        echo json_encode(['data'=>$p]);
    }
    public function store($data){
        if(empty($_SESSION['user'])){
            http_response_code(401);
            echo json_encode(['error'=>'Login necessário']);
            return;
        }
        echo json_encode(['success'=>true]);
    }
    public function update($id, $data){
        if(empty($_SESSION['user'])){
            http_response_code(401);
            echo json_encode(['error'=>'Login necessário']);
            return;
        }
        $success = $this->model->update($id, $data);
        if($success){
            echo json_encode(['success'=>true]);
        } else {
            http_response_code(404);
            echo json_encode(['error'=>'Produto não encontrado']);
        }
    }
    public function delete($id){
        if(empty($_SESSION['user'])){
            http_response_code(401);
            echo json_encode(['error'=>'Login necessário']);
            return;
        }
        $success = $this->model->delete($id);
        if($success){
            echo json_encode(['success'=>true]);
        } else {
            http_response_code(404);
            echo json_encode(['error'=>'Produto não encontrado']);
        }
    }
}
