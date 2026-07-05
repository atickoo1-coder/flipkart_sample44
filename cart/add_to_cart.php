<?php
require_once __DIR__ . '/../includes/customer_auth.php';

if (!isCustomerLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['HTTP_REFERER'] ?? getBaseUrl() . '/products/products.php';
    header('Location: ' . getBaseUrl() . '/customer/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . getBaseUrl() . '/products/products.php');
    exit();
}

$productId = (int)($_POST['product_id'] ?? 0);
$quantity = max(1, (int)($_POST['quantity'] ?? 1));
$redirectCheckout = isset($_GET['redirect_checkout']) || isset($_POST['redirect_checkout']);

if ($productId <= 0) {
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? getBaseUrl() . '/products/products.php'));
    exit();
}

try {
    $pdo = getConnection();

    $stmt = $pdo->prepare("SELECT id, name, price, stock_quantity FROM products WHERE id = ? AND status = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if (!$product) {
        setFlashMessage('error', 'Product not found');
        header('Location: ' . getBaseUrl() . '/products/products.php');
        exit();
    }

    if ($quantity > $product['stock_quantity']) {
        setFlashMessage('error', 'Requested quantity exceeds available stock');
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? getBaseUrl() . '/products/product.php?id=' . $productId));
        exit();
    }

    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE customer_id = ? AND product_id = ?");
    $stmt->execute([$_SESSION['customer_id'], $productId]);
    $existing = $stmt->fetch();

    if ($existing) {
        $newQty = min($existing['quantity'] + $quantity, $product['stock_quantity']);
        $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$newQty, $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['customer_id'], $productId, $quantity]);
    }

    setFlashMessage('success', 'Item added to cart!');
} catch (PDOException $e) {
    setFlashMessage('error', 'Database error: ' . $e->getMessage());
}

if ($redirectCheckout) {
    header('Location: ' . getBaseUrl() . '/cart/checkout.php');
} else {
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? getBaseUrl() . '/cart/cart.php'));
}
exit();
