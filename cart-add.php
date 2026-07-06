<?php
require_once __DIR__ . '/includes/customer_auth.php';

header('Content-Type: application/json');

if (!isCustomerLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['HTTP_REFERER'] ?? getBaseUrl() . '/index.php';
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$productId = (int)($_POST['product_id'] ?? 0);
$quantity = max(1, (int)($_POST['quantity'] ?? 1));

if ($productId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid product']);
    exit();
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit();
}

try {
    $pdo = getConnection();

    $stmt = $pdo->prepare("SELECT id, name, price, stock_quantity FROM products WHERE id = ? AND status = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
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

    // Remove from wishlist if it exists there
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE customer_id = ? AND product_id = ?");
    $stmt->execute([$customerId, $productId]);

    // Count remaining items in wishlist
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM wishlist WHERE customer_id = ?");
    $stmt->execute([$customerId]);
    $wishlistCount = (int)$stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'message' => 'Added to cart!',
        'wishlist_count' => $wishlistCount
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
