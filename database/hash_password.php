<?php
/**
 * Password Hash Generator
 * 
 * Utility script to generate bcrypt password hash for admin accounts.
 * Run this script once to create a hashed password.
 * 
 * Usage: php hash_password.php
 */

echo "=== Password Hash Generator ===\n\n";

// Default admin password
$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: " . $password . "\n";
echo "Hash: " . $hash . "\n\n";
echo "Add this SQL to your database:\n\n";
echo "INSERT INTO `admins` (`username`, `email`, `password`) VALUES\n";
echo "('admin', 'admin@example.com', '" . $hash . "');\n\n";
echo "Verify with: password_verify('" . $password . "', \$hash);\n";
