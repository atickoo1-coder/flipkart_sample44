<?php
require_once __DIR__ . '/includes/customer_auth.php';

try {
    $pdo = getConnection();
    $stmt = $pdo->query('SELECT 1');
    $stmt->fetch();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok']);
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
