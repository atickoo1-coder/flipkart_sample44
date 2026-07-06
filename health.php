<?php
require_once __DIR__ . '/config/database.php';

header('Content-Type: application/json');

try {
    $pdo = getConnection();
    $pdo->query('SELECT 1')->fetch();
    http_response_code(200);
    echo json_encode(['status' => 'ok', 'database' => 'connected']);
} catch (Throwable $e) {
    http_response_code(200);
    echo json_encode([
        'status' => 'degraded',
        'database' => 'disconnected',
        'message' => 'Database connection not available yet'
    ]);
}
