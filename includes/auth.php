<?php
/**
 * Authentication Check
 * 
 * Validates admin session for protected pages.
 * Redirects to login page if session is not valid.
 * Starts session if not already started.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if admin is logged in
 * 
 * @return bool True if admin is authenticated
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && isset($_SESSION['admin_username']);
}

/**
 * Require authentication
 * Redirects to login page if not authenticated
 */
function requireAuth() {
    if (!isLoggedIn()) {
        $_SESSION['error_message'] = 'Please login to access the admin panel.';
        header('Location: login.php');
        exit();
    }
}

/**
 * Get current admin username
 * 
 * @return string|null Admin username or null
 */
function getAdminUsername() {
    return $_SESSION['admin_username'] ?? null;
}

/**
 * Get current admin ID
 * 
 * @return int|null Admin ID or null
 */
function getAdminId() {
    return $_SESSION['admin_id'] ?? null;
}
