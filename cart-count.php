<?php
require_once __DIR__ . '/includes/customer_auth.php';

header('Content-Type: application/json');

$count = 0;
if (isCustomerLoggedIn()) {
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT SUM(quantity) FROM cart WHERE customer_id = ?");
        $stmt->execute([$_SESSION['customer_id']]);
        $res = $stmt->fetchColumn();
        $count = $res !== null ? (int)$res : 0;
    } catch (PDOException $e) {
        $count = 0;
    }
}

echo json_encode(['count' => $count]);
