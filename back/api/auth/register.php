<?php
header("Content-Type: application/json");

require_once "../../config/database.php";
require_once "../../middleware/authMiddleware.php";

$userAuth = authMiddleware(['admin','super_admin']);

$data = json_decode(file_get_contents("php://input"), true);

$passwordHash = password_hash($data['password'], PASSWORD_BCRYPT);

$stmt = $pdo->prepare("
    INSERT INTO users (nome, email, password, role, farmacia_id)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->execute([
    $data['nome'],
    $data['email'],
    $passwordHash,
    $data['role'],
    $data['farmacia_id'] ?? null
]);

echo json_encode(["success" => true]);
