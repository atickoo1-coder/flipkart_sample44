<?php
/**
 * Temporary verbose SMTP debug script to run on Render server.
 * Will print the full SMTP handshakes and error logs.
 * Will be deleted immediately after troubleshooting.
 */
require_once __DIR__ . '/../includes/customer_auth.php';
require_once __DIR__ . '/../includes/PHPMailer/Exception.php';
require_once __DIR__ . '/../includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../includes/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "<pre>";
echo "=== Verbose SMTP Debug Test ===\n\n";

$smtpUser = getenv('SMTP_USER');
$smtpPass = getenv('SMTP_PASS');
$smtpHost = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
$smtpPort = (int)(getenv('SMTP_PORT') ?: 587);
$smtpSecure = getenv('SMTP_SECURE') ?: 'tls';

echo "Environment Configuration:\n";
echo "Host: $smtpHost\n";
echo "Port: $smtpPort\n";
echo "Secure: $smtpSecure\n";
echo "User: " . ($smtpUser ? $smtpUser : 'NOT SET') . "\n";
echo "Pass: " . ($smtpPass ? 'SET (Length: ' . strlen($smtpPass) . ')' : 'NOT SET') . "\n\n";

if (empty($smtpUser) || empty($smtpPass)) {
    echo "ERROR: SMTP_USER or SMTP_PASS environment variables are not configured on Render yet!\n";
    echo "Please configure them in your Render dashboard environment tab and wait for redeployment to finish.\n";
    echo "</pre>";
    exit();
}

$mail = new PHPMailer(true);

try {
    // Enable verbose debug output
    $mail->SMTPDebug = 2; 
    // Echo debug output directly
    $mail->Debugoutput = function($str, $level) {
        echo "DEBUG: $str\n";
    };

    // Server settings
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

    // SSL Peer options
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );

    // Recipients
    $mail->setFrom($smtpUser, 'QuickKart Test');
    $mail->addAddress($smtpUser); // Send to self for test

    // Content
    $mail->isHTML(false);
    $mail->Subject = "SMTP Test Connection";
    $mail->Body    = "This is a test email to verify SMTP connection settings.";

    echo "Attempting to send email...\n";
    $mail->send();
    echo "\nSUCCESS: SMTP Connection established and email sent successfully!\n";
} catch (Exception $e) {
    echo "\nFAILURE: SMTP Connection failed.\n";
    echo "Mailer Error Info: " . $mail->ErrorInfo . "\n";
    echo "Exception Message: " . $e->getMessage() . "\n";
}

echo "\n=== End of Test ===\n";
echo "</pre>";
