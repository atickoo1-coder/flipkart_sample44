<?php
require_once __DIR__ . '/includes/customer_auth.php';

header('Content-Type: application/json');

if (!isCustomerLoggedIn()) {
    echo json_encode(['count' => 0]);
    exit();
}

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE customer_id = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    $count = (int)$stmt->fetchColumn();
    echo json_encode(['count' => $count]);
} catch (PDOException $e) {
    echo json_encode(['count' => 0]);
}
