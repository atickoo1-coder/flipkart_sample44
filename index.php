<?php
$pageTitle = 'Online Shopping Site';
require_once __DIR__ . '/includes/customer_auth.php';

$pdo = getConnection();

// Featured products
$stmt = $pdo->query(
    "SELECT p.*, c.name as category_name 
     FROM products p 
     LEFT JOIN categories c ON p.category_id = c.id 
     WHERE p.status = 1 AND p.featured = 1 
     ORDER BY p.rating DESC 
     LIMIT 8"
);
$featuredProducts = $stmt->fetchAll();

// Latest products
$stmt = $pdo->query(
    "SELECT p.*, c.name as category_name 
     FROM products p 
     LEFT JOIN categories c ON p.category_id = c.id 
     WHERE p.status = 1 
     ORDER BY p.created_at DESC 
     LIMIT 12"
);
$latestProducts = $stmt->fetchAll();

// Categories for grid
$stmt = $pdo->query("SELECT id, name, slug FROM categories WHERE status = 1 ORDER BY name LIMIT 6");
$categories = $stmt->fetchAll();

$categoryProducts = [];
foreach ($categories as $cat) {
    $stmt = $pdo->prepare(
        "SELECT id, name, slug, price, original_price, discount, image, rating, reviews 
         FROM products WHERE category_id = ? AND status = 1 
         ORDER BY RAND() LIMIT 4"
    );
    $stmt->execute([$cat['id']]);
    $catProducts = $stmt->fetchAll();
    if (!empty($catProducts)) {
        $categoryProducts[$cat['id']] = [
            'name' => $cat['name'],
            'slug' => $cat['slug'],
            'products' => $catProducts
        ];
    }
}

// Gather all product IDs for wishlist check
$allProductIds = array_merge(
    array_column($featuredProducts, 'id'),
    array_column($latestProducts, 'id')
);
foreach ($categoryProducts as $catData) {
    foreach ($catData['products'] as $p) {
        $allProductIds[] = $p['id'];
    }
}

// Get wishlisted product IDs for current user
$wishlistedIds = [];
if (isCustomerLoggedIn() && !empty($allProductIds)) {
    $allProductIds = array_unique(array_map('intval', $allProductIds));
    $placeholders = implode(',', array_fill(0, count($allProductIds), '?'));
    $stmt = $pdo->prepare("SELECT product_id FROM wishlist WHERE customer_id = ? AND product_id IN ($placeholders)");
    $stmt->execute(array_merge([$_SESSION['customer_id']], $allProductIds));
    while ($row = $stmt->fetch()) {
        $wishlistedIds[$row['product_id']] = true;
    }
}
$isWishlisted = function($id) use ($wishlistedIds) {
    return isset($wishlistedIds[$id]) ? '1' : '0';
};
?>
<?php require_once __DIR__ . '/includes/customer_header.php'; ?>

<style>
/* Homepage Banner */
.banner-section { margin: 0 auto; max-width: 1248px; padding: 0 8px; }
.banner-slider { display: grid; grid-template-columns: 2fr 1fr; gap: 8px; margin-bottom: 16px; }
.banner-main { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 2px; padding: 40px 48px; color: #fff; min-height: 240px; display: flex; flex-direction: column; justify-content: center; }
.banner-main h1 { font-size: 28px; font-weight: 700; margin: 0 0 8px 0; }
.banner-main p { font-size: 16px; opacity: 0.9; margin: 0 0 20px 0; max-width: 360px; }
.banner-main a { display: inline-block; padding: 10px 28px; background: #fff; color: #2874f0; border-radius: 2px; font-weight: 600; font-size: 14px; text-decoration: none; text-transform: uppercase; align-self: flex-start; }
.banner-side { display: flex; flex-direction: column; gap: 8px; }
.banner-side-item { flex: 1; border-radius: 2px; padding: 20px 24px; display: flex; flex-direction: column; justify-content: center; }
.banner-side-item h3 { margin: 0 0 4px 0; font-size: 16px; font-weight: 600; }
.banner-side-item p { margin: 0; font-size: 13px; opacity: 0.8; }
.banner-side-item a { color: inherit; text-decoration: none; }
.banner-side-item:first-child { background: #e3f2fd; color: #1565c0; }
.banner-side-item:last-child { background: #fff3e0; color: #e65100; }

/* Sections */
.section { max-width: 1248px; margin: 0 auto 24px; padding: 0 8px; }
.section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
.section-header h2 { font-size: 22px; font-weight: 600; color: #212121; margin: 0; }
.section-header a { font-size: 14px; color: #2874f0; text-decoration: none; font-weight: 500; display: flex; align-items: center; gap: 4px; }
.section-header a:hover { text-decoration: underline; }

/* Product Grid */
.product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 10px; }
.product-card { background: #fff; border-radius: 2px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); overflow: hidden; text-decoration: none; transition: box-shadow 0.2s, transform 0.2s; }
.product-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); transform: translateY(-2px); }
.product-card-img { height: 180px; display: flex; align-items: center; justify-content: center; padding: 16px; background: #fafafa; }
.product-card-img img { max-width: 100%; max-height: 100%; object-fit: contain; }
.product-card-body { padding: 10px 14px 14px; }
.product-card-title { font-size: 14px; font-weight: 500; color: #212121; margin: 0 0 2px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.product-card-brand { font-size: 12px; color: #878787; margin-bottom: 6px; }
.product-card-price { font-size: 16px; font-weight: 600; color: #212121; }
.product-card-original { font-size: 13px; color: #878787; text-decoration: line-through; margin-left: 6px; }
.product-card-discount { font-size: 12px; color: #388e3c; font-weight: 500; margin-left: 6px; }
.product-card-rating { display: inline-flex; align-items: center; gap: 4px; background: #388e3c; color: #fff; padding: 2px 6px; border-radius: 2px; font-size: 11px; font-weight: 500; margin-top: 4px; }
.product-card-reviews { font-size: 12px; color: #878787; margin-left: 4px; }

/* Category Grid */
.category-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px; }
.category-card { background: #fff; border-radius: 2px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 24px 16px; text-align: center; text-decoration: none; transition: box-shadow 0.2s; }
.category-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
.category-card svg { width: 40px; height: 40px; color: #2874f0; margin-bottom: 8px; }
.category-card h3 { font-size: 14px; font-weight: 500; color: #212121; margin: 0 0 4px 0; }
.category-card p { font-size: 12px; color: #878787; margin: 0; }

/* Category Product Row */
.category-product-section { max-width: 1248px; margin: 0 auto 24px; padding: 0 8px; }
.category-product-section .product-row { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 10px; }
.featured-badge { position: absolute; top: 8px; left: 8px; background: #fb641b; color: #fff; font-size: 11px; padding: 2px 8px; border-radius: 2px; font-weight: 500; }

@media (max-width: 768px) {
    .banner-slider { grid-template-columns: 1fr; }
    .product-grid, .category-product-section .product-row { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); }
}
</style>

<!-- Banner -->
<div class="banner-section">
    <div class="banner-slider">
        <div class="banner-main">
            <h1>Biggest Sale of the Year!</h1>
            <p>Up to 70% off on top brands. Limited time offer. Shop now and save big!</p>
            <a href="<?php echo getBaseUrl(); ?>/products/products.php">Shop Now</a>
        </div>
        <div class="banner-side">
            <div class="banner-side-item">
                <a href="<?php echo getBaseUrl(); ?>/products/products.php?category=mobiles">
                    <h3>Smartphones</h3>
                    <p>From &#8377;7,999</p>
                </a>
            </div>
            <div class="banner-side-item">
                <a href="<?php echo getBaseUrl(); ?>/products/products.php?category=fashion">
                    <h3>Fashion Store</h3>
                    <p>Min 50% off</p>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Categories -->
<div class="section">
    <div class="section-header">
        <h2>Shop by Category</h2>
        <a href="<?php echo getBaseUrl(); ?>/products/products.php">View All &rarr;</a>
    </div>
    <div class="category-grid">
        <a href="<?php echo getBaseUrl(); ?>/products/products.php?category=mobiles" class="category-card">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
            <h3>Mobiles</h3>
            <p>Best Offers</p>
        </a>
        <a href="<?php echo getBaseUrl(); ?>/products/products.php?category=fashion" class="category-card">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            <h3>Fashion</h3>
            <p>Trendy Styles</p>
        </a>
        <a href="<?php echo getBaseUrl(); ?>/products/products.php?category=electronics" class="category-card">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 12 16.5"/></svg>
            <h3>Electronics</h3>
            <p>Latest Tech</p>
        </a>
        <a href="<?php echo getBaseUrl(); ?>/products/products.php?category=home-furniture" class="category-card">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <h3>Home & Furniture</h3>
            <p>Decor Ideas</p>
        </a>
        <a href="<?php echo getBaseUrl(); ?>/products/products.php?category=beauty" class="category-card">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
            <h3>Beauty</h3>
            <p>Top Brands</p>
        </a>
        <a href="<?php echo getBaseUrl(); ?>/products/products.php?category=books" class="category-card">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
            <h3>Books</h3>
            <p>Best Reads</p>
        </a>
    </div>
</div>

<!-- Featured Products -->
<?php if (!empty($featuredProducts)): ?>
<div class="section">
    <div class="section-header">
        <h2>Featured Products</h2>
        <a href="<?php echo getBaseUrl(); ?>/products/products.php">View All &rarr;</a>
    </div>
    <div class="product-grid">
        <?php foreach ($featuredProducts as $product): ?>
            <a href="<?php echo getBaseUrl(); ?>/products/product.php?slug=<?php echo escapeOutput($product['slug']); ?>" class="product-card" style="position:relative;">
                <span class="featured-badge">Featured</span>
                <div class="product-card-img" style="position:relative;">
                    <button class="wishlist-btn-heart <?php echo $isWishlisted($product['id']) === '1' ? 'active' : ''; ?>" data-product-id="<?php echo (int)$product['id']; ?>" data-wishlisted="<?php echo $isWishlisted($product['id']); ?>" title="<?php echo $isWishlisted($product['id']) === '1' ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>" onclick="event.stopPropagation();event.preventDefault();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </button>
                    <img src="<?php echo getBaseUrl(); ?>/uploads/<?php echo escapeOutput($product['image'] ?? 'placeholder.png'); ?>" 
                         alt="<?php echo escapeOutput($product['name']); ?>"
                         onerror="this.src='<?php echo getBaseUrl(); ?>/uploads/placeholder.png'">
                </div>
                <div class="product-card-body">
                    <div class="product-card-title"><?php echo escapeOutput($product['name']); ?></div>
                    <?php if ($product['brand']): ?>
                        <div class="product-card-brand"><?php echo escapeOutput($product['brand']); ?></div>
                    <?php endif; ?>
                    <span class="product-card-price">&#8377;<?php echo number_format($product['price']); ?></span>
                    <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                        <span class="product-card-original">&#8377;<?php echo number_format($product['original_price']); ?></span>
                        <span class="product-card-discount"><?php echo (int)$product['discount']; ?>% off</span>
                    <?php endif; ?>
                    <?php if ($product['rating'] > 0): ?>
                        <div><span class="product-card-rating"><?php echo number_format($product['rating'], 1); ?>&#9733;</span><span class="product-card-reviews">(<?php echo (int)$product['reviews']; ?>)</span></div>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Category Product Sections -->
<?php foreach ($categoryProducts as $catId => $catData): ?>
<div class="category-product-section">
    <div class="section-header">
        <h2><?php echo escapeOutput($catData['name']); ?></h2>
        <a href="<?php echo getBaseUrl(); ?>/products/products.php?category=<?php echo escapeOutput($catData['slug']); ?>">View All &rarr;</a>
    </div>
    <div class="product-row">
        <?php foreach ($catData['products'] as $product): ?>
            <a href="<?php echo getBaseUrl(); ?>/products/product.php?slug=<?php echo escapeOutput($product['slug']); ?>" class="product-card">
                <div class="product-card-img" style="position:relative;">
                    <button class="wishlist-btn-heart <?php echo $isWishlisted($product['id']) === '1' ? 'active' : ''; ?>" data-product-id="<?php echo (int)$product['id']; ?>" data-wishlisted="<?php echo $isWishlisted($product['id']); ?>" title="<?php echo $isWishlisted($product['id']) === '1' ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>" onclick="event.stopPropagation();event.preventDefault();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </button>
                    <img src="<?php echo getBaseUrl(); ?>/uploads/<?php echo escapeOutput($product['image'] ?? 'placeholder.png'); ?>" 
                         alt="<?php echo escapeOutput($product['name']); ?>"
                         onerror="this.src='<?php echo getBaseUrl(); ?>/uploads/placeholder.png'">
                </div>
                <div class="product-card-body">
                    <div class="product-card-title"><?php echo escapeOutput($product['name']); ?></div>
                    <span class="product-card-price">&#8377;<?php echo number_format($product['price']); ?></span>
                    <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                        <span class="product-card-original">&#8377;<?php echo number_format($product['original_price']); ?></span>
                        <span class="product-card-discount"><?php echo (int)$product['discount']; ?>% off</span>
                    <?php endif; ?>
                    <?php if ($product['rating'] > 0): ?>
                        <div><span class="product-card-rating"><?php echo number_format($product['rating'], 1); ?>&#9733;</span><span class="product-card-reviews">(<?php echo (int)$product['reviews']; ?>)</span></div>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<!-- Latest Products -->
<?php if (!empty($latestProducts)): ?>
<div class="section">
    <div class="section-header">
        <h2>New Arrivals</h2>
        <a href="<?php echo getBaseUrl(); ?>/products/products.php?sort=newest">View All &rarr;</a>
    </div>
    <div class="product-grid">
        <?php foreach ($latestProducts as $product): ?>
            <a href="<?php echo getBaseUrl(); ?>/products/product.php?slug=<?php echo escapeOutput($product['slug']); ?>" class="product-card">
                <div class="product-card-img" style="position:relative;">
                    <button class="wishlist-btn-heart <?php echo $isWishlisted($product['id']) === '1' ? 'active' : ''; ?>" data-product-id="<?php echo (int)$product['id']; ?>" data-wishlisted="<?php echo $isWishlisted($product['id']); ?>" title="<?php echo $isWishlisted($product['id']) === '1' ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>" onclick="event.stopPropagation();event.preventDefault();">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    </button>
                    <img src="<?php echo getBaseUrl(); ?>/uploads/<?php echo escapeOutput($product['image'] ?? 'placeholder.png'); ?>" 
                         alt="<?php echo escapeOutput($product['name']); ?>"
                         onerror="this.src='<?php echo getBaseUrl(); ?>/uploads/placeholder.png'">
                </div>
                <div class="product-card-body">
                    <div class="product-card-title"><?php echo escapeOutput($product['name']); ?></div>
                    <?php if ($product['brand']): ?>
                        <div class="product-card-brand"><?php echo escapeOutput($product['brand']); ?></div>
                    <?php endif; ?>
                    <span class="product-card-price">&#8377;<?php echo number_format($product['price']); ?></span>
                    <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                        <span class="product-card-original">&#8377;<?php echo number_format($product['original_price']); ?></span>
                        <span class="product-card-discount"><?php echo (int)$product['discount']; ?>% off</span>
                    <?php endif; ?>
                    <?php if ($product['rating'] > 0): ?>
                        <div><span class="product-card-rating"><?php echo number_format($product['rating'], 1); ?>&#9733;</span><span class="product-card-reviews">(<?php echo (int)$product['reviews']; ?>)</span></div>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/customer_footer.php'; ?>
