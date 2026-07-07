<?php
require_once __DIR__ . '/../includes/customer_auth.php';
echo "<pre>";
try {
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT id, username, email, full_name, is_verified FROM customers");
    $stmt->execute();
    $all = $stmt->fetchAll();
    echo "Total customers in database: " . count($all) . "\n";
    foreach ($all as $c) {
        echo "ID: {$c['id']} | Username: {$c['username']} | Email: {$c['email']} | Name: {$c['full_name']} | Verified: {$c['is_verified']}\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
echo "</pre>";
