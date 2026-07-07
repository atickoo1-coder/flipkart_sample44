<?php
/**
 * Temporary diagnostic and delete script to run on Render database.
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
    
    // List all customers currently in DB
    $stmtAll = $pdo->prepare("SELECT id, username, email, full_name, created_at FROM customers");
    $stmtAll->execute();
    $all = $stmtAll->fetchAll();

    echo "Total customers in database: " . count($all) . "\n";
    foreach ($all as $c) {
        echo "ID: {$c['id']} | Username: {$c['username']} | Email: {$c['email']} | Name: {$c['full_name']} | Created: {$c['created_at']}\n";
    }

    // Try deleting target email variations if matched
    // Lowercase hash
    $stmtDelete = $pdo->prepare("DELETE FROM customers WHERE email = ? OR email = ? OR LOWER(username) = 'anshultickoo22'");
    $stmtDelete->execute([$targetEmail, $targetHash]);
    $rowsDeleted = $stmtDelete->rowCount();
    if ($rowsDeleted > 0) {
        echo "\nSuccessfully deleted $rowsDeleted customer record(s) matching '$targetEmail' or username 'anshultickoo22'.\n";
    } else {
        echo "\nNo direct match deleted for '$targetEmail'.\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
echo "</pre>";
