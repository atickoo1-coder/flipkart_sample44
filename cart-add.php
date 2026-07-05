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
$quantity = max(1, (int)($_POST['quantity'] ?? 1));

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

    $stmt = $pdo->prepare("SELECT id, name, price, stock_quantity FROM products WHERE id = ? AND status = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit();
    }

    if ($product['stock_quantity'] <= 0) {
        echo json_encode(['success' => false, 'message' => 'Product is out of stock']);
        exit();
    }

    if ($quantity > $product['stock_quantity']) {
        echo json_encode(['success' => false, 'message' => 'Requested quantity exceeds stock']);
        exit();
    }

    $customerId = $_SESSION['customer_id'];

    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE customer_id = ? AND product_id = ?");
    $stmt->execute([$customerId, $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $newQty = min($existing['quantity'] + $quantity, $product['stock_quantity']);
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$newQty, $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$customerId, $productId, $quantity]);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Added to cart!'
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
