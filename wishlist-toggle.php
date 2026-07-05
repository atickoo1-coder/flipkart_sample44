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

    $stmt = $pdo->prepare("SELECT id FROM products WHERE id = ? AND status = 1");
    $stmt->execute([$productId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit();
    }

    $customerId = $_SESSION['customer_id'];

    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE customer_id = ? AND product_id = ?");
    $stmt->execute([$customerId, $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare("DELETE FROM wishlist WHERE id = ?");
        $stmt->execute([$existing['id']]);
        $action = 'removed';
    } else {
        $stmt = $pdo->prepare("INSERT INTO wishlist (customer_id, product_id) VALUES (?, ?)");
        $stmt->execute([$customerId, $productId]);
        $action = 'added';
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE customer_id = ?");
    $stmt->execute([$customerId]);
    $count = (int)$stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'action' => $action,
        'count' => $count,
        'message' => $action === 'added' ? 'Added to wishlist' : 'Removed from wishlist'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
