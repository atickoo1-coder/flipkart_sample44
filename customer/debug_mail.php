<?php
/**
 * Temporary script to test current SMTP environment settings on Render.
 * Will print the exact error message from the SMTP server.
 * Will be deleted immediately after troubleshooting.
 */
require_once __DIR__ . '/../includes/customer_auth.php';
require_once __DIR__ . '/../includes/PHPMailer/Exception.php';
require_once __DIR__ . '/../includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../includes/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "<pre>";
echo "=== Live SMTP Test ===\n\n";

$smtpUser = getenv('SMTP_USER');
$smtpPass = getenv('SMTP_PASS');
$smtpHost = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
$smtpPort = (int)(getenv('SMTP_PORT') ?: 587);
$smtpSecure = getenv('SMTP_SECURE') ?: 'tls';

echo "Current Environment Settings on Render:\n";
echo "Host: $smtpHost\n";
echo "Port: $smtpPort\n";
echo "Secure: $smtpSecure\n";
echo "User: $smtpUser\n";
echo "Pass: " . ($smtpPass ? 'SET (Length: ' . strlen($smtpPass) . ')' : 'NOT SET') . "\n\n";

if (empty($smtpUser) || empty($smtpPass)) {
    echo "ERROR: SMTP_USER or SMTP_PASS environment variables are not set on Render yet!\n";
    echo "</pre>";
    exit();
}

$mail = new PHPMailer(true);
$mail->SMTPDebug = 2; // Verbose debug output
$mail->Debugoutput = function($str, $level) {
    echo "DEBUG: " . trim($str) . "\n";
};

try {
    $mail->isSMTP();
    $mail->Host       = $smtpHost;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUser;
    $mail->Password   = $smtpPass;

    if (strtolower($smtpSecure) === 'ssl') {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    } else {
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    }
    $mail->Port       = $smtpPort;

    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    $mail->setFrom($smtpUser, 'QuickKart Test');
    $mail->addAddress($smtpUser);
    $mail->isHTML(false);
    $mail->Subject = "QuickKart SMTP Connection Test";
    $mail->Body    = "This confirms your SMTP settings are correct.";

    echo "Connecting to SMTP server...\n";
    $mail->send();
    echo "\nSUCCESS: SMTP connected and test email sent successfully to $smtpUser!\n";
} catch (Exception $e) {
    echo "\nFAILURE: SMTP send failed.\n";
    echo "Error Details: " . $mail->ErrorInfo . "\n";
}

echo "\n=== End of Test ===\n";
echo "</pre>";
