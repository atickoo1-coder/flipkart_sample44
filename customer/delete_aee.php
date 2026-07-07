<?php
/**
 * Temporary script to search and delete a user with "aee" in their username or details on Render DB.
 * Will be deleted immediately after execution.
 */
require_once __DIR__ . '/../includes/customer_auth.php';

echo "<pre>";
echo "Connecting to database on Render...\n";
try {
    $pdo = getConnection();
    echo "Connection successful!\n\n";
    
    // Find matching customer(s) first
    $stmt = $pdo->prepare("SELECT id, username, email, full_name FROM customers WHERE LOWER(username) LIKE '%aee%' OR LOWER(full_name) LIKE '%aee%'");
    $stmt->execute();
    $customers = $stmt->fetchAll();

    if (empty($customers)) {
        echo "No customer found matching 'aee' in username or full name.\n";
    } else {
        echo "Found the following matching customer(s):\n";
        foreach ($customers as $c) {
            echo "ID: {$c['id']} | Username: {$c['username']} | Email: {$c['email']} | Name: {$c['full_name']}\n";
        }

        // Delete the matching rows
        $stmtDelete = $pdo->prepare("DELETE FROM customers WHERE LOWER(username) LIKE '%aee%' OR LOWER(full_name) LIKE '%aee%'");
        $stmtDelete->execute();
        $rowsDeleted = $stmtDelete->rowCount();
        echo "\nSuccessfully deleted $rowsDeleted customer record(s) from the Render database.\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo "</pre>";
