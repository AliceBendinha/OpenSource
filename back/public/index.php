<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/bootstrap.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$path = substr($uri, strlen($basePath));
$path = trim($path, '/');
$method = $_SERVER['REQUEST_METHOD'];

function input_json(){
    $data = json_decode(file_get_contents('php://input'), true);
    return $data ?: [];
}

if ($path === 'api/login' && $method === 'POST') {
    $data = input_json();
    $c = new AuthController($pdo);
    $c->login($data);
    exit;
}
if ($path === 'api/logout' && $method === 'POST') {
    $c = new AuthController($pdo);
    $c->logout();
    exit;
}
if ($path === 'api/farmacias' && $method === 'GET') {
    $c = new PharmacyController($pdo);
    $c->index();
    exit;
}
if ($path === 'api/farmacias' && $method === 'POST') {
    $data = input_json();
    $c = new PharmacyController($pdo);
    $c->store($data);
    exit;
}
if (preg_match('#^api/farmacias/(\d+)$#', $path, $m) && $method === 'GET') {
    $id = (int)$m[1];
    $c = new PharmacyController($pdo);
    $c->show($id);
    exit;
}
if ($path === 'api/produtos' && $method === 'POST') {
    $data = input_json();
    $c = new ProductController($pdo);
    $c->store($data);
    exit;
}
if ($path === 'api/produtos' && $method === 'GET') {
    $farmacia_id = isset($_GET['farmacia_id']) ? (int)$_GET['farmacia_id'] : null;
    $c = new ProductController($pdo);
    $c->index($farmacia_id);
    exit;
}
if (preg_match('#^api/produtos/(\d+)$#', $path, $m) && in_array($method, ['GET','PUT','DELETE'])) {
    $id = (int)$m[1];
    $c = new ProductController($pdo);
    if ($method === 'GET') $c->show($id);
    if ($method === 'PUT') $c->update($id, input_json());
    if ($method === 'DELETE') $c->delete($id);
    exit;
}
if ($path === 'api/servicos' && $method === 'POST') {
    $data = input_json();
    $c = new ServiceController($pdo);
    $c->store($data);
    exit;
}
if ($path === 'api/search' && $method === 'GET') {
    $q = $_GET['q'] ?? '';
    $c = new SearchController($pdo);
    $c->search($q);
    exit;
}
if ($path === 'api/alerts' && $method === 'GET') {
    $c = new AlertController($pdo);
    $c->index();
    exit;
}
if ($path === 'api/report' && $method === 'POST') {
    $data = input_json();
    $c = new ReportController($pdo);
    $c->generate($data);
    exit;
}
http_response_code(404);
echo json_encode(['error' => 'Endpoint not found']);
