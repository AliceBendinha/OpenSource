<?php
spl_autoload_register(function($class){
    $paths = [__DIR__ . '/../app/controllers/', __DIR__ . '/../app/models/'];
    foreach ($paths as $p) {
        $file = $p . $class . '.php';
        if (file_exists($file)) { require_once $file; return; }
    }
});
$dbcfg = require __DIR__ . '/db.php';
$dsn = "mysql:host={$dbcfg['host']};dbname={$dbcfg['dbname']};charset={$dbcfg['charset']}";
try {
    $pdo = new PDO($dsn, $dbcfg['user'], $dbcfg['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}
