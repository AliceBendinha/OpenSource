<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . "/../config/jwt.php";

function authMiddleware($roles = []) {
    $headers = getallheaders();

    if (!isset($headers['Authorization'])) {
        http_response_code(401);
        echo json_encode(["error" => "Token não fornecido"]);
        exit;
    }

    $token = str_replace("Bearer ", "", $headers['Authorization']);

    try {
        $decoded = JWT::decode($token, new Key(SECRET_KEY, JWT_ALG));

        if (!empty($roles) && !in_array($decoded->role, $roles)) {
            http_response_code(403);
            echo json_encode(["error" => "Acesso negado"]);
            exit;
        }

        return $decoded;

    } catch (Exception $e) {
        http_response_code(401);
        echo json_encode(["error" => "Token inválido"]);
        exit;
    }
}
