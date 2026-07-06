<?php
$pageTitle = 'Verify OTP';
require_once __DIR__ . '/../includes/customer_auth.php';

// Redirect if there is no pending registration
if (empty($_SESSION['pending_registration']) || empty($_SESSION['registration_otp'])) {
    setFlashMessage('error', 'No pending registration found. Please sign up first.');
    header('Location: ' . getBaseUrl() . '/customer/register.php');
    exit();
}

$pending = $_SESSION['pending_registration'];
$error = '';

// Handle Resend OTP
if (isset($_GET['resend']) && $_GET['resend'] == 1) {
    $otp = strval(rand(100000, 999999));
    $_SESSION['registration_otp'] = $otp;
    $_SESSION['registration_otp_time'] = time();
    setFlashMessage('info', '[MOCK EMAIL] Verification OTP for ' . $pending['email'] . ' is: ' . $otp);
    header('Location: ' . getBaseUrl() . '/customer/verify_otp.php');
    exit();
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Security verification failed. Please try again.';
    } else {
        $enteredOtp = trim($_POST['otp'] ?? '');
        
        if (empty($enteredOtp)) {
            $error = 'Please enter the 6-digit OTP.';
        } elseif ($enteredOtp !== $_SESSION['registration_otp']) {
            $error = 'Invalid OTP. Please try again.';
        } else {
            // Check if OTP has expired (10 minutes validity)
            $otpTime = $_SESSION['registration_otp_time'] ?? 0;
            if (time() - $otpTime > 600) {
                $error = 'OTP has expired. Please request a new one.';
            } else {
                // Successful verification! Insert user details into the database
                try {
                    $pdo = getConnection();
                    $stmt = $pdo->prepare(
                        "INSERT INTO customers (full_name, username, email, phone, password, address, city, state, postal_code) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                    );
                    $stmt->execute([
                        $pending['full_name'],
                        $pending['username'],
                        $pending['email'],
                        $pending['phone'],
                        $pending['password'],
                        $pending['address'],
                        $pending['city'],
                        $pending['state'],
                        $pending['postal_code']
                    ]);

                    $customerId = $pdo->lastInsertId();
                    
                    // Log the user in
                    $_SESSION['customer_id'] = $customerId;
                    $_SESSION['customer_name'] = $pending['full_name'];
                    $_SESSION['customer_email'] = $pending['email'];

                    // Clear OTP and pending registration from session
                    unset($_SESSION['pending_registration']);
                    unset($_SESSION['registration_otp']);
                    unset($_SESSION['registration_otp_time']);

                    setFlashMessage('success', 'Email verified successfully! Welcome, ' . $pending['full_name'] . '.');
                    
                    $redirect = $_SESSION['redirect_after_login'] ?? null;
                    unset($_SESSION['redirect_after_login']);
                    header('Location: ' . ($redirect ?: getBaseUrl() . '/index.php'));
                    exit();
                } catch (PDOException $e) {
                    $error = 'Database insertion failed: ' . $e->getMessage();
                }
            }
        }
    }
}
?>
<?php require_once __DIR__ . '/../includes/customer_header.php'; ?>

<style>
body { background: #e3f2fd; }
.auth-container { max-width: 420px; margin: 50px auto; padding: 0 16px; }
.auth-card { background: #fff; border-radius: 2px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; padding: 32px 40px; }
.auth-card h2 { font-size: 20px; font-weight: 600; color: #212121; margin: 0 0 4px 0; }
.auth-subtitle { font-size: 13px; color: #878787; margin-bottom: 24px; }
.form-group { margin-bottom: 20px; }
.form-group label { display: block; font-size: 13px; font-weight: 500; color: #212121; margin-bottom: 6px; }
.form-group input { width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 2px; font-size: 16px; text-align: center; letter-spacing: 6px; font-weight: 600; outline: none; box-sizing: border-box; }
.form-group input:focus { border-color: #2874f0; }
.btn-verify { width: 100%; padding: 14px; background: #fb641b; color: #fff; border: none; border-radius: 2px; font-size: 15px; font-weight: 600; cursor: pointer; text-transform: uppercase; }
.btn-verify:hover { background: #e85a16; }
.error-msg { background: #ffebee; color: #c62828; padding: 10px 12px; border-radius: 2px; margin-bottom: 16px; font-size: 13px; border: 1px solid #ef9a9a; }
.resend-box { margin-top: 16px; text-align: center; font-size: 13px; color: #878787; }
.resend-box a { color: #2874f0; text-decoration: none; font-weight: 500; }
.resend-box a:hover { text-decoration: underline; }
</style>

<div class="auth-container">
    <div class="auth-card">
        <h2>Verify your email</h2>
        <div class="auth-subtitle">We have sent a 6-digit verification code to <strong><?php echo escapeOutput($pending['email']); ?></strong>.</div>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo escapeOutput($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <div class="form-group">
                <label for="otp">Enter 6-Digit OTP</label>
                <input type="text" id="otp" name="otp" maxlength="6" autocomplete="one-time-code" placeholder="000000" required>
            </div>
            <button type="submit" class="btn-verify">Verify & Create Account</button>
        </form>

        <div class="resend-box">
            Didn't receive the OTP? <a href="?resend=1">Resend OTP</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/customer_footer.php'; ?>
