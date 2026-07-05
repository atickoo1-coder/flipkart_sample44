<?php
require_once __DIR__ . '/../includes/customer_auth.php';

if (!isCustomerLoggedIn()) {
    header('Location: ' . getBaseUrl() . '/customer/login.php');
    exit();
}

$cartId = (int)($_GET['id'] ?? 0);
if ($cartId <= 0) {
    header('Location: ' . getBaseUrl() . '/cart/cart.php');
    exit();
}

try {
    $pdo = getConnection();

    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND customer_id = ?");
    $stmt->execute([$cartId, $_SESSION['customer_id']]);

    if ($stmt->rowCount() > 0) {
        setFlashMessage('success', 'Item removed from cart');
    }
} catch (PDOException $e) {
    setFlashMessage('error', 'Failed to remove item');
}

header('Location: ' . getBaseUrl() . '/cart/cart.php');
exit();
