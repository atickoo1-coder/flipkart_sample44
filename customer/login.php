<?php
$pageTitle = 'Customer Login';
require_once __DIR__ . '/../includes/customer_auth.php';

if (isCustomerLoggedIn()) {
    header('Location: ' . getBaseUrl() . '/index.php');
    exit();
}

$errors = [];
$loginInput = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loginInput = sanitizeInput($_POST['login_input'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($loginInput)) $errors[] = 'Email or username is required';
    if (empty($password)) $errors[] = 'Password is required';

    if (empty($errors)) {
        try {
            $pdo = getConnection();

            $stmt = $pdo->prepare(
                "SELECT id, full_name, username, email, phone, password, address, city, state, postal_code 
                 FROM customers WHERE email = ? OR username = ?"
            );
            $stmt->execute([$loginInput, $loginInput]);
            $customer = $stmt->fetch();

            if ($customer && password_verify($password, $customer['password'])) {
                $_SESSION['customer_id'] = (int)$customer['id'];
                $_SESSION['customer_name'] = $customer['full_name'];
                $_SESSION['customer_email'] = $customer['email'];

                setFlashMessage('success', 'Welcome back, ' . $customer['full_name'] . '!');

                $redirect = $_SESSION['redirect_after_login'] ?? null;
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . ($redirect ?: getBaseUrl() . '/index.php'));
                exit();
            } else {
                $errors[] = 'Invalid email/username or password';
            }
        } catch (PDOException $e) {
            $errors[] = 'Login failed: ' . $e->getMessage();
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
.terms-text { font-size: 12px; color: #878787; margin-top: 16px; line-height: 1.5; }
.terms-text a { color: #2874f0; text-decoration: none; }

@media (max-width: 600px) {
    .auth-card { flex-direction: column; }
    .auth-left { width: 100%; padding: 24px; }
    .auth-right { width: 100%; padding: 24px; }
}
</style>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-left">
            <h2>Login</h2>
            <p>Get access to your orders, wishlist and recommendations</p>
            <div class="login-illustration">
                <svg viewBox="0 0 100 80" fill="none" style="opacity:0.3;">
                    <rect x="10" y="10" width="80" height="60" rx="5" stroke="#fff" stroke-width="2"/>
                    <line x1="30" y1="30" x2="70" y2="30" stroke="#fff" stroke-width="2"/>
                    <line x1="30" y1="40" x2="60" y2="40" stroke="#fff" stroke-width="2"/>
                    <line x1="30" y1="50" x2="50" y2="50" stroke="#fff" stroke-width="2"/>
                </svg>
            </div>
        </div>
        <div class="auth-right">
            <?php if (!empty($errors)): ?>
                <div class="error-alert">
                    <ul style="margin:0;padding-left:16px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo escapeOutput($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="form-group">
                    <label for="login_input">Email or Username</label>
                    <input type="text" id="login_input" name="login_input" value="<?php echo escapeOutput($loginInput); ?>" placeholder="Enter email or username" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>

                <button type="submit" class="btn-primary">Login</button>

                <div class="terms-text">
                    By continuing, you agree to QuickKart's <a href="#">Terms of Use</a> and <a href="#">Privacy Policy</a>.
                </div>
            </form>

            <div class="auth-links">
                New to QuickKart? <a href="<?php echo getBaseUrl(); ?>/customer/register.php">Create an account</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/customer_footer.php'; ?>
