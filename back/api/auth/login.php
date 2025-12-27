<?php
header("Content-Type: application/json");

require_once "../../config/database.php";
require_once "../../config/jwt.php";
require_once "../../vendor/autoload.php";

use Firebase\JWT\JWT;

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$data['email']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($data['password'], $user['password'])) {
    http_response_code(401);
    echo json_encode(["error" => "Credenciais inválidas"]);
    exit;
}

$payload = [
    "iat" => time(),
    "exp" => time() + 86400,
    "user_id" => $user['id'],
    "role" => $user['role']
];

$token = JWT::encode($payload, SECRET_KEY, JWT_ALG);

echo json_encode([
    "token" => $token,
    "user" => [
        "id" => $user['id'],
        "nome" => $user['nome'],
        "role" => $user['role']
    ]
]);
