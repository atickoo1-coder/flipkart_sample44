<?php
/**
 * Temporary script to test multiple SMTP ports and encryption modes on Render.
 * Will help identify which combination is blocked by the network.
 */
require_once __DIR__ . '/../includes/customer_auth.php';
require_once __DIR__ . '/../includes/PHPMailer/Exception.php';
require_once __DIR__ . '/../includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../includes/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "<pre>";
echo "=== Multi-Port SMTP Diagnostic ===\n\n";

$smtpUser = getenv('SMTP_USER');
$smtpPass = getenv('SMTP_PASS');

if (empty($smtpUser) || empty($smtpPass)) {
    echo "ERROR: SMTP_USER or SMTP_PASS environment variables are not set on Render yet!\n";
    echo "</pre>";
    exit();
}

$testConfigs = [
    [
        'host' => 'smtp-relay.brevo.com',
        'port' => 587,
        'secure' => 'tls',
        'desc' => 'Brevo standard TLS (STARTTLS)'
    ],
    [
        'host' => 'smtp-relay.brevo.com',
        'port' => 465,
        'secure' => 'ssl',
        'desc' => 'Brevo SMTPS (SSL/TLS)'
    ],
    [
        'host' => 'smtp-relay.brevo.com',
        'port' => 2525,
        'secure' => 'tls',
        'desc' => 'Brevo alternative TLS'
    ],
    [
        'host' => 'smtp.gmail.com',
        'port' => 587,
        'secure' => 'tls',
        'desc' => 'Gmail standard TLS'
    ],
    [
        'host' => 'smtp.gmail.com',
        'port' => 465,
        'secure' => 'ssl',
        'desc' => 'Gmail SMTPS'
    ]
];

foreach ($testConfigs as $idx => $cfg) {
    $num = $idx + 1;
    echo "--------------------------------------------------\n";
    echo "Test #$num: {$cfg['desc']}\n";
    echo "Target: {$cfg['host']}:{$cfg['port']} ({$cfg['secure']})\n";
    echo "--------------------------------------------------\n";

    $mail = new PHPMailer(true);
    $mail->SMTPDebug = 1; // Basic debug messages
    $mail->Debugoutput = function($str, $level) {
        echo "   DEBUG: " . trim($str) . "\n";
    };

    try {
        $mail->isSMTP();
        $mail->Host       = $cfg['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->Timeout    = 5; // Low timeout so test doesn't stall too long

        if ($cfg['secure'] === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } else {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        }
        $mail->Port       = $cfg['port'];

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->setFrom($smtpUser, 'QuickKart Diagnostic');
        $mail->addAddress($smtpUser);
        $mail->isHTML(false);
        $mail->Subject = "QuickKart Port Test";
        $mail->Body    = "Verification test successful.";

        echo "Connecting...\n";
        $mail->send();
        echo "Result: SUCCESS! This configuration works on Render.\n\n";
        // If one works, let's stop and recommend it!
    } catch (Exception $e) {
        echo "Result: FAILED - " . $mail->ErrorInfo . "\n\n";
    }
}

echo "=== End of Diagnostic ===\n";
echo "</pre>";
