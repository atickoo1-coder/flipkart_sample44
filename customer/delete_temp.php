<?php
/**
 * Temporary delete script to run on Render database.
 * Will be deleted immediately after execution.
 */
require_once __DIR__ . '/../includes/customer_auth.php';

$targetEmail = 'anshultickoo22@gmail.com';
$targetHash = hash('sha256', $targetEmail);

echo "<pre>";
echo "Connecting to database on Render...\n";
try {
    $pdo = getConnection();
    echo "Connection successful!\n\n";
    
    // Find matching customer(s) to report first
    $stmt = $pdo->prepare("SELECT id, username, email, full_name FROM customers WHERE email = ? OR email = ?");
    $stmt->execute([$targetEmail, $targetHash]);
    $customers = $stmt->fetchAll();

    if (empty($customers)) {
        echo "No customer found with email '$targetEmail' or its hash '$targetHash'.\n";
    } else {
        echo "Found the following matching customer(s):\n";
        foreach ($customers as $c) {
            echo "ID: {$c['id']} | Username: {$c['username']} | Email: {$c['email']} | Name: {$c['full_name']}\n";
        }

        // Delete the matching rows
        $stmtDelete = $pdo->prepare("DELETE FROM customers WHERE email = ? OR email = ?");
        $stmtDelete->execute([$targetEmail, $targetHash]);
        $rowsDeleted = $stmtDelete->rowCount();
        echo "\nSuccessfully deleted $rowsDeleted customer record(s) from the Render database.\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo "</pre>";
