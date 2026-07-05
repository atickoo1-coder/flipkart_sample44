<?php
/**
 * Delete Product
 * 
 * Handles product deletion with associated image removal from uploads folder.
 * Uses POST request for CSRF protection.
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

// Only accept POST requests for security
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = 'Invalid request method.';
    header('Location: view.php');
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !isset($_SESSION['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error_message'] = 'Invalid security token. Please try again.';
    header('Location: view.php');
    exit();
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    $_SESSION['error_message'] = 'Invalid product ID.';
    header('Location: view.php');
    exit();
}

try {
    $pdo = getConnection();

    // Fetch product to get image filename
    $stmt = $pdo->prepare("SELECT name, image FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch();

    if (!$product) {
        $_SESSION['error_message'] = 'Product not found.';
        header('Location: view.php');
        exit();
    }

    // Delete associated image file
    if ($product['image']) {
        $imagePath = __DIR__ . '/../../uploads/' . $product['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Delete product from database
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute([':id' => $id]);

    $_SESSION['success_message'] = 'Product "' . htmlspecialchars($product['name']) . '" deleted successfully.';

} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Failed to delete product. Please try again.';
}

header('Location: view.php');
exit();
