<?php
$pageTitle = 'My Profile';
require_once __DIR__ . '/../includes/customer_auth.php';
requireCustomerAuth();

$pdo = getConnection();
$errors = [];
$success = false;

$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$_SESSION['customer_id']]);
$customer = $stmt->fetch();

if (!$customer) {
    header('Location: ' . getBaseUrl() . '/customer/logout.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $city = sanitizeInput($_POST['city'] ?? '');
        $state = sanitizeInput($_POST['state'] ?? '');
        $postalCode = sanitizeInput($_POST['postal_code'] ?? '');

        if (empty($fullName)) $errors[] = 'Full name is required';
        if (empty($phone)) $errors[] = 'Phone is required';

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare(
                    "UPDATE customers SET full_name = ?, phone = ?, address = ?, city = ?, state = ?, postal_code = ? WHERE id = ?"
                );
                $stmt->execute([$fullName, $phone, $address, $city, $state, $postalCode, $_SESSION['customer_id']]);
                $_SESSION['customer_name'] = $fullName;
                $success = 'Profile updated successfully';

                $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
                $stmt->execute([$_SESSION['customer_id']]);
                $customer = $stmt->fetch();
            } catch (PDOException $e) {
                $errors[] = 'Update failed: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword)) $errors[] = 'Current password is required';
        if (empty($newPassword)) $errors[] = 'New password is required';
        elseif (strlen($newPassword) < 6) $errors[] = 'New password must be at least 6 characters';
        if ($newPassword !== $confirmPassword) $errors[] = 'Passwords do not match';

        if (empty($errors)) {
            if (!password_verify($currentPassword, $customer['password'])) {
                $errors[] = 'Current password is incorrect';
            } else {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE customers SET password = ? WHERE id = ?");
                $stmt->execute([$hashedPassword, $_SESSION['customer_id']]);
                $success = 'Password changed successfully';
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT COUNT(*) as order_count FROM orders WHERE customer_id = ?");
$stmt->execute([$_SESSION['customer_id']]);
$orderCount = $stmt->fetch()['order_count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as cart_count FROM cart WHERE customer_id = ?");
$stmt->execute([$_SESSION['customer_id']]);
$cartCount = $stmt->fetch()['cart_count'];
?>
<?php require_once __DIR__ . '/../includes/customer_header.php'; ?>

<style>
.profile-container { max-width: 1024px; margin: 24px auto; padding: 0 16px; display: grid; grid-template-columns: 280px 1fr; gap: 20px; }
.profile-sidebar { background: #fff; border-radius: 2px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); padding: 24px; height: fit-content; }
.profile-sidebar h3 { font-size: 14px; color: #878787; text-transform: uppercase; letter-spacing: 0.5px; margin: 0 0 16px 0; }
.profile-sidebar ul { list-style: none; padding: 0; margin: 0; }
.profile-sidebar li { margin-bottom: 4px; }
.profile-sidebar a { display: flex; align-items: center; gap: 10px; padding: 10px 12px; color: #212121; text-decoration: none; font-size: 14px; border-radius: 2px; transition: background 0.2s; }
.profile-sidebar a:hover, .profile-sidebar a.active { background: #f5faff; color: #2874f0; font-weight: 500; }
.profile-sidebar a svg { width: 18px; height: 18px; flex-shrink: 0; }
.profile-main { background: #fff; border-radius: 2px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); padding: 32px; }
.profile-main h2 { font-size: 20px; font-weight: 600; color: #212121; margin: 0 0 4px 0; }
.profile-subtitle { font-size: 13px; color: #878787; margin-bottom: 24px; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 500; color: #212121; margin-bottom: 4px; }
.form-group input, .form-group textarea { width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 2px; font-size: 14px; outline: none; transition: border-color 0.2s; box-sizing: border-box; }
.form-group input:focus, .form-group textarea:focus { border-color: #2874f0; box-shadow: 0 0 0 1px #2874f0; }
.form-group textarea { resize: vertical; min-height: 60px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.btn-primary { padding: 10px 24px; background: #fb641b; color: #fff; border: none; border-radius: 2px; font-size: 14px; font-weight: 600; cursor: pointer; text-transform: uppercase; }
.btn-primary:hover { background: #e85a16; }
.btn-secondary { padding: 10px 24px; background: #fff; color: #2874f0; border: 1px solid #2874f0; border-radius: 2px; font-size: 14px; font-weight: 500; cursor: pointer; }
.btn-secondary:hover { background: #f5faff; }
.success-alert { background: #e8f5e9; color: #2e7d32; padding: 10px 14px; border-radius: 2px; margin-bottom: 16px; font-size: 13px; border: 1px solid #a5d6a7; }
.error-alert { background: #ffebee; color: #c62828; padding: 10px 14px; border-radius: 2px; margin-bottom: 16px; font-size: 13px; border: 1px solid #ef9a9a; }
.profile-card { background: #f5faff; padding: 16px; border-radius: 2px; margin-bottom: 24px; border: 1px solid #e3f2fd; }
.profile-card h3 { font-size: 14px; font-weight: 600; color: #212121; margin: 0 0 8px 0; }
.profile-card p { font-size: 14px; color: #555; margin: 0 0 4px 0; }
.password-section { margin-top: 32px; padding-top: 24px; border-top: 1px solid #f0f0f0; }
.password-section h3 { font-size: 16px; font-weight: 600; color: #212121; margin: 0 0 16px 0; }

@media (max-width: 768px) {
    .profile-container { grid-template-columns: 1fr; }
    .form-row { grid-template-columns: 1fr; }
}
</style>

<div class="profile-container">
    <div class="profile-sidebar">
        <h3>My Account</h3>
        <ul>
            <li><a href="<?php echo getBaseUrl(); ?>/customer/profile.php" class="active">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                My Profile
            </a></li>
            <li><a href="<?php echo getBaseUrl(); ?>/customer/orders.php">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                Orders (<?php echo $orderCount; ?>)
            </a></li>
            <li><a href="<?php echo getBaseUrl(); ?>/cart/cart.php">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                Cart (<?php echo $cartCount; ?>)
            </a></li>
        </ul>
    </div>

    <div class="profile-main">
        <h2>My Profile</h2>
        <p class="profile-subtitle">Manage your personal information</p>

        <?php if ($success): ?>
            <div class="success-alert"><?php echo escapeOutput($success); ?></div>
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

        <div class="profile-card">
            <h3>Account Information</h3>
            <p><strong>Email:</strong> <?php echo escapeOutput($customer['email']); ?></p>
            <p><strong>Username:</strong> <?php echo escapeOutput($customer['username']); ?></p>
            <p><strong>Member since:</strong> <?php echo date('d M Y', strtotime($customer['created_at'])); ?></p>
        </div>

        <form method="POST" action="" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="update_profile">

            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo escapeOutput($customer['full_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" value="<?php echo escapeOutput($customer['phone']); ?>" required>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address"><?php echo escapeOutput($customer['address'] ?? ''); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?php echo escapeOutput($customer['city'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" value="<?php echo escapeOutput($customer['state'] ?? ''); ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="postal_code">Postal Code</label>
                <input type="text" id="postal_code" name="postal_code" value="<?php echo escapeOutput($customer['postal_code'] ?? ''); ?>">
            </div>

            <button type="submit" class="btn-primary">Save Changes</button>
        </form>

        <div class="password-section">
            <h3>Change Password</h3>
            <form method="POST" action="" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                <input type="hidden" name="action" value="change_password">

                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" id="current_password" name="current_password" placeholder="Enter current password">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" placeholder="At least 6 characters">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter new password">
                    </div>
                </div>

                <button type="submit" class="btn-secondary">Change Password</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/customer_footer.php'; ?>
