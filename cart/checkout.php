<?php
$pageTitle = 'Checkout';
require_once __DIR__ . '/../includes/customer_auth.php';
requireCustomerAuth();

$pdo = getConnection();

// Get customer data
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$_SESSION['customer_id']]);
$customer = $stmt->fetch();

if (!$customer) {
    header('Location: ' . getBaseUrl() . '/customer/logout.php');
    exit();
}

// Get cart items
$stmt = $pdo->prepare(
    "SELECT c.id as cart_id, c.quantity, c.product_id, 
            p.name, p.slug, p.price, p.image, p.stock_quantity, p.brand
     FROM cart c 
     JOIN products p ON c.product_id = p.id 
     WHERE c.customer_id = ?
     ORDER BY c.created_at DESC"
);
$stmt->execute([$_SESSION['customer_id']]);
$cartItems = $stmt->fetchAll();

if (empty($cartItems)) {
    setFlashMessage('error', 'Your cart is empty');
    header('Location: ' . getBaseUrl() . '/cart/cart.php');
    exit();
}

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$deliveryCharge = ($subtotal >= 499) ? 0 : 40;
$totalAmount = $subtotal + $deliveryCharge;

// Handle form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $fullName = sanitizeInput($_POST['full_name'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');
    $address = sanitizeInput($_POST['address'] ?? '');
    $city = sanitizeInput($_POST['city'] ?? '');
    $state = sanitizeInput($_POST['state'] ?? '');
    $postalCode = sanitizeInput($_POST['postal_code'] ?? '');
    $paymentMethod = $_POST['payment_method'] ?? 'cod';

    if (empty($fullName)) $errors[] = 'Full name is required';
    if (empty($phone)) $errors[] = 'Phone is required';
    if (empty($address)) $errors[] = 'Address is required';
    if (empty($city)) $errors[] = 'City is required';
    if (empty($state)) $errors[] = 'State is required';
    if (empty($postalCode)) $errors[] = 'Postal code is required';

    if ($paymentMethod === 'online') {
        $onlineType = $_POST['online_type'] ?? '';
        if ($onlineType === 'card') {
            $cardName = sanitizeInput($_POST['card_name'] ?? '');
            $cardNumber = sanitizeInput($_POST['card_number'] ?? '');
            $cardExpiry = sanitizeInput($_POST['card_expiry'] ?? '');
            $cardCvv = sanitizeInput($_POST['card_cvv'] ?? '');

            if (empty($cardName)) $errors[] = 'Cardholder name is required';
            if (empty($cardNumber)) $errors[] = 'Card number is required';
            elseif (strlen(str_replace(' ', '', $cardNumber)) < 16) $errors[] = 'Card number must be 16 digits';
            
            if (empty($cardExpiry)) $errors[] = 'Card expiry date is required';
            elseif (!preg_match('/^(0[1-9]|1[0-2])\/[0-9]{2}$/', $cardExpiry)) $errors[] = 'Invalid expiry date format (MM/YY)';
            
            if (empty($cardCvv)) $errors[] = 'Card CVV is required';
            elseif (strlen($cardCvv) < 3) $errors[] = 'CVV must be 3 digits';
        } elseif ($onlineType === 'upi') {
            $upiId = sanitizeInput($_POST['upi_id'] ?? '');
            if (empty($upiId)) $errors[] = 'UPI ID is required';
            elseif (strpos($upiId, '@') === false) $errors[] = 'Invalid UPI ID format (must contain @)';
        } else {
            $errors[] = 'Please select a valid online payment option (Card or UPI)';
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // Verify stock availability for all items
            $stockCheck = $pdo->prepare("SELECT stock_quantity FROM products WHERE id = ? FOR UPDATE");
            foreach ($cartItems as $item) {
                $stockCheck->execute([$item['product_id']]);
                $product = $stockCheck->fetch();
                if (!$product || $product['stock_quantity'] < $item['quantity']) {
                    throw new PDOException('Insufficient stock for: ' . $item['name']);
                }
            }

            // Generate unique order number
            $orderNumber = 'ORD' . strtoupper(substr(uniqid(), -8)) . date('Ymd');

            // Insert order
            $stmt = $pdo->prepare(
                "INSERT INTO orders (order_number, customer_id, full_name, phone, address, city, state, postal_code, total_amount, order_status, payment_status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)"
            );
            $paymentStatus = ($paymentMethod === 'cod') ? 'pending' : 'paid';
            $stmt->execute([$orderNumber, $_SESSION['customer_id'], $fullName, $phone, $address, $city, $state, $postalCode, $totalAmount, $paymentStatus]);
            $orderId = $pdo->lastInsertId();

            // Insert order items and reduce stock
            $insertItem = $pdo->prepare(
                "INSERT INTO order_items (order_id, product_id, product_name, product_image, quantity, price, total) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $reduceStock = $pdo->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");

            foreach ($cartItems as $item) {
                $itemTotal = $item['price'] * $item['quantity'];
                $insertItem->execute([
                    $orderId,
                    $item['product_id'],
                    $item['name'],
                    $item['image'],
                    $item['quantity'],
                    $item['price'],
                    $itemTotal
                ]);
                $reduceStock->execute([$item['quantity'], $item['product_id']]);
            }

            // Clear cart
            $stmt = $pdo->prepare("DELETE FROM cart WHERE customer_id = ?");
            $stmt->execute([$_SESSION['customer_id']]);

            $pdo->commit();

            setFlashMessage('success', 'Order placed successfully! Order #' . $orderNumber);
            header('Location: ' . getBaseUrl() . '/customer/orders.php');
            exit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = 'Order failed: ' . $e->getMessage();
        }
    }
}
?>
<?php require_once __DIR__ . '/../includes/customer_header.php'; ?>

<style>
.checkout-container { max-width: 1024px; margin: 20px auto; padding: 0 16px; display: grid; grid-template-columns: 1fr 340px; gap: 20px; align-items: start; }
.checkout-form { background: #fff; border-radius: 2px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); padding: 24px 32px; }
.checkout-form h2 { font-size: 18px; font-weight: 600; color: #212121; margin: 0 0 4px 0; }
.checkout-subtitle { font-size: 13px; color: #878787; margin-bottom: 24px; }
.checkout-section { margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #f0f0f0; }
.checkout-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.checkout-section h3 { font-size: 14px; font-weight: 500; color: #212121; margin: 0 0 12px 0; display: flex; align-items: center; gap: 8px; }
.checkout-section h3 .step { width: 24px; height: 24px; background: #2874f0; color: #fff; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 600; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 500; color: #212121; margin-bottom: 4px; }
.form-group input, .form-group textarea { width: 100%; padding: 10px 12px; border: 1px solid #e0e0e0; border-radius: 2px; font-size: 14px; outline: none; transition: border-color 0.2s; box-sizing: border-box; }
.form-group input:focus, .form-group textarea:focus { border-color: #2874f0; box-shadow: 0 0 0 1px #2874f0; }
.form-group textarea { resize: vertical; min-height: 60px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

/* Payment options */
.payment-options { display: flex; flex-direction: column; gap: 8px; }
.payment-option { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border: 1px solid #e0e0e0; border-radius: 2px; cursor: pointer; transition: border-color 0.2s; }
.payment-option:hover { border-color: #2874f0; }
.payment-option input[type="radio"] { accent-color: #2874f0; margin: 0; }
.payment-option-label { font-size: 14px; color: #212121; }
.payment-option-desc { font-size: 12px; color: #878787; }

/* Sidebar */
.checkout-sidebar { background: #fff; border-radius: 2px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); padding: 20px; position: sticky; top: 16px; }
.checkout-sidebar h3 { font-size: 16px; font-weight: 600; color: #212121; margin: 0 0 16px 0; padding-bottom: 12px; border-bottom: 1px solid #f0f0f0; }
.checkout-item { display: flex; gap: 12px; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #f5f5f5; }
.checkout-item:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.checkout-item-img { width: 48px; height: 48px; flex-shrink: 0; border: 1px solid #f0f0f0; border-radius: 2px; display: flex; align-items: center; justify-content: center; }
.checkout-item-img img { max-width: 100%; max-height: 100%; object-fit: contain; }
.checkout-item-info { flex: 1; min-width: 0; }
.checkout-item-name { font-size: 13px; color: #212121; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.checkout-item-meta { font-size: 12px; color: #878787; }
.checkout-item-price { font-size: 13px; font-weight: 500; color: #212121; text-align: right; flex-shrink: 0; }
.price-line { display: flex; justify-content: space-between; font-size: 14px; color: #555; margin-bottom: 8px; }
.price-line.total { font-size: 18px; font-weight: 600; color: #212121; border-top: 1px solid #f0f0f0; padding-top: 12px; margin-top: 8px; }
.price-line .free { color: #388e3c; font-weight: 500; }
.btn-place-order { width: 100%; padding: 14px; background: #fb641b; color: #fff; border: none; border-radius: 2px; font-size: 16px; font-weight: 600; cursor: pointer; text-transform: uppercase; margin-top: 16px; }
.btn-place-order:hover { background: #e85a16; }
.error-alert { background: #ffebee; color: #c62828; padding: 10px 14px; border-radius: 2px; margin-bottom: 16px; font-size: 13px; border: 1px solid #ef9a9a; }

@media (max-width: 768px) {
    .checkout-container { grid-template-columns: 1fr; }
    .form-row { grid-template-columns: 1fr; }
}
</style>

<div class="checkout-container">
    <form method="POST" action="" class="checkout-form" novalidate>
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="place_order" value="1">

        <h2>Checkout</h2>
        <p class="checkout-subtitle">Review your order and complete payment</p>

        <?php if (!empty($errors)): ?>
            <div class="error-alert">
                <ul style="margin:0;padding-left:16px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo escapeOutput($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <div class="checkout-section">
            <h3><span class="step">1</span> Delivery Address</h3>
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" 
                       value="<?php echo escapeOutput($_POST['full_name'] ?? $customer['full_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" 
                       value="<?php echo escapeOutput($_POST['phone'] ?? $customer['phone'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <textarea id="address" name="address" required><?php echo escapeOutput($_POST['address'] ?? $customer['address'] ?? ''); ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" 
                           value="<?php echo escapeOutput($_POST['city'] ?? $customer['city'] ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="state">State</label>
                    <input type="text" id="state" name="state" 
                           value="<?php echo escapeOutput($_POST['state'] ?? $customer['state'] ?? ''); ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="postal_code">Postal Code</label>
                <input type="text" id="postal_code" name="postal_code" 
                       value="<?php echo escapeOutput($_POST['postal_code'] ?? $customer['postal_code'] ?? ''); ?>" required>
            </div>
        </div>

        <div class="checkout-section">
            <h3><span class="step">2</span> Payment Method</h3>
            <div class="payment-options">
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="cod" <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'cod') || !isset($_POST['payment_method']) ? 'checked' : ''; ?>>
                    <div>
                        <div class="payment-option-label">Cash on Delivery</div>
                        <div class="payment-option-desc">Pay when you receive the order</div>
                    </div>
                </label>
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="online" <?php echo isset($_POST['payment_method']) && $_POST['payment_method'] === 'online' ? 'checked' : ''; ?>>
                    <div>
                        <div class="payment-option-label">Online Payment</div>
                        <div class="payment-option-desc">Credit/Debit Card, UPI, Net Banking</div>
                    </div>
                </label>
            </div>

            <!-- Online Payment Details -->
            <div id="online_payment_details" class="payment-details-box" style="display: <?php echo isset($_POST['payment_method']) && $_POST['payment_method'] === 'online' ? 'block' : 'none'; ?>; margin-top:16px; padding:20px; border:1px dashed #2874f0; background:#f5faff; border-radius:2px;">
                <h4 style="font-size:13px; font-weight:600; color:#212121; margin:0 0 12px 0;">Select Payment Option</h4>
                
                <div style="display:flex; gap:16px; margin-bottom:16px;">
                    <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:13px;">
                        <input type="radio" name="online_type" value="card" <?php echo (isset($_POST['online_type']) && $_POST['online_type'] === 'card') || !isset($_POST['online_type']) ? 'checked' : ''; ?>> Card Details
                    </label>
                    <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:13px;">
                        <input type="radio" name="online_type" value="upi" <?php echo isset($_POST['online_type']) && $_POST['online_type'] === 'upi' ? 'checked' : ''; ?>> UPI Option
                    </label>
                </div>
                
                <!-- Card Details Form -->
                <div id="card_form_group" style="display: <?php echo (isset($_POST['online_type']) && $_POST['online_type'] === 'card') || !isset($_POST['online_type']) ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label for="card_name">Name on Card</label>
                        <input type="text" id="card_name" name="card_name" value="<?php echo escapeOutput($_POST['card_name'] ?? ''); ?>" placeholder="John Doe">
                    </div>
                    <div class="form-group">
                        <label for="card_number">Card Number</label>
                        <input type="text" id="card_number" name="card_number" value="<?php echo escapeOutput($_POST['card_number'] ?? ''); ?>" placeholder="1234 5678 1234 5678" maxlength="19">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="card_expiry">Expiry Date</label>
                            <input type="text" id="card_expiry" name="card_expiry" value="<?php echo escapeOutput($_POST['card_expiry'] ?? ''); ?>" placeholder="MM/YY" maxlength="5">
                        </div>
                        <div class="form-group">
                            <label for="card_cvv">CVV</label>
                            <input type="password" id="card_cvv" name="card_cvv" value="<?php echo escapeOutput($_POST['card_cvv'] ?? ''); ?>" placeholder="123" maxlength="3">
                        </div>
                    </div>
                </div>
                
                <!-- UPI Form -->
                <div id="upi_form_group" style="display: <?php echo isset($_POST['online_type']) && $_POST['online_type'] === 'upi' ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label for="upi_id">UPI ID (VPA)</label>
                        <input type="text" id="upi_id" name="upi_id" value="<?php echo escapeOutput($_POST['upi_id'] ?? ''); ?>" placeholder="example@upi">
                    </div>
                </div>
            </div>
        </div>

        <div class="checkout-section">
            <button type="submit" class="btn-place-order">Place Order</button>
        </div>
    </form>

    <div class="checkout-sidebar">
        <h3>Order Summary</h3>

        <?php foreach ($cartItems as $item): ?>
            <div class="checkout-item">
                <div class="checkout-item-img">
                    <img src="<?php echo getBaseUrl(); ?>/uploads/<?php echo escapeOutput($item['image'] ?? 'placeholder.png'); ?>" 
                         alt="<?php echo escapeOutput($item['name']); ?>"
                         onerror="this.src='<?php echo getBaseUrl(); ?>/uploads/placeholder.png'">
                </div>
                <div class="checkout-item-info">
                    <div class="checkout-item-name"><?php echo escapeOutput($item['name']); ?></div>
                    <div class="checkout-item-meta">Qty: <?php echo (int)$item['quantity']; ?></div>
                </div>
                <div class="checkout-item-price">&#8377;<?php echo number_format($item['price'] * $item['quantity']); ?></div>
            </div>
        <?php endforeach; ?>

        <div class="price-line">
            <span>Subtotal</span>
            <span>&#8377;<?php echo number_format($subtotal); ?></span>
        </div>
        <div class="price-line">
            <span>Delivery</span>
            <?php if ($deliveryCharge > 0): ?>
                <span>&#8377;<?php echo number_format($deliveryCharge); ?></span>
            <?php else: ?>
                <span class="free">Free</span>
            <?php endif; ?>
        </div>
        <div class="price-line total">
            <span>Total</span>
            <span>&#8377;<?php echo number_format($totalAmount); ?></span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Payment method toggle logic
    document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var box = document.getElementById('online_payment_details');
            if (this.value === 'online') {
                box.style.display = 'block';
            } else {
                box.style.display = 'none';
            }
        });
    });

    // Online payment sub-type toggle logic
    document.querySelectorAll('input[name="online_type"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            var cardForm = document.getElementById('card_form_group');
            var upiForm = document.getElementById('upi_form_group');
            if (this.value === 'card') {
                cardForm.style.display = 'block';
                upiForm.style.display = 'none';
            } else {
                cardForm.style.display = 'none';
                upiForm.style.display = 'block';
            }
        });
    });

    // Format card number with spaces (1234 5678 1234 5678)
    var cardNum = document.getElementById('card_number');
    if (cardNum) {
        cardNum.addEventListener('input', function(e) {
            var target = e.target;
            var position = target.selectionEnd;
            var length = target.value.length;
            var value = target.value.replace(/\D/g, '');
            var formatted = '';
            for (var i = 0; i < value.length && i < 16; i++) {
                if (i > 0 && i % 4 === 0) formatted += ' ';
                formatted += value[i];
            }
            target.value = formatted;
            if (position !== length) {
                target.selectionStart = target.selectionEnd = position;
            }
        });
    }

    // Format expiry date with slash (MM/YY)
    var cardExp = document.getElementById('card_expiry');
    if (cardExp) {
        cardExp.addEventListener('input', function(e) {
            var value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                e.target.value = value.substring(0, 2) + '/' + value.substring(2, 4);
            } else {
                e.target.value = value;
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/customer_footer.php'; ?>
