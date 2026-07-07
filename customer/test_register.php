<?php
/**
 * Temporary diagnostic script to test registration database queries and session setup.
 * Will print errors directly on screen.
 * Will be deleted immediately after troubleshooting.
 */
require_once __DIR__ . '/../includes/customer_auth.php';
require_once __DIR__ . '/../includes/otp_helper.php';

echo "<pre>";
echo "=== Registration Diagnostic Test ===\n\n";

try {
    $pdo = getConnection();
    echo "1. Database Connection: SUCCESS!\n";

    // Generate random credentials to prevent "Username or email already exists" error
    $testUsername = 'test_' . mt_rand(1000, 9999);
    $testEmail = $testUsername . '@example.com';
    $emailHash = hash('sha256', $testEmail);

    echo "Testing with Username: $testUsername, Email: $testEmail (Hash: $emailHash)\n";

    // Check uniqueness
    $stmt = $pdo->prepare("SELECT id FROM customers WHERE username = ? OR email = ?");
    $stmt->execute([$testUsername, $emailHash]);
    $exists = $stmt->fetch();
    echo "2. Uniqueness check: " . ($exists ? 'EXISTS (FAIL)' : 'UNIQUE (PASS)') . "\n";

    // Store in session
    $_SESSION['pending_registration'] = [
        'full_name' => 'Diagnostic Test',
        'username' => $testUsername,
        'email' => $emailHash,
        'phone' => '1234567890',
        'password' => 'hashedpasswordhere',
        'address' => 'Test Address',
        'city' => 'Test City',
        'state' => 'Test State',
        'postal_code' => '123456'
    ];
    $_SESSION['verify_email'] = $testEmail;
    echo "3. Session storage: SUCCESS!\n";

    // Test sendOTP
    echo "4. Executing sendOTP()...\n";
    $otpResult = sendOTP($testEmail, $pdo);
    echo "   Result of sendOTP: " . ($otpResult ? 'TRUE' : 'FALSE') . "\n";
    echo "   verify_otp in Session: " . ($_SESSION['verify_otp'] ?? 'NOT SET') . "\n";
    echo "   verify_otp_expires_at: " . ($_SESSION['verify_otp_expires_at'] ?? 'NOT SET') . "\n";
    echo "   email_sent_successfully: " . ($_SESSION['email_sent_successfully'] ?? 'NOT SET') . "\n";

    // Cleanup session
    unset($_SESSION['pending_registration']);
    unset($_SESSION['verify_email']);
    unset($_SESSION['verify_otp']);
    unset($_SESSION['verify_otp_expires_at']);

    echo "\nDiagnostic completed with NO FATAL ERRORS!\n";
} catch (Exception $e) {
    echo "\nFATAL ERROR caught during registration diagnostic:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n=== End of Test ===\n";
echo "</pre>";
