<?php
$pageTitle = 'My Wishlist';
require_once __DIR__ . '/includes/customer_auth.php';
requireCustomerAuth();

$pdo = getConnection();
$customerId = $_SESSION['customer_id'];

$stmt = $pdo->prepare(
    "SELECT p.id, p.name, p.slug, p.price, p.original_price, p.discount, p.brand, 
            p.image, p.stock_quantity, p.rating, p.reviews, p.description,
            c.name as category_name,
            w.id as wishlist_id, w.created_at as wishlist_added
     FROM wishlist w
     JOIN products p ON w.product_id = p.id
     LEFT JOIN categories c ON p.category_id = c.id
     WHERE w.customer_id = ? AND p.status = 1
     ORDER BY w.created_at DESC"
);
$stmt->execute([$customerId]);
$wishlistItems = $stmt->fetchAll();

// Clean up: remove wishlist entries for products that no longer exist or are inactive
$stmt = $pdo->prepare(
    "DELETE w FROM wishlist w
     LEFT JOIN products p ON w.product_id = p.id
     WHERE w.customer_id = ? AND (p.id IS NULL OR p.status = 0)"
);
$stmt->execute([$customerId]);
?>
<?php require_once __DIR__ . '/includes/customer_header.php'; ?>

<style>
.wishlist-page { max-width: 1248px; margin: 20px auto; padding: 0 16px; }
.wishlist-page h1 { font-size: 24px; font-weight: 600; color: #212121; margin: 0 0 4px; }
.wishlist-page .subtitle { font-size: 14px; color: #878787; margin-bottom: 24px; }
.wishlist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; }
.wishlist-card { background: #fff; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); overflow: hidden; transition: box-shadow 0.2s, transform 0.2s; position: relative; }
.wishlist-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.1); transform: translateY(-2px); }
.wishlist-card-img { height: 200px; display: flex; align-items: center; justify-content: center; padding: 16px; background: #fafafa; position: relative; }
.wishlist-card-img img { max-width: 100%; max-height: 100%; object-fit: contain; transition: transform 0.3s; }
.wishlist-card:hover .wishlist-card-img img { transform: scale(1.05); }
.wishlist-card-body { padding: 14px 16px 16px; }
.wishlist-card-title { font-size: 15px; font-weight: 500; color: #212121; margin: 0 0 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.wishlist-card-brand { font-size: 13px; color: #878787; margin-bottom: 6px; }
.wishlist-card-category { font-size: 12px; color: #2874f0; margin-bottom: 8px; }
.wishlist-card-pricing { display: flex; align-items: center; gap: 8px; margin-bottom: 6px; flex-wrap: wrap; }
.wishlist-card-price { font-size: 17px; font-weight: 600; color: #212121; }
.wishlist-card-original { font-size: 14px; color: #878787; text-decoration: line-through; }
.wishlist-card-discount { font-size: 13px; color: #388e3c; font-weight: 500; }
.wishlist-card-rating { display: inline-flex; align-items: center; gap: 4px; background: #388e3c; color: #fff; padding: 2px 6px; border-radius: 2px; font-size: 11px; font-weight: 500; }
.wishlist-card-reviews { font-size: 12px; color: #878787; margin-left: 4px; }
.wishlist-card-stock { font-size: 13px; margin-bottom: 8px; }
.wishlist-card-stock.in-stock { color: #388e3c; }
.wishlist-card-stock.out-of-stock { color: #c62828; }
.wishlist-card-seller { font-size: 12px; color: #878787; margin-bottom: 4px; }
.wishlist-card-delivery { font-size: 12px; color: #388e3c; margin-bottom: 12px; display: flex; align-items: center; gap: 4px; }
.wishlist-card-delivery svg { width: 14px; height: 14px; }
.wishlist-card-actions { display: flex; gap: 8px; margin-top: 12px; }
.wishlist-card-actions .btn-view { flex: 1; padding: 8px 12px; background: #fff; color: #2874f0; border: 1px solid #2874f0; border-radius: 2px; font-size: 13px; font-weight: 500; cursor: pointer; text-align: center; text-decoration: none; transition: all 0.15s; }
.wishlist-card-actions .btn-view:hover { background: #2874f0; color: #fff; }
.wishlist-card-actions .btn-cart { flex: 1; padding: 8px 12px; background: #ff9f00; color: #fff; border: none; border-radius: 2px; font-size: 13px; font-weight: 500; cursor: pointer; text-align: center; transition: background 0.15s; }
.wishlist-card-actions .btn-cart:hover { background: #f59100; }
.wishlist-card-actions .btn-cart:disabled { background: #e0e0e0; cursor: not-allowed; color: #878787; }
.wishlist-card-actions .btn-remove { padding: 8px; background: none; border: 1px solid #e0e0e0; border-radius: 2px; cursor: pointer; color: #878787; display: flex; align-items: center; justify-content: center; transition: all 0.15s; }
.wishlist-card-actions .btn-remove:hover { border-color: #c62828; color: #c62828; }
.wishlist-card-actions .btn-remove svg { width: 16px; height: 16px; }

/* Empty state */
.empty-wishlist { text-align: center; padding: 80px 20px; }
.empty-wishlist svg { width: 120px; height: 120px; color: #e0e0e0; margin-bottom: 24px; }
.empty-wishlist h2 { font-size: 22px; font-weight: 600; color: #212121; margin: 0 0 8px; }
.empty-wishlist p { font-size: 15px; color: #878787; margin: 0 0 24px; }
.empty-wishlist .btn-continue { display: inline-block; padding: 12px 32px; background: #2874f0; color: #fff; border: none; border-radius: 2px; font-size: 15px; font-weight: 500; text-decoration: none; text-transform: uppercase; transition: background 0.15s; }
.empty-wishlist .btn-continue:hover { background: #1c5dc9; }

/* Loading spinner */
.loading-spinner { text-align: center; padding: 60px 20px; }
.loading-spinner .spinner { width: 40px; height: 40px; border: 3px solid #e0e0e0; border-top-color: #2874f0; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 16px; }
@keyframes spin { to { transform: rotate(360deg); } }
.loading-spinner p { font-size: 14px; color: #878787; }

/* Toast notification */
.toast-container { position: fixed; top: 80px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; }
.toast { padding: 12px 20px; border-radius: 4px; font-size: 14px; font-weight: 500; box-shadow: 0 4px 12px rgba(0,0,0,0.15); animation: slideIn 0.3s ease, fadeOut 0.3s ease 2.7s forwards; display: flex; align-items: center; gap: 8px; }
.toast-success { background: #388e3c; color: #fff; }
.toast-error { background: #c62828; color: #fff; }
.toast-info { background: #2874f0; color: #fff; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
@keyframes fadeOut { to { opacity: 0; transform: translateX(50px); } }

@media (max-width: 768px) {
    .wishlist-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); }
}
@media (max-width: 480px) {
    .wishlist-grid { grid-template-columns: 1fr; }
    .wishlist-card-actions { flex-direction: column; }
}
</style>

<div class="wishlist-page">
    <h1>My Wishlist</h1>
    <p class="subtitle">Products you love</p>

    <div id="wishlistContainer">
        <?php if (empty($wishlistItems)): ?>
            <div class="empty-wishlist">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                <h2>Your Wishlist is Empty</h2>
                <p>Save products you love to buy them later.</p>
                <a href="<?php echo getBaseUrl(); ?>/products/products.php" class="btn-continue">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="wishlist-grid">
                <?php foreach ($wishlistItems as $item):
                    $inStock = $item['stock_quantity'] > 0;
                ?>
                    <div class="wishlist-card" data-product-id="<?php echo (int)$item['id']; ?>">
                        <div class="wishlist-card-img">
                            <img src="<?php echo getBaseUrl(); ?>/uploads/<?php echo escapeOutput($item['image'] ?? 'placeholder.png'); ?>"
                                 alt="<?php echo escapeOutput($item['name']); ?>"
                                 loading="lazy"
                                 onerror="this.src='<?php echo getBaseUrl(); ?>/uploads/placeholder.png'">
                        </div>
                        <div class="wishlist-card-body">
                            <div class="wishlist-card-title"><?php echo escapeOutput($item['name']); ?></div>
                            <?php if ($item['brand']): ?>
                                <div class="wishlist-card-brand"><?php echo escapeOutput($item['brand']); ?></div>
                            <?php endif; ?>
                            <div class="wishlist-card-category"><?php echo escapeOutput($item['category_name'] ?? ''); ?></div>

                            <div class="wishlist-card-pricing">
                                <span class="wishlist-card-price">&#8377;<?php echo number_format($item['price']); ?></span>
                                <?php if ($item['original_price'] && $item['original_price'] > $item['price']): ?>
                                    <span class="wishlist-card-original">&#8377;<?php echo number_format($item['original_price']); ?></span>
                                    <span class="wishlist-card-discount"><?php echo (int)$item['discount']; ?>% off</span>
                                <?php endif; ?>
                            </div>

                            <?php if ($item['rating'] > 0): ?>
                                <div style="margin-bottom:6px;">
                                    <span class="wishlist-card-rating"><?php echo number_format($item['rating'], 1); ?>&#9733;</span>
                                    <span class="wishlist-card-reviews">(<?php echo (int)$item['reviews']; ?>)</span>
                                </div>
                            <?php endif; ?>

                            <div class="wishlist-card-stock <?php echo $inStock ? 'in-stock' : 'out-of-stock'; ?>">
                                <?php if ($inStock): ?>
                                    &#10003; In Stock
                                <?php else: ?>
                                    &#10007; Out of Stock
                                <?php endif; ?>
                            </div>

                            <div class="wishlist-card-seller">Seller: QuickKart Retail</div>

                            <div class="wishlist-card-delivery">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                                Free Delivery
                            </div>

                            <div class="wishlist-card-actions">
                                <a href="<?php echo getBaseUrl(); ?>/products/product.php?slug=<?php echo escapeOutput($item['slug']); ?>" class="btn-view">View Product</a>
                                <?php if ($inStock): ?>
                                    <button class="btn-cart" onclick="addToCart(<?php echo (int)$item['id']; ?>, this)">Add to Cart</button>
                                <?php else: ?>
                                    <button class="btn-cart" disabled>Out of Stock</button>
                                <?php endif; ?>
                                <button class="btn-remove" onclick="removeFromWishlist(<?php echo (int)$item['id']; ?>, this)" title="Remove from wishlist">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="toast-container" id="toastContainer"></div>

<script>
const baseUrl = '<?php echo getBaseUrl(); ?>';

function showToast(message, type) {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = 'toast toast-' + type;
    toast.textContent = message;
    container.appendChild(toast);
    setTimeout(function() { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 3000);
}

function addToCart(productId, btn) {
    btn.disabled = true;
    btn.textContent = 'Adding...';

    fetch(baseUrl + '/cart-add.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId + '&quantity=1&csrf_token=' + encodeURIComponent('<?php echo generateCSRFToken(); ?>')
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showToast('Added to cart!', 'success');
        } else {
            showToast(data.message || 'Failed to add to cart', 'error');
        }
    })
    .catch(function() {
        showToast('Something went wrong', 'error');
    })
    .finally(function() {
        btn.disabled = false;
        btn.textContent = 'Add to Cart';
    });
}

function removeFromWishlist(productId, btn) {
    var card = btn.closest('.wishlist-card');
    card.style.opacity = '0.5';

    fetch(baseUrl + '/wishlist-remove.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'product_id=' + productId + '&csrf_token=' + encodeURIComponent('<?php echo generateCSRFToken(); ?>')
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            showToast('Product removed from Wishlist.', 'info');
            card.remove();
            updateWishlistCount(data.count);
            var grid = document.querySelector('.wishlist-grid');
            if (!grid || grid.children.length === 0) {
                showEmptyState();
            }
        } else {
            showToast(data.message || 'Failed to remove', 'error');
            card.style.opacity = '1';
        }
    })
    .catch(function() {
        showToast('Something went wrong', 'error');
        card.style.opacity = '1';
    });
}

function updateWishlistCount(count) {
    var badge = document.getElementById('wishlistBadge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count;
            badge.style.display = 'flex';
        } else {
            badge.style.display = 'none';
        }
    }
}

function showEmptyState() {
    var container = document.getElementById('wishlistContainer');
    container.innerHTML = `
        <div class="empty-wishlist">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
            <h2>Your Wishlist is Empty</h2>
            <p>Save products you love to buy them later.</p>
            <a href="${baseUrl}/products/products.php" class="btn-continue">Continue Shopping</a>
        </div>
    `;
}
</script>

<?php require_once __DIR__ . '/includes/customer_footer.php'; ?>
