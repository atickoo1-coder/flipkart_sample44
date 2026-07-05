<?php
/**
 * Admin Header
 * 
 * Contains the HTML head, CSS links, and opening body tags.
 * To be included at the top of every admin page.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Require authentication
require_once __DIR__ . '/auth.php';
requireAuth();

// Get current page name for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo isset($page_title) ? htmlspecialchars($page_title) : 'E-Commerce'; ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Admin CSS -->
    <?php $scriptPath = $_SERVER['SCRIPT_NAME']; $adminPos = strrpos($scriptPath, '/admin/'); $adminBase = $adminPos !== false ? substr($scriptPath, 0, $adminPos + 6) : dirname($scriptPath); ?>
    <link rel="stylesheet" href="<?php echo $adminBase; ?>/assets/css/style.css">
</head>
<body>
