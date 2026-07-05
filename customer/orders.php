<?php
$pageTitle = 'My Orders';
require_once __DIR__ . '/../includes/customer_auth.php';
requireCustomerAuth();

$pdo = getConnection();
$stmt = $pdo->prepare(
    "SELECT o.*, 
            (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count 
     FROM orders o 
     WHERE o.customer_id = ? 
     ORDER BY o.created_at DESC"
);
$stmt->execute([$_SESSION['customer_id']]);
$orders = $stmt->fetchAll();
?>
<?php require_once __DIR__ . '/../includes/customer_header.php'; ?>

<style>
.orders-container { max-width: 1024px; margin: 24px auto; padding: 0 16px; }
.orders-main { background: #fff; border-radius: 2px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); padding: 24px 32px; }
.orders-main h2 { font-size: 20px; font-weight: 600; color: #212121; margin: 0 0 4px 0; }
.orders-subtitle { font-size: 13px; color: #878787; margin-bottom: 24px; }
.order-card { border: 1px solid #f0f0f0; border-radius: 2px; margin-bottom: 16px; padding: 16px 20px; transition: box-shadow 0.2s; }
.order-card:hover { box-shadow: 0 2px 6px rgba(0,0,0,0.06); }
.order-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px; }
.order-number { font-size: 14px; font-weight: 600; color: #212121; }
.order-date { font-size: 13px; color: #878787; }
.order-total { font-size: 15px; font-weight: 600; color: #212121; }
.order-items { margin-top: 12px; }
.order-item { display: flex; align-items: center; gap: 12px; padding: 8px 0; border-top: 1px solid #f5f5f5; }
.order-item:first-child { border-top: none; }
.order-item-img { width: 50px; height: 50px; object-fit: contain; border: 1px solid #f0f0f0; border-radius: 2px; flex-shrink: 0; }
.order-item-info { flex: 1; min-width: 0; }
.order-item-name { font-size: 14px; color: #212121; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.order-item-meta { font-size: 12px; color: #878787; margin-top: 2px; }
.order-item-price { font-size: 14px; font-weight: 500; color: #212121; text-align: right; flex-shrink: 0; }
.status-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: 500; text-transform: capitalize; }
.status-pending { background: #fff3e0; color: #e65100; }
.status-confirmed { background: #e3f2fd; color: #1565c0; }
.status-shipped { background: #e8f5e9; color: #2e7d32; }
.status-delivered { background: #e8f5e9; color: #1b5e20; }
.status-cancelled { background: #ffebee; color: #c62828; }
.empty-orders { text-align: center; padding: 60px 20px; }
.empty-orders svg { width: 80px; height: 80px; color: #e0e0e0; margin-bottom: 16px; }
.empty-orders h3 { font-size: 18px; color: #212121; margin: 0 0 8px 0; }
.empty-orders p { font-size: 14px; color: #878787; margin: 0 0 20px 0; }
.empty-orders a { display: inline-block; padding: 10px 24px; background: #2874f0; color: #fff; text-decoration: none; border-radius: 2px; font-weight: 500; font-size: 14px; }
.empty-orders a:hover { background: #1c5dc9; }

@media (max-width: 600px) {
    .orders-main { padding: 16px; }
}
</style>

<div class="orders-container">
    <div class="orders-main">
        <h2>My Orders</h2>
        <p class="orders-subtitle">View and track all your orders</p>

        <?php if (empty($orders)): ?>
            <div class="empty-orders">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                <h3>No orders yet</h3>
                <p>Looks like you haven't placed any orders yet. Start shopping!</p>
                <a href="<?php echo getBaseUrl(); ?>/products/products.php">Start Shopping</a>
            </div>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-number">Order #<?php echo escapeOutput($order['order_number']); ?></div>
                            <div class="order-date">Placed on <?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></div>
                        </div>
                        <div style="display:flex;align-items:center;gap:16px;">
                            <span class="status-badge status-<?php echo escapeOutput($order['order_status']); ?>">
                                <?php echo escapeOutput($order['order_status']); ?>
                            </span>
                            <span class="order-total">&#8377;<?php echo number_format($order['total_amount'], 2); ?></span>
                        </div>
                    </div>

                    <?php
                    $stmt = $pdo->prepare(
                        "SELECT oi.*, p.slug 
                         FROM order_items oi 
                         LEFT JOIN products p ON oi.product_id = p.id 
                         WHERE oi.order_id = ?"
                    );
                    $stmt->execute([$order['id']]);
                    $items = $stmt->fetchAll();
                    ?>

                    <div class="order-items">
                        <?php foreach ($items as $item): ?>
                            <div class="order-item">
                                <img class="order-item-img" 
                                     src="<?php echo getBaseUrl(); ?>/uploads/<?php echo escapeOutput($item['product_image'] ?? 'placeholder.png'); ?>" 
                                     alt="<?php echo escapeOutput($item['product_name']); ?>"
                                     onerror="this.src='<?php echo getBaseUrl(); ?>/uploads/placeholder.png'">
                                <div class="order-item-info">
                                    <div class="order-item-name"><?php echo escapeOutput($item['product_name']); ?></div>
                                    <div class="order-item-meta">Qty: <?php echo (int)$item['quantity']; ?></div>
                                </div>
                                <div class="order-item-price">&#8377;<?php echo number_format($item['total'], 2); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/customer_footer.php'; ?>
