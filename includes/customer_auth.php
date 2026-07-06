<?php
/**
 * Customer Authentication Helper
 * 
 * Session management and authentication functions for customer-facing pages.
 * Uses PDO (matching existing admin panel) and ecommerce_db database.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Prevent browser caching of personal user details (essential for shared devices)
if (!headers_sent()) {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
}

// Load database configuration
require_once __DIR__ . '/../config/database.php';

/**
 * Check if a customer is logged in
 */
function isCustomerLoggedIn() {
    return isset($_SESSION['customer_id']);
}

/**
 * Get logged-in customer details
 */
function getCustomer() {
    if (!isCustomerLoggedIn()) return null;
    
    $pdo = getConnection();
    
    $stmt = $pdo->prepare(
        "SELECT id, full_name, username, email, phone, address, city, state, postal_code, created_at 
         FROM customers WHERE id = ?"
    );
    $stmt->execute([$_SESSION['customer_id']]);
    return $stmt->fetch();
}

/**
 * Require customer authentication - redirect to login
 */
function requireCustomerAuth() {
    if (!isCustomerLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . getBaseUrl() . '/customer/login.php');
        exit();
    }
}

/**
 * Get base URL dynamically
 */
function getBaseUrl() {
    $envBaseUrl = getenv('APP_BASE_URL');
    if (!empty($envBaseUrl) && strpos($envBaseUrl, '<your-render') === false) {
        return rtrim($envBaseUrl, '/');
    }

    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Dynamically calculate the script's subdirectory path
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $scriptDir = rtrim(dirname($scriptName), '/\\');
    
    // Normalize path if we are in subdirectories
    $subdirs = ['/customer', '/admin', '/cart', '/products'];
    foreach ($subdirs as $dir) {
        if (strpos($scriptName, $dir . '/') !== false) {
            $scriptDir = rtrim(substr($scriptName, 0, strpos($scriptName, $dir)), '/\\');
            break;
        }
    }

    return $protocol . '://' . $host . $scriptDir;
}

/**
 * Set flash message
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $type = htmlspecialchars($flash['type'], ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($flash['message'], ENT_QUOTES, 'UTF-8');
        $alertClass = $type === 'error' ? 'danger' : $type;
        echo '<div class="alert alert-' . $alertClass . ' alert-dismissible fade show" role="alert" style="margin-bottom:0;">';
        echo $message;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return !empty($_SESSION['csrf_token']) && !empty($token) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Escape output for safe HTML rendering (XSS protection)
 */
function escapeOutput($data) {
    return htmlspecialchars($data ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}
?>
