<?php
header("Content-Type: application/json");

require_once "../../config/database.php";
require_once "../../middleware/authMiddleware.php";

$user = authMiddleware(['farmacia']);

$stmt = $pdo->prepare("SELECT * FROM produtos WHERE farmacia_id = ?");
$stmt->execute([$user->user_id]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
