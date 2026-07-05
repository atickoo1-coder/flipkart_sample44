<?php
$pageTitle = 'Shopping Cart';
require_once __DIR__ . '/../includes/customer_auth.php';
requireCustomerAuth();

$pdo = getConnection();

$stmt = $pdo->prepare(
    "SELECT c.id as cart_id, c.quantity, c.product_id, 
            p.name, p.slug, p.price, p.original_price, p.discount, p.image, p.stock_quantity, p.brand
     FROM cart c 
     JOIN products p ON c.product_id = p.id 
     WHERE c.customer_id = ?
     ORDER BY c.created_at DESC"
);
$stmt->execute([$_SESSION['customer_id']]);
$cartItems = $stmt->fetchAll();

// Calculate summary
$subtotal = 0;
$deliveryCharge = 40;
$freeDeliveryThreshold = 499;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$deliveryCharge = ($subtotal >= $freeDeliveryThreshold || empty($cartItems)) ? 0 : $deliveryCharge;
$total = $subtotal + $deliveryCharge;
?>
<?php require_once __DIR__ . '/../includes/customer_header.php'; ?>

<style>
.cart-container { max-width: 1024px; margin: 20px auto; padding: 0 16px; display: grid; grid-template-columns: 1fr 320px; gap: 20px; align-items: start; }
.cart-main { background: #fff; border-radius: 2px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden; }
.cart-header { padding: 16px 24px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
.cart-header h2 { font-size: 18px; font-weight: 600; color: #212121; margin: 0; }
.cart-header span { font-size: 13px; color: #878787; }
.cart-empty { text-align: center; padding: 60px 20px; }
.cart-empty svg { width: 80px; height: 80px; color: #e0e0e0; margin-bottom: 16px; }
.cart-empty h3 { font-size: 18px; color: #212121; margin: 0 0 8px 0; }
.cart-empty p { font-size: 14px; color: #878787; margin: 0 0 20px 0; }
.cart-empty a { display: inline-block; padding: 10px 24px; background: #2874f0; color: #fff; text-decoration: none; border-radius: 2px; font-weight: 500; font-size: 14px; }
.cart-item { display: flex; align-items: center; gap: 16px; padding: 16px 24px; border-bottom: 1px solid #f0f0f0; }
.cart-item:last-child { border-bottom: none; }
.cart-item-img { width: 80px; height: 80px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; border: 1px solid #f0f0f0; border-radius: 2px; overflow: hidden; }
.cart-item-img img { max-width: 100%; max-height: 100%; object-fit: contain; }
.cart-item-info { flex: 1; min-width: 0; }
.cart-item-name { font-size: 15px; font-weight: 500; color: #212121; text-decoration: none; display: block; margin-bottom: 2px; }
.cart-item-name:hover { color: #2874f0; }
.cart-item-brand { font-size: 12px; color: #878787; margin-bottom: 4px; }
.cart-item-price { font-size: 16px; font-weight: 600; color: #212121; }
.cart-item-original { font-size: 13px; color: #878787; text-decoration: line-through; margin-left: 6px; }
.cart-item-discount { font-size: 12px; color: #388e3c; font-weight: 500; margin-left: 6px; }
.cart-item-stock { font-size: 12px; color: #388e3c; margin-top: 4px; }
.cart-item-actions { display: flex; align-items: center; gap: 16px; margin-top: 8px; }
.cart-qty-selector { display: flex; align-items: center; border: 1px solid #e0e0e0; border-radius: 2px; }
.cart-qty-selector button { width: 28px; height: 32px; border: none; background: #fafafa; font-size: 16px; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.cart-qty-selector button:hover { background: #f0f0f0; }
.cart-qty-selector input { width: 40px; height: 32px; border: none; border-left: 1px solid #e0e0e0; border-right: 1px solid #e0e0e0; text-align: center; font-size: 13px; outline: none; }
.cart-item-remove { font-size: 12px; color: #878787; cursor: pointer; background: none; border: none; text-decoration: none; }
.cart-item-remove:hover { color: #c62828; }
.cart-item-total { font-size: 15px; font-weight: 600; color: #212121; text-align: right; flex-shrink: 0; }
.cart-save-later { margin-top: 8px; font-size: 12px; color: #2874f0; cursor: pointer; background: none; border: none; }

/* Sidebar */
.cart-sidebar { background: #fff; border-radius: 2px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); padding: 20px; position: sticky; top: 16px; }
.cart-sidebar h3 { font-size: 16px; font-weight: 600; color: #212121; margin: 0 0 16px 0; padding-bottom: 12px; border-bottom: 1px solid #f0f0f0; }
.price-line { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; font-size: 14px; color: #555; }
.price-line.total { font-size: 18px; font-weight: 600; color: #212121; border-top: 1px solid #f0f0f0; padding-top: 12px; margin-top: 8px; }
.price-line .free { color: #388e3c; font-weight: 500; }
.cart-sidebar .btn-checkout { width: 100%; padding: 12px; background: #fb641b; color: #fff; border: none; border-radius: 2px; font-size: 15px; font-weight: 600; cursor: pointer; text-transform: uppercase; margin-top: 16px; text-align: center; display: block; text-decoration: none; }
.cart-sidebar .btn-checkout:hover { background: #e85a16; }
.cart-sidebar .btn-checkout:disabled { background: #ccc; cursor: not-allowed; }

@media (max-width: 768px) {
    .cart-container { grid-template-columns: 1fr; }
    .cart-item { flex-wrap: wrap; }
    .cart-item-total { width: 100%; text-align: left; }
}
</style>

<div class="cart-container">
    <div class="cart-main">
        <div class="cart-header">
            <h2>My Cart (<?php echo count($cartItems); ?>)</h2>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="cart-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <h3>Your cart is empty!</h3>
                <p>Looks like you haven't added anything yet. Start shopping!</p>
                <a href="<?php echo getBaseUrl(); ?>/products/products.php">Shop Now</a>
            </div>
        <?php else: ?>
            <form method="POST" action="<?php echo getBaseUrl(); ?>/cart/update_cart.php" id="cartForm">
                <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item">
                        <div class="cart-item-img">
                            <img src="<?php echo getBaseUrl(); ?>/uploads/<?php echo escapeOutput($item['image'] ?? 'placeholder.png'); ?>" 
                                 alt="<?php echo escapeOutput($item['name']); ?>"
                                 onerror="this.src='<?php echo getBaseUrl(); ?>/uploads/placeholder.png'">
                        </div>
                        <div class="cart-item-info">
                            <a href="<?php echo getBaseUrl(); ?>/products/product.php?slug=<?php echo escapeOutput($item['slug']); ?>" class="cart-item-name">
                                <?php echo escapeOutput($item['name']); ?>
                            </a>
                            <?php if ($item['brand']): ?>
                                <div class="cart-item-brand"><?php echo escapeOutput($item['brand']); ?></div>
                            <?php endif; ?>
                            <div>
                                <span class="cart-item-price">&#8377;<?php echo number_format($item['price']); ?></span>
                                <?php if ($item['original_price'] && $item['original_price'] > $item['price']): ?>
                                    <span class="cart-item-original">&#8377;<?php echo number_format($item['original_price']); ?></span>
                                    <span class="cart-item-discount"><?php echo (int)$item['discount']; ?>% off</span>
                                <?php endif; ?>
                            </div>
                            <div class="cart-item-stock">In Stock</div>

                            <div class="cart-item-actions">
                                <div class="cart-qty-selector">
                                    <button type="button" onclick="updateQty(this, -1)">-</button>
                                    <input type="number" name="qty[<?php echo (int)$item['cart_id']; ?>]" 
                                           value="<?php echo (int)$item['quantity']; ?>" 
                                           min="1" max="<?php echo (int)$item['stock_quantity']; ?>"
                                           data-cart-id="<?php echo (int)$item['cart_id']; ?>"
                                           data-price="<?php echo $item['price']; ?>" 
                                           data-max="<?php echo (int)$item['stock_quantity']; ?>">
                                    <button type="button" onclick="updateQty(this, 1)">+</button>
                                </div>
                                <a href="<?php echo getBaseUrl(); ?>/cart/remove_item.php?id=<?php echo (int)$item['cart_id']; ?>" class="cart-item-remove" onclick="return confirm('Remove this item from cart?')">Remove</a>
                            </div>
                        </div>
                        <div class="cart-item-total" id="itemTotal_<?php echo (int)$item['cart_id']; ?>">
                            &#8377;<?php echo number_format($item['price'] * $item['quantity']); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </form>
        <?php endif; ?>
    </div>

    <?php if (!empty($cartItems)): ?>
        <div class="cart-sidebar">
            <h3>Price Details</h3>
            <div class="price-line">
                <span>Price (<?php echo count($cartItems); ?> items)</span>
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
            <?php if ($subtotal < $freeDeliveryThreshold && !empty($cartItems)): ?>
                <div style="font-size:12px;color:#388e3c;margin-bottom:8px;">
                    Add &#8377;<?php echo number_format($freeDeliveryThreshold - $subtotal); ?> more for FREE delivery
                </div>
            <?php endif; ?>
            <div class="price-line total">
                <span>Total Amount</span>
                <span>&#8377;<?php echo number_format($total); ?></span>
            </div>
            <a href="<?php echo getBaseUrl(); ?>/cart/checkout.php" class="btn-checkout">Place Order</a>
        </div>
    <?php endif; ?>
</div>

<script>
function updateQty(btn, delta) {
    var input = btn.parentElement.querySelector('input');
    var val = parseInt(input.value) || 1;
    var min = parseInt(input.min) || 1;
    var max = parseInt(input.max) || 99;
    val = Math.max(min, Math.min(max, val + delta));
    input.value = val;

    var cartId = input.getAttribute('data-cart-id');
    var price = parseFloat(input.getAttribute('data-price')) || 0;
    var totalEl = document.getElementById('itemTotal_' + cartId);
    if (totalEl) {
        totalEl.textContent = '\u20B9' + (price * val).toLocaleString('en-IN', {minimumFractionDigits:0, maximumFractionDigits:0});
    }
}
</script>

<?php require_once __DIR__ . '/../includes/customer_footer.php'; ?>
