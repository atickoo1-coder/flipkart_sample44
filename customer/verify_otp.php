<?php
$pageTitle = 'Email Verification';
require_once __DIR__ . '/../includes/customer_auth.php';
require_once __DIR__ . '/../includes/otp_helper.php';

// Redirect if already logged in
if (isCustomerLoggedIn()) {
    header('Location: ' . getBaseUrl() . '/index.php');
    exit();
}

// Check if verification email exists in session
if (empty($_SESSION['verify_email'])) {
    setFlashMessage('warning', 'Please register or log in first to verify your email address.');
    header('Location: ' . getBaseUrl() . '/customer/login.php');
    exit();
}

$email = $_SESSION['verify_email'];
$errors = [];

// Handle Resend OTP request
if (isset($_GET['resend'])) {
    try {
        $pdo = getConnection();
        if (sendOTP($email, $pdo)) {
            setFlashMessage('success', 'A new OTP has been sent to your email.');
        } else {
            setFlashMessage('error', 'Failed to resend verification code. Please try again.');
        }
    } catch (Exception $e) {
        setFlashMessage('error', 'An error occurred: ' . $e->getMessage());
    }
    header('Location: ' . getBaseUrl() . '/customer/verify_otp.php');
    exit();
}

// Handle Form POST submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF verification
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request. Please refresh the page and try again.';
    } else {
        $otp = sanitizeInput($_POST['otp'] ?? '');

        if (empty($otp)) {
            $errors[] = 'Please enter the verification code.';
        } elseif (!preg_match('/^\d{6}$/', $otp)) {
            $errors[] = 'Verification code must be a 6-digit number.';
        }

        if (empty($errors)) {
            try {
                $pdo = getConnection();

                if (!empty($_SESSION['pending_registration'])) {
                    // Registration Verification Flow (No user in DB yet)
                    $currentTimestamp = time();
                    $expiresTimestamp = strtotime($_SESSION['verify_otp_expires_at'] ?? '0');

                    if (($_SESSION['verify_otp'] ?? '') !== $otp) {
                        $errors[] = 'Invalid verification code. Please try again.';
                    } elseif ($currentTimestamp > $expiresTimestamp) {
                        $errors[] = 'Verification code has expired. Please click "Resend Code".';
                    } else {
                        // Success! Save customer record to database with is_verified = 1
                        $pending = $_SESSION['pending_registration'];
                        
                        $stmt = $pdo->prepare(
                            "INSERT INTO customers (full_name, username, email, phone, password, address, city, state, postal_code, is_verified) 
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)"
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
                        
                        $newCustomerId = (int)$pdo->lastInsertId();

                        // Clear verification target email and pending registration details
                        unset($_SESSION['verify_email']);
                        unset($_SESSION['pending_registration']);
                        unset($_SESSION['verify_otp']);
                        unset($_SESSION['verify_otp_expires_at']);

                        // Log user in
                        $_SESSION['customer_id'] = $newCustomerId;
                        $_SESSION['customer_name'] = $pending['full_name'];
                        $_SESSION['customer_email'] = $pending['email'];

                        // Process pending cart action if exists
                        if (!empty($_SESSION['pending_cart_action'])) {
                            $pendingCart = $_SESSION['pending_cart_action'];
                            unset($_SESSION['pending_cart_action']);
                            try {
                                $stmtC = $pdo->prepare("SELECT id, quantity FROM cart WHERE customer_id = ? AND product_id = ?");
                                $stmtC->execute([$_SESSION['customer_id'], $pendingCart['product_id']]);
                                $existing = $stmtC->fetch();

                                $stmtP = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
                                $stmtP->execute([$pendingCart['product_id']]);
                                $productInfo = $stmtP->fetch();

                                if ($productInfo) {
                                    $qty = min($pendingCart['quantity'], $productInfo['stock_quantity']);
                                    if ($existing) {
                                        $newQty = min($existing['quantity'] + $qty, $productInfo['stock_quantity']);
                                        $stmtU = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                                        $stmtU->execute([$newQty, $existing['id']]);
                                    } else {
                                        $stmtI = $pdo->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)");
                                        $stmtI->execute([$_SESSION['customer_id'], $pendingCart['product_id'], $qty]);
                                    }
                                }
                            } catch (Exception $e) {
                                // Ignore
                            }
                        }

                        setFlashMessage('success', 'Email verification successful! Welcome to QuickKart.');

                        $redirect = $_SESSION['redirect_after_login'] ?? null;
                        unset($_SESSION['redirect_after_login']);
                        header('Location: ' . ($redirect ?: getBaseUrl() . '/index.php'));
                        exit();
                    }
                } else {
                    // Login Verification Flow for legacy unverified DB accounts
                    // Fetch customer matching the verification email
                    $stmt = $pdo->prepare(
                        "SELECT id, full_name, email, otp_code, otp_expires_at, is_verified 
                         FROM customers WHERE email = ?"
                    );
                    $stmt->execute([$email]);
                    $customer = $stmt->fetch();

                    if (!$customer) {
                        $errors[] = 'Customer account not found.';
                    } elseif ((int)$customer['is_verified'] === 1) {
                        // Already verified, clear verification session and redirect to login
                        unset($_SESSION['verify_email']);
                        setFlashMessage('success', 'Your account is already verified. Please log in.');
                        header('Location: ' . getBaseUrl() . '/customer/login.php');
                        exit();
                    } else {
                        // Check OTP code and expiration
                        $currentTimestamp = time();
                        $expiresTimestamp = strtotime($customer['otp_expires_at']);

                        if ($customer['otp_code'] !== $otp) {
                            $errors[] = 'Invalid verification code. Please try again.';
                        } elseif ($currentTimestamp > $expiresTimestamp) {
                            $errors[] = 'Verification code has expired. Please click "Resend Code".';
                        } else {
                            // Success! Update verification status
                            $stmtUpdate = $pdo->prepare(
                                "UPDATE customers 
                                 SET is_verified = 1, otp_code = NULL, otp_expires_at = NULL 
                                 WHERE id = ?"
                            );
                            $stmtUpdate->execute([$customer['id']]);

                            // Clear verification target email and session OTP variables
                            unset($_SESSION['verify_email']);
                            unset($_SESSION['verify_otp']);
                            unset($_SESSION['verify_otp_expires_at']);

                            // Log user in
                            $_SESSION['customer_id'] = (int)$customer['id'];
                            $_SESSION['customer_name'] = $customer['full_name'];
                            $_SESSION['customer_email'] = $customer['email'];

                            // Process pending cart action if exists
                            if (!empty($_SESSION['pending_cart_action'])) {
                                $pendingCart = $_SESSION['pending_cart_action'];
                                unset($_SESSION['pending_cart_action']);
                                try {
                                    $stmtC = $pdo->prepare("SELECT id, quantity FROM cart WHERE customer_id = ? AND product_id = ?");
                                    $stmtC->execute([$_SESSION['customer_id'], $pendingCart['product_id']]);
                                    $existing = $stmtC->fetch();

                                    $stmtP = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ?");
                                    $stmtP->execute([$pendingCart['product_id']]);
                                    $productInfo = $stmtP->fetch();

                                    if ($productInfo) {
                                        $qty = min($pendingCart['quantity'], $productInfo['stock_quantity']);
                                        if ($existing) {
                                            $newQty = min($existing['quantity'] + $qty, $productInfo['stock_quantity']);
                                            $stmtU = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                                            $stmtU->execute([$newQty, $existing['id']]);
                                        } else {
                                            $stmtI = $pdo->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)");
                                            $stmtI->execute([$_SESSION['customer_id'], $pendingCart['product_id'], $qty]);
                                        }
                                    }
                                } catch (Exception $e) {
                                    // Ignore
                                }
                            }

                            setFlashMessage('success', 'Email verification successful! Welcome to QuickKart.');

                            $redirect = $_SESSION['redirect_after_login'] ?? null;
                            unset($_SESSION['redirect_after_login']);
                            header('Location: ' . ($redirect ?: getBaseUrl() . '/index.php'));
                            exit();
                        }
                    }
                }
            } catch (PDOException $e) {
                $errors[] = 'Verification failed: ' . $e->getMessage();
            }
        }
    }
}

// Fetch active OTP code to display as a demo helper (only if email was NOT sent successfully!)
$demoOtp = null;
if (empty($_SESSION['email_sent_successfully'])) {
    $demoOtp = $_SESSION['verify_otp'] ?? null;
    if (empty($demoOtp)) {
        try {
            $pdo = getConnection();
            $stmtDemo = $pdo->prepare("SELECT otp_code FROM customers WHERE email = ?");
            $stmtDemo->execute([$email]);
            $demoRow = $stmtDemo->fetch();
            if ($demoRow && !empty($demoRow['otp_code'])) {
                $demoOtp = $demoRow['otp_code'];
            }
        } catch (Exception $e) {
            // Ignore
        }
    }
}
?>
<?php require_once __DIR__ . '/../includes/customer_header.php'; ?>

<style>
.auth-container { max-width: 480px; margin: 60px auto; padding: 0 16px; }
.auth-card { background: #fff; border-radius: 2px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); display: flex; overflow: hidden; }
.auth-left { width: 40%; background: #2874f0; color: #fff; padding: 40px 24px; display: flex; flex-direction: column; justify-content: center; }
.auth-left h2 { font-size: 24px; font-weight: 600; margin: 0 0 12px 0; }
.auth-left p { font-size: 14px; line-height: 1.6; opacity: 0.9; margin: 0; }
.auth-left .login-illustration { margin-top: 24px; }
.auth-right { width: 60%; padding: 40px; }
.auth-right .form-group { margin-bottom: 20px; }
.auth-right .form-group label { display: block; font-size: 13px; font-weight: 500; color: #212121; margin-bottom: 4px; }
.auth-right .form-group input { width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 2px; font-size: 14px; outline: none; transition: border-color 0.2s; box-sizing: border-box; }
.auth-right .form-group input:focus { border-color: #2874f0; box-shadow: 0 0 0 1px #2874f0; }
.btn-primary { width: 100%; padding: 12px; background: #fb641b; color: #fff; border: none; border-radius: 2px; font-size: 15px; font-weight: 600; cursor: pointer; text-transform: uppercase; letter-spacing: 0.3px; }
.btn-primary:hover { background: #e85a16; }
.auth-links { text-align: center; margin-top: 16px; font-size: 14px; color: #878787; }
.auth-links a { color: #2874f0; font-weight: 500; text-decoration: none; }
.auth-links a:hover { text-decoration: underline; }
.error-alert { background: #ffebee; color: #c62828; padding: 10px 14px; border-radius: 2px; margin-bottom: 16px; font-size: 13px; border: 1px solid #ef9a9a; }
.success-alert { background: #e8f5e9; color: #2e7d32; padding: 10px 14px; border-radius: 2px; margin-bottom: 16px; font-size: 13px; border: 1px solid #a5d6a7; }
.info-text { font-size: 13px; color: #878787; line-height: 1.5; margin-bottom: 20px; }
.email-highlight { font-weight: 600; color: #212121; }

@media (max-width: 600px) {
    .auth-card { flex-direction: column; }
    .auth-left { width: 100%; padding: 24px; }
    .auth-right { width: 100%; padding: 24px; }
}
</style>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-left">
            <h2>Verify</h2>
            <p>Verification code is sent to your registered email address.</p>
            <div class="login-illustration">
                <svg viewBox="0 0 100 80" fill="none" style="opacity:0.3; width: 60px; height: 60px;">
                    <rect x="10" y="10" width="80" height="60" rx="5" stroke="#fff" stroke-width="2"/>
                    <path d="M10 20 L50 50 L90 20" stroke="#fff" stroke-width="2" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
        <div class="auth-right">
            <?php
            // Display flash messages
            $flash = getFlashMessage();
            if ($flash):
                $alertClass = ($flash['type'] === 'success' || $flash['type'] === 'info') ? 'success-alert' : 'error-alert';
            ?>
                <div class="<?php echo $alertClass; ?>">
                    <?php echo escapeOutput($flash['message']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="error-alert">
                    <ul style="margin:0;padding-left:16px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo escapeOutput($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($demoOtp): ?>
                <div class="success-alert" style="background: #e3f2fd; color: #0d47a1; border-color: #90caf9; margin-bottom: 20px; font-weight: normal;">
                    <strong>Demo / QA Helper:</strong> Your verification code is <strong style="font-size: 16px; color: #1565c0; letter-spacing: 1px;"><?php echo escapeOutput($demoOtp); ?></strong>.
                </div>
            <?php endif; ?>

            <div class="info-text">
                Enter the 6-digit code sent to:<br>
                <span class="email-highlight"><?php echo escapeOutput($email); ?></span>
            </div>

            <form method="POST" action="" autocomplete="off" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="form-group">
                    <label for="otp">Verification Code</label>
                    <input type="text" id="otp" name="otp" placeholder="Enter 6-digit OTP" pattern="\d{6}" maxlength="6" inputmode="numeric" autocomplete="one-time-code" required>
                </div>

                <button type="submit" class="btn-primary">Verify Email</button>
            </form>

            <div class="auth-links">
                Didn't receive the OTP? <a href="<?php echo getBaseUrl(); ?>/customer/verify_otp.php?resend=1">Resend Code</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/customer_footer.php'; ?>
