<?php
require_once __DIR__ . '/../includes/customer_auth.php';

if (!isCustomerLoggedIn()) {
    header('Location: ' . getBaseUrl() . '/customer/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . getBaseUrl() . '/cart/cart.php');
    exit();
}

$quantities = $_POST['qty'] ?? [];

try {
    $pdo = getConnection();

    foreach ($quantities as $cartId => $qty) {
        $cartId = (int)$cartId;
        $qty = max(1, (int)$qty);

        // Verify cart item belongs to customer
        $stmt = $pdo->prepare(
            "SELECT c.id, c.product_id, p.stock_quantity 
             FROM cart c 
             JOIN products p ON c.product_id = p.id 
             WHERE c.id = ? AND c.customer_id = ?"
        );
        $stmt->execute([$cartId, $_SESSION['customer_id']]);
        $item = $stmt->fetch();

        if ($item) {
            $qty = min($qty, (int)$item['stock_quantity']);
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->execute([$qty, $cartId]);
        }
    }

    setFlashMessage('success', 'Cart updated successfully');
} catch (PDOException $e) {
    setFlashMessage('error', 'Failed to update cart');
}

header('Location: ' . getBaseUrl() . '/cart/cart.php');
exit();
