<?php
/**
 * Admin Panel Index
 * 
 * Redirects to dashboard if authenticated, otherwise to login page.
 */

session_start();

if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
} else {
    header('Location: login.php');
}
exit();
