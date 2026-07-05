<?php
require_once __DIR__ . '/includes/customer_auth.php';

header('Content-Type: application/json');

if (!isCustomerLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['HTTP_REFERER'] ?? getBaseUrl() . '/index.php';
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$productId = (int)($_POST['product_id'] ?? 0);
if ($productId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit();
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit();
}

try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE customer_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['customer_id'], $productId]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'Item not found in wishlist']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE customer_id = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    $count = (int)$stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'message' => 'Removed from wishlist',
        'count' => $count
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
