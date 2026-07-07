<?php
/**
 * Temporary migration script to add verification columns to remote database.
 * Will be deleted immediately after execution.
 */
require_once __DIR__ . '/../includes/customer_auth.php';

echo "<pre>";
echo "Connecting to database on Render...\n";
try {
    $pdo = getConnection();
    echo "Connection successful!\n\n";
    
    // Check and add is_verified column
    $stmt = $pdo->prepare("SHOW COLUMNS FROM customers LIKE 'is_verified'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN is_verified TINYINT(1) DEFAULT 0");
        echo "Added column: is_verified\n";
    } else {
        echo "Column already exists: is_verified\n";
    }

    // Check and add otp_code column
    $stmt = $pdo->prepare("SHOW COLUMNS FROM customers LIKE 'otp_code'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN otp_code VARCHAR(6) DEFAULT NULL");
        echo "Added column: otp_code\n";
    } else {
        echo "Column already exists: otp_code\n";
    }

    // Check and add otp_expires_at column
    $stmt = $pdo->prepare("SHOW COLUMNS FROM customers LIKE 'otp_expires_at'");
    $stmt->execute();
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN otp_expires_at DATETIME DEFAULT NULL");
        echo "Added column: otp_expires_at\n";
    } else {
        echo "Column already exists: otp_expires_at\n";
    }

    echo "\nMigration script completed successfully!\n";
} catch (Exception $e) {
    echo "ERROR running migration: " . $e->getMessage() . "\n";
}
echo "</pre>";
