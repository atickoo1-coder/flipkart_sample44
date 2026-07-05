<?php
$pageTitle = 'Customer Registration';
require_once __DIR__ . '/../includes/customer_auth.php';

// Redirect if already logged in
if (isCustomerLoggedIn()) {
    header('Location: ' . getBaseUrl() . '/index.php');
    exit();
}

$errors = [];
$formData = [
    'full_name' => '',
    'username' => '',
    'email' => '',
    'phone' => '',
    'password' => '',
    'confirm_password' => '',
    'address' => '',
    'city' => '',
    'state' => '',
    'postal_code' => ''
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'full_name' => sanitizeInput($_POST['full_name'] ?? ''),
        'username' => sanitizeInput($_POST['username'] ?? ''),
        'email' => sanitizeInput($_POST['email'] ?? ''),
        'phone' => sanitizeInput($_POST['phone'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'address' => sanitizeInput($_POST['address'] ?? ''),
        'city' => sanitizeInput($_POST['city'] ?? ''),
        'state' => sanitizeInput($_POST['state'] ?? ''),
        'postal_code' => sanitizeInput($_POST['postal_code'] ?? '')
    ];

    // Validation
    if (empty($formData['full_name'])) $errors[] = 'Full name is required';
    if (empty($formData['username'])) $errors[] = 'Username is required';
    elseif (!preg_match('/^[a-zA-Z0-9_]{3,50}$/', $formData['username'])) $errors[] = 'Username must be 3-50 alphanumeric characters';
    
    if (empty($formData['email'])) $errors[] = 'Email is required';
    elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    
    if (empty($formData['phone'])) $errors[] = 'Phone number is required';
    elseif (!preg_match('/^\d{10,15}$/', $formData['phone'])) $errors[] = 'Phone must be 10-15 digits';
    
    if (empty($formData['password'])) $errors[] = 'Password is required';
    elseif (strlen($formData['password']) < 6) $errors[] = 'Password must be at least 6 characters';
    
    if ($formData['password'] !== $formData['confirm_password']) $errors[] = 'Passwords do not match';

    // If no validation errors, check uniqueness and insert
    if (empty($errors)) {
        try {
            $pdo = getConnection();

            $stmt = $pdo->prepare("SELECT id FROM customers WHERE username = ? OR email = ?");
            $stmt->execute([$formData['username'], $formData['email']]);
            
            if ($stmt->fetch()) {
                $errors[] = 'Username or email already exists';
            } else {
                $hashedPassword = password_hash($formData['password'], PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare(
                    "INSERT INTO customers (full_name, username, email, phone, password, address, city, state, postal_code) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
                );
                $stmt->execute([
                    $formData['full_name'],
                    $formData['username'],
                    $formData['email'],
                    $formData['phone'],
                    $hashedPassword,
                    $formData['address'],
                    $formData['city'],
                    $formData['state'],
                    $formData['postal_code']
                ]);

                $_SESSION['customer_id'] = $pdo->lastInsertId();
                $_SESSION['customer_name'] = $formData['full_name'];
                $_SESSION['customer_email'] = $formData['email'];

                setFlashMessage('success', 'Registration successful! Welcome, ' . $formData['full_name'] . '.');
                
                $redirect = $_SESSION['redirect_after_login'] ?? null;
                unset($_SESSION['redirect_after_login']);
                header('Location: ' . ($redirect ?: getBaseUrl() . '/index.php'));
                exit();
            }
        } catch (PDOException $e) {
            $errors[] = 'Registration failed: ' . $e->getMessage();
        }
    }
}
?>
<?php require_once __DIR__ . '/../includes/customer_header.php'; ?>

<style>
body { background: #e3f2fd; }
.auth-container { max-width: 480px; margin: 30px auto; padding: 0 16px; }
.auth-card { background: #fff; border-radius: 2px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow: hidden; }
.auth-left { display: none; }
.auth-right { padding: 32px 40px; }
.auth-right h2 { font-size: 20px; font-weight: 600; color: #212121; margin-bottom: 4px; }
.auth-subtitle { font-size: 13px; color: #878787; margin-bottom: 24px; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 500; color: #212121; margin-bottom: 4px; }
.form-group input, .form-group textarea { width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 2px; font-size: 14px; transition: border-color 0.2s; outline: none; box-sizing: border-box; }
.form-group input:focus, .form-group textarea:focus { border-color: #2874f0; box-shadow: 0 0 0 1px #2874f0; }
.form-group textarea { resize: vertical; min-height: 60px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.btn-primary { width: 100%; padding: 12px; background: #fb641b; color: #fff; border: none; border-radius: 2px; font-size: 15px; font-weight: 600; cursor: pointer; text-transform: uppercase; letter-spacing: 0.3px; }
.btn-primary:hover { background: #e85a16; }
.auth-links { text-align: center; margin-top: 16px; font-size: 14px; color: #878787; }
.auth-links a { color: #2874f0; font-weight: 500; text-decoration: none; }
.auth-links a:hover { text-decoration: underline; }
.error-text { color: #c62828; font-size: 12px; margin-top: 4px; }
.error-alert { background: #ffebee; color: #c62828; padding: 10px 14px; border-radius: 2px; margin-bottom: 16px; font-size: 13px; border: 1px solid #ef9a9a; }
.success-alert { background: #e8f5e9; color: #2e7d32; padding: 10px 14px; border-radius: 2px; margin-bottom: 16px; font-size: 13px; border: 1px solid #a5d6a7; }
</style>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-right">
            <h2>Create Account</h2>
            <p class="auth-subtitle">Join us and start shopping</p>

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
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo escapeOutput($formData['full_name']); ?>" placeholder="Enter your full name" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo escapeOutput($formData['username']); ?>" placeholder="Choose a username" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo escapeOutput($formData['phone']); ?>" placeholder="10-digit mobile number" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo escapeOutput($formData['email']); ?>" placeholder="Enter your email" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="At least 6 characters" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address" placeholder="Street, area, landmark"><?php echo escapeOutput($formData['address']); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" id="city" name="city" value="<?php echo escapeOutput($formData['city']); ?>" placeholder="City">
                    </div>
                    <div class="form-group">
                        <label for="state">State</label>
                        <input type="text" id="state" name="state" value="<?php echo escapeOutput($formData['state']); ?>" placeholder="State">
                    </div>
                </div>

                <div class="form-group">
                    <label for="postal_code">Postal Code</label>
                    <input type="text" id="postal_code" name="postal_code" value="<?php echo escapeOutput($formData['postal_code']); ?>" placeholder="PIN code">
                </div>

                <button type="submit" class="btn-primary">Register</button>
            </form>

            <div class="auth-links">
                Already have an account? <a href="<?php echo getBaseUrl(); ?>/customer/login.php">Login</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/customer_footer.php'; ?>
