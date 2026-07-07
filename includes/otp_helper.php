<?php
/**
 * OTP Helper Functions for Customer Email Verification (with PHPMailer SMTP Support)
 */

require_once __DIR__ . '/customer_auth.php';

// Load PHPMailer files manually
require_once __DIR__ . '/PHPMailer/Exception.php';
require_once __DIR__ . '/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Generate a 6-digit numeric OTP and send it via SMTP or fallback
 * Also logs the OTP to uploads/email_otp.log for development testing
 * 
 * @param string $email The customer's email
 * @param PDO $pdo PDO connection
 * @return bool True if successful, false otherwise
 */
function sendOTP($email, $pdo) {
    try {
        // Generate a 6-digit OTP
        $otp = (string)mt_rand(100000, 999999);
        $expiresAt = date('Y-m-d H:i:s', time() + 600); // 10 minutes expiry

        // Update database with OTP details (for users who already exist, e.g., during login verification)
        $stmt = $pdo->prepare("UPDATE customers SET otp_code = ?, otp_expires_at = ? WHERE email = ?");
        $stmt->execute([$otp, $expiresAt, $email]);

        // Store details in session for verification (especially useful for pending registrations)
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['verify_otp'] = $otp;
        $_SESSION['verify_otp_expires_at'] = $expiresAt;

        // Try to send real email using PHPMailer SMTP if environment variables are set
        $smtpUser = getenv('SMTP_USER');
        $smtpPass = getenv('SMTP_PASS');
        $smtpHost = getenv('SMTP_HOST') ?: 'smtp.gmail.com';
        $smtpPort = (int)(getenv('SMTP_PORT') ?: 587);
        $smtpSecure = getenv('SMTP_SECURE') ?: 'tls';

        $emailSent = false;

        // Validate that user didn't leave SMTP_PASS as placeholder "App Password"
        if (!empty($smtpUser) && !empty($smtpPass) && strtolower(trim($smtpPass)) !== 'app password') {
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host       = $smtpHost;
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtpUser;
                $mail->Password   = $smtpPass;
                
                // Handle encryption options
                if (strtolower($smtpSecure) === 'ssl') {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                } else {
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                }
                $mail->Port       = $smtpPort;

                // Disable SSL certificate verification if needed (common for local environments)
                $mail->SMTPOptions = array(
                    'ssl' => array(
                        'verify_peer' => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true
                    )
                );

                // Recipients
                $mail->setFrom($smtpUser, 'QuickKart');
                $mail->addAddress($email);

                // Content
                $mail->isHTML(true);
                $mail->Subject = "QuickKart - Verify Your Email Account";
                $mail->Body    = "
                    <div style='font-family: Roboto, sans-serif; background-color: #f4f7f6; padding: 20px; color: #212121;'>
                        <div style='background: #fff; padding: 30px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto;'>
                            <div style='font-size: 24px; font-weight: 700; color: #2874f0; margin-bottom: 20px; text-align: center;'>QuickKart</div>
                            <p>Hello,</p>
                            <p>Thank you for registering on QuickKart! Please use the following One-Time Password (OTP) to verify your email address and activate your account. This OTP is valid for 10 minutes.</p>
                            <div style='background: #e3f2fd; padding: 15px; text-align: center; font-size: 28px; font-weight: 700; color: #1565c0; letter-spacing: 4px; margin: 20px 0; border-radius: 4px; border: 1px solid #1e88e5;'>$otp</div>
                            <p>If you did not request this verification, please ignore this email.</p>
                            <div style='font-size: 12px; color: #878787; text-align: center; margin-top: 30px;'>This is an automated system email. Please do not reply.</div>
                        </div>
                    </div>
                ";

                $mail->send();
                $emailSent = true;
            } catch (Exception $e) {
                error_log("PHPMailer SMTP Error: " . $mail->ErrorInfo);
            }
        }

        // Write to log file so developers/users can test locally without a working SMTP mail server
        $logDir = __DIR__ . '/../uploads';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/email_otp.log';
        $logEntry = "[" . date('Y-m-d H:i:s') . "] To: $email | OTP: $otp | Expiry: $expiresAt | Sent: " . ($emailSent ? 'YES' : 'NO') . "\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);

        // Store status in session
        $_SESSION['email_sent_successfully'] = $emailSent;

        return $emailSent;
    } catch (Exception $e) {
        error_log("OTP Send Error: " . $e->getMessage());
        return false;
    }
}
