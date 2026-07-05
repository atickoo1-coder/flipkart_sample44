<?php
/**
 * Delete Category
 * 
 * Handles category deletion with confirmation.
 * Products under this category will also be deleted (CASCADE).
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';

// Only accept POST requests for security (prevents CSRF deletion via link)
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
    $_SESSION['error_message'] = 'Invalid category ID.';
    header('Location: view.php');
    exit();
}

try {
    $pdo = getConnection();

    // Fetch category to get name for message
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $category = $stmt->fetch();

    if (!$category) {
        $_SESSION['error_message'] = 'Category not found.';
        header('Location: view.php');
        exit();
    }

    // Delete the category (products will be cascade deleted)
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
    $stmt->execute([':id' => $id]);

    $_SESSION['success_message'] = 'Category "' . htmlspecialchars($category['name']) . '" deleted successfully.';

} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Failed to delete category. It may have associated products.';
}

header('Location: view.php');
exit();
