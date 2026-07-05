<?php
$pageTitle = 'Product Details';
require_once __DIR__ . '/../includes/customer_auth.php';

$slug = sanitizeInput($_GET['slug'] ?? '');
if (empty($slug)) {
    header('Location: ' . getBaseUrl() . '/products/products.php');
    exit();
}

$pdo = getConnection();
$stmt = $pdo->prepare(
    "SELECT p.*, c.name as category_name, c.slug as category_slug 
     FROM products p 
     LEFT JOIN categories c ON p.category_id = c.id 
     WHERE p.slug = ? AND p.status = 1"
);
$stmt->execute([$slug]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: ' . getBaseUrl() . '/products/products.php');
    exit();
}

$pageTitle = $product['name'];

// Get related products (same category, excluding current)
$stmt = $pdo->prepare(
    "SELECT id, name, slug, price, original_price, discount, image, rating, reviews, brand 
     FROM products WHERE category_id = ? AND id != ? AND status = 1 
     ORDER BY RAND() LIMIT 4"
);
$stmt->execute([$product['category_id'], $product['id']]);
$relatedProducts = $stmt->fetchAll();

// Check which related products are wishlisted
$relWishlistedIds = [];
if (isCustomerLoggedIn() && !empty($relatedProducts)) {
    $relIds = array_map(function($p) { return (int)$p['id']; }, $relatedProducts);
    $placeholders = implode(',', array_fill(0, count($relIds), '?'));
    $stmtW2 = $pdo->prepare("SELECT product_id FROM wishlist WHERE customer_id = ? AND product_id IN ($placeholders)");
    $stmtW2->execute(array_merge([$_SESSION['customer_id']], $relIds));
    while ($row = $stmtW2->fetch()) {
        $relWishlistedIds[$row['product_id']] = true;
    }
}
$isRelWishlisted = function($id) use ($relWishlistedIds) {
    return isset($relWishlistedIds[$id]) ? '1' : '0';
};

// Check if product is in wishlist
$inWishlist = false;
if (isCustomerLoggedIn()) {
    $stmtW = $pdo->prepare("SELECT id FROM wishlist WHERE customer_id = ? AND product_id = ?");
    $stmtW->execute([$_SESSION['customer_id'], $product['id']]);
    $inWishlist = (bool)$stmtW->fetch();
}

// Handle add to cart
$cartMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    if (!isCustomerLoggedIn()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: ' . getBaseUrl() . '/customer/login.php');
        exit();
    }

    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    $productId = (int)$product['id'];

    try {
        if ($quantity > $product['stock_quantity']) {
            $cartMessage = ['type' => 'error', 'text' => 'Requested quantity exceeds available stock.'];
        } else {
            $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE customer_id = ? AND product_id = ?");
            $stmt->execute([$_SESSION['customer_id'], $productId]);
            $existing = $stmt->fetch();

            if ($existing) {
                $newQty = min($existing['quantity'] + $quantity, $product['stock_quantity']);
                $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
                $stmt->execute([$newQty, $existing['id']]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO cart (customer_id, product_id, quantity) VALUES (?, ?, ?)");
                $stmt->execute([$_SESSION['customer_id'], $productId, $quantity]);
            }
            $cartMessage = ['type' => 'success', 'text' => 'Item added to cart!'];
        }
    } catch (PDOException $e) {
        $cartMessage = ['type' => 'error', 'text' => 'Database error: ' . $e->getMessage()];
    }
}
?>
<?php require_once __DIR__ . '/../includes/customer_header.php'; ?>

<style>
.product-detail-container { max-width: 1080px; margin: 20px auto; padding: 0 16px; }
.breadcrumb { font-size: 13px; color: #878787; margin-bottom: 16px; }
.breadcrumb a { color: #2874f0; text-decoration: none; }
.breadcrumb a:hover { text-decoration: underline; }
.breadcrumb span { color: #878787; }
.product-detail-card { background: #fff; border-radius: 2px; box-shadow: 0 2px 8px rgba(0,0,0,0.06); display: grid; grid-template-columns: 380px 1fr; gap: 0; overflow: hidden; }
.product-image-section { padding: 24px; display: flex; flex-direction: column; align-items: center; border-right: 1px solid #f0f0f0; }
.product-main-image { width: 100%; max-height: 360px; object-fit: contain; margin-bottom: 16px; }
.product-thumbnails { display: flex; gap: 8px; flex-wrap: wrap; justify-content: center; }
.product-thumbnails img { width: 60px; height: 60px; object-fit: contain; border: 1px solid #e0e0e0; border-radius: 2px; cursor: pointer; padding: 4px; }
.product-thumbnails img:hover, .product-thumbnails img.active { border-color: #2874f0; }
.product-info-section { padding: 24px 32px; }
.product-title { font-size: 20px; font-weight: 500; color: #212121; margin: 0 0 4px 0; line-height: 1.3; }
.product-brand { font-size: 14px; color: #878787; margin-bottom: 12px; }
.product-rating-row { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
.product-rating-badge { display: inline-flex; align-items: center; gap: 4px; background: #388e3c; color: #fff; padding: 3px 8px; border-radius: 2px; font-size: 13px; font-weight: 500; }
.product-rating-reviews { font-size: 13px; color: #878787; }
.product-rating-reviews a { color: #2874f0; text-decoration: none; }
.product-price-row { display: flex; align-items: baseline; gap: 12px; margin-bottom: 12px; flex-wrap: wrap; }
.product-current-price { font-size: 28px; font-weight: 600; color: #212121; }
.product-original-price { font-size: 18px; color: #878787; text-decoration: line-through; }
.product-discount-badge { font-size: 16px; color: #388e3c; font-weight: 500; }
.product-tax-info { font-size: 12px; color: #878787; margin-bottom: 16px; }
.product-stock { font-size: 14px; margin-bottom: 20px; }
.product-stock.in-stock { color: #388e3c; }
.product-stock.out-of-stock { color: #c62828; }
.product-delivery { display: flex; align-items: center; gap: 8px; padding: 12px 16px; background: #f5faff; border-radius: 2px; margin-bottom: 20px; font-size: 13px; color: #555; border: 1px solid #e3f2fd; }
.product-delivery svg { width: 18px; height: 18px; color: #2874f0; flex-shrink: 0; }
.product-delivery input { flex: 1; padding: 6px 10px; border: 1px solid #e0e0e0; border-radius: 2px; font-size: 13px; outline: none; }
.product-delivery button { padding: 6px 16px; background: #2874f0; color: #fff; border: none; border-radius: 2px; font-size: 13px; font-weight: 500; cursor: pointer; white-space: nowrap; }
.product-actions { display: flex; gap: 12px; margin-top: 24px; }
.product-actions .qty-selector { display: flex; align-items: center; border: 1px solid #e0e0e0; border-radius: 2px; }
.product-actions .qty-selector button { width: 36px; height: 40px; border: none; background: #fafafa; font-size: 18px; cursor: pointer; display: flex; align-items: center; justify-content: center; color: #212121; }
.product-actions .qty-selector button:hover { background: #f0f0f0; }
.product-actions .qty-selector input { width: 50px; height: 40px; border: none; border-left: 1px solid #e0e0e0; border-right: 1px solid #e0e0e0; text-align: center; font-size: 14px; outline: none; }
.btn-add-cart { padding: 12px 32px; background: #ff9f00; color: #fff; border: none; border-radius: 2px; font-size: 15px; font-weight: 600; cursor: pointer; text-transform: uppercase; }
.btn-add-cart:hover { background: #f59100; }
.btn-buy-now { padding: 12px 32px; background: #fb641b; color: #fff; border: none; border-radius: 2px; font-size: 15px; font-weight: 600; cursor: pointer; text-transform: uppercase; }
.btn-buy-now:hover { background: #e85a16; }
.product-highlights { margin-top: 24px; }
.product-highlights h3 { font-size: 16px; font-weight: 600; color: #212121; margin: 0 0 8px 0; }
.product-highlights ul { margin: 0; padding-left: 20px; }
.product-highlights li { font-size: 14px; color: #555; margin-bottom: 4px; line-height: 1.5; }
.product-description { margin-top: 20px; }
.product-description h3 { font-size: 16px; font-weight: 600; color: #212121; margin: 0 0 8px 0; }
.product-description p { font-size: 14px; color: #555; line-height: 1.6; margin: 0; }
.related-products { margin-top: 32px; }
.related-products h2 { font-size: 18px; font-weight: 600; color: #212121; margin: 0 0 16px 0; }
.related-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; }
.related-card { background: #fff; border-radius: 2px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); overflow: hidden; text-decoration: none; transition: box-shadow 0.2s; }
.related-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
.related-card-img { height: 160px; display: flex; align-items: center; justify-content: center; padding: 12px; background: #fafafa; }
.related-card-img img { max-width: 100%; max-height: 100%; object-fit: contain; }
.related-card-body { padding: 10px 14px 14px; }
.related-card-name { font-size: 14px; font-weight: 500; color: #212121; margin: 0 0 4px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.related-card-price { font-size: 15px; font-weight: 600; color: #212121; }
.related-card-original { font-size: 12px; color: #878787; text-decoration: line-through; }
.related-card-discount { font-size: 12px; color: #388e3c; font-weight: 500; }

@media (max-width: 768px) {
    .product-detail-card { grid-template-columns: 1fr; }
    .product-image-section { border-right: none; border-bottom: 1px solid #f0f0f0; }
    .product-actions { flex-direction: column; }
    .btn-add-cart, .btn-buy-now { width: 100%; text-align: center; }
}
</style>

<div class="product-detail-container">
    <div class="breadcrumb">
        <a href="<?php echo getBaseUrl(); ?>/index.php">Home</a>
        <span> &rsaquo; </span>
        <a href="<?php echo getBaseUrl(); ?>/products/products.php?category=<?php echo escapeOutput($product['category_slug']); ?>">
            <?php echo escapeOutput($product['category_name'] ?? 'Products'); ?>
        </a>
        <span> &rsaquo; </span>
        <span><?php echo escapeOutput($product['name']); ?></span>
    </div>

    <?php if ($cartMessage): ?>
        <div style="max-width:1080px;margin:0 0 16px;padding:10px 14px;border-radius:2px;font-size:14px;<?php
            echo $cartMessage['type'] === 'success' ? 'background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;' : 'background:#ffebee;color:#c62828;border:1px solid #ef9a9a;';
        ?>"><?php echo escapeOutput($cartMessage['text']); ?></div>
    <?php endif; ?>

    <div class="product-detail-card">
        <div class="product-image-section" style="position:relative;">
            <?php
            $images = [];
            if ($product['multiple_images']) {
                $images = array_filter(explode(',', $product['multiple_images']));
            }
            $mainImage = $product['image'] ?? ($images[0] ?? 'placeholder.png');
            ?>
            <button class="wishlist-btn-heart <?php echo $inWishlist ? 'active' : ''; ?>" data-product-id="<?php echo (int)$product['id']; ?>" data-wishlisted="<?php echo $inWishlist ? '1' : '0'; ?>" title="<?php echo $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>" style="position:absolute;top:16px;right:16px;z-index:10;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            </button>
            <img class="product-main-image" id="mainProductImage" 
                 src="<?php echo getBaseUrl(); ?>/uploads/<?php echo escapeOutput($mainImage); ?>" 
                 alt="<?php echo escapeOutput($product['name']); ?>"
                 onerror="this.src='<?php echo getBaseUrl(); ?>/uploads/placeholder.png'">

            <?php if (!empty($images)): ?>
                <div class="product-thumbnails">
                    <?php foreach ($images as $img): ?>
                        <img src="<?php echo getBaseUrl(); ?>/uploads/<?php echo escapeOutput($img); ?>" 
                             onclick="document.getElementById('mainProductImage').src=this.src;document.querySelectorAll('.product-thumbnails img').forEach(i=>i.classList.remove('active'));this.classList.add('active');"
                             onerror="this.style.display='none'">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="product-info-section">
            <h1 class="product-title"><?php echo escapeOutput($product['name']); ?></h1>
            <?php if ($product['brand']): ?>
                <div class="product-brand">Brand: <strong><?php echo escapeOutput($product['brand']); ?></strong></div>
            <?php endif; ?>

            <div class="product-rating-row">
                <?php if ($product['rating'] > 0): ?>
                    <span class="product-rating-badge">
                        <?php echo number_format($product['rating'], 1); ?> &#9733;
                    </span>
                    <span class="product-rating-reviews">
                        <?php echo number_format($product['reviews']); ?> ratings &amp; <?php echo number_format($product['reviews']); ?> reviews
                    </span>
                <?php endif; ?>
            </div>

            <div class="product-price-row">
                <span class="product-current-price">&#8377;<?php echo number_format($product['price']); ?></span>
                <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                    <span class="product-original-price">&#8377;<?php echo number_format($product['original_price']); ?></span>
                    <span class="product-discount-badge"><?php echo (int)$product['discount']; ?>% off</span>
                <?php endif; ?>
            </div>
            <div class="product-tax-info">inclusive of all taxes</div>

            <div class="product-stock <?php echo $product['stock_quantity'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                <?php if ($product['stock_quantity'] > 0): ?>
                    &#10003; In Stock (<?php echo (int)$product['stock_quantity']; ?> units)
                <?php else: ?>
                    &#10007; Out of Stock
                <?php endif; ?>
            </div>

            <div class="product-delivery">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                <input type="text" placeholder="Enter delivery pincode" id="pincodeInput">
                <button onclick="alert('Delivery available in 3-5 business days')">Check</button>
            </div>

            <?php if ($product['stock_quantity'] > 0): ?>
                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <input type="hidden" name="add_to_cart" value="1">

                    <div class="product-actions">
                        <div class="qty-selector">
                            <button type="button" onclick="adjustQty(-1)" tabindex="-1">-</button>
                            <input type="number" name="quantity" id="qtyInput" value="1" min="1" max="<?php echo (int)$product['stock_quantity']; ?>">
                            <button type="button" onclick="adjustQty(1)" tabindex="-1">+</button>
                        </div>
                        <button type="submit" class="btn-add-cart">Add to Cart</button>
                        <button type="submit" class="btn-buy-now" formaction="<?php echo getBaseUrl(); ?>/cart/add_to_cart.php?redirect_checkout=1">Buy Now</button>
                    </div>
                </form>
            <?php endif; ?>

            <?php if ($product['description']): ?>
                <div class="product-description">
                    <h3>Description</h3>
                    <p><?php echo escapeOutput($product['description']); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($relatedProducts)): ?>
        <div class="related-products">
            <h2>Related Products</h2>
            <div class="related-grid">
                <?php foreach ($relatedProducts as $related): ?>
                    <a href="<?php echo getBaseUrl(); ?>/products/product.php?slug=<?php echo escapeOutput($related['slug']); ?>" class="related-card">
                        <div class="related-card-img" style="position:relative;">
                            <button class="wishlist-btn-heart <?php echo $isRelWishlisted($related['id']) === '1' ? 'active' : ''; ?>" data-product-id="<?php echo (int)$related['id']; ?>" data-wishlisted="<?php echo $isRelWishlisted($related['id']); ?>" title="<?php echo $isRelWishlisted($related['id']) === '1' ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>" onclick="event.stopPropagation();event.preventDefault();">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                            </button>
                            <img src="<?php echo getBaseUrl(); ?>/uploads/<?php echo escapeOutput($related['image'] ?? 'placeholder.png'); ?>" 
                                 alt="<?php echo escapeOutput($related['name']); ?>"
                                 onerror="this.src='<?php echo getBaseUrl(); ?>/uploads/placeholder.png'">
                        </div>
                        <div class="related-card-body">
                            <div class="related-card-name"><?php echo escapeOutput($related['name']); ?></div>
                            <span class="related-card-price">&#8377;<?php echo number_format($related['price']); ?></span>
                            <?php if ($related['original_price'] && $related['original_price'] > $related['price']): ?>
                                <span class="related-card-original">&#8377;<?php echo number_format($related['original_price']); ?></span>
                                <span class="related-card-discount"><?php echo (int)$related['discount']; ?>% off</span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function adjustQty(delta) {
    var input = document.getElementById('qtyInput');
    var val = parseInt(input.value) || 1;
    var min = parseInt(input.min) || 1;
    var max = parseInt(input.max) || 99;
    val = Math.max(min, Math.min(max, val + delta));
    input.value = val;
}
</script>

<?php require_once __DIR__ . '/../includes/customer_footer.php'; ?>
