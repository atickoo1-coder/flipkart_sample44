<?php
/**
 * OTP Helper Functions for Customer Email Verification
 */

require_once __DIR__ . '/customer_auth.php';

/**
 * Generate a 6-digit numeric OTP and send it via email
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

        // Update database with OTP details
        $stmt = $pdo->prepare("UPDATE customers SET otp_code = ?, otp_expires_at = ? WHERE email = ?");
        $stmt->execute([$otp, $expiresAt, $email]);

        if ($stmt->rowCount() === 0) {
            // Check if customer exists first
            $stmtCheck = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
            $stmtCheck->execute([$email]);
            if (!$stmtCheck->fetch()) {
                return false;
            }
        }

        // Prepare email
        $subject = "QuickKart - Verify Your Email Account";
        
        $message = "
        <html>
        <head>
            <title>QuickKart Email Verification</title>
            <style>
                body { font-family: Roboto, sans-serif; background-color: #f4f7f6; padding: 20px; color: #212121; }
                .card { background: #fff; padding: 30px; border-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); max-width: 500px; margin: 0 auto; }
                .logo { font-size: 24px; font-weight: 700; color: #2874f0; margin-bottom: 20px; text-align: center; }
                .otp-box { background: #e3f2fd; padding: 15px; text-align: center; font-size: 28px; font-weight: 700; color: #1565c0; letter-spacing: 4px; margin: 20px 0; border-radius: 4px; border: 1px solid #1e88e5; }
                .footer { font-size: 12px; color: #878787; text-align: center; margin-top: 30px; }
            </style>
        </head>
        <body>
            <div class='card'>
                <div class='logo'>QuickKart</div>
                <p>Hello,</p>
                <p>Thank you for registering on QuickKart! Please use the following One-Time Password (OTP) to verify your email address and activate your account. This OTP is valid for 10 minutes.</p>
                <div class='otp-box'>$otp</div>
                <p>If you did not request this verification, please ignore this email.</p>
                <div class='footer'>This is an automated system email. Please do not reply.</div>
            </div>
        </body>
        </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: no-reply@quickkart.com" . "\r\n";

        // Try to send real email
        @mail($email, $subject, $message, $headers);

        // Also write to a log file so developers/users can test locally without a working SMTP mail server
        $logDir = __DIR__ . '/../uploads';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . '/email_otp.log';
        $logEntry = "[" . date('Y-m-d H:i:s') . "] To: $email | OTP: $otp | Expiry: $expiresAt\n";
        file_put_contents($logFile, $logEntry, FILE_APPEND);

        return true;
    } catch (Exception $e) {
        error_log("OTP Send Error: " . $e->getMessage());
        return false;
    }
}
