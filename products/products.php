<?php
$pageTitle = 'Products';
require_once __DIR__ . '/../includes/customer_auth.php';

$pdo = getConnection();

// Filters
$search = sanitizeInput($_GET['search'] ?? '');
$categorySlug = sanitizeInput($_GET['category'] ?? '');
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$where = ['p.status = 1'];
$params = [];

if ($search) {
    $where[] = '(p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($categorySlug) {
    $where[] = 'c.slug = ?';
    $params[] = $categorySlug;
}

if ($minPrice > 0) {
    $where[] = 'p.price >= ?';
    $params[] = $minPrice;
}

if ($maxPrice > 0) {
    $where[] = 'p.price <= ?';
    $params[] = $maxPrice;
}

$whereClause = implode(' AND ', $where);

// Sort
$orderBy = match ($sort) {
    'price_low' => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'name_asc' => 'p.name ASC',
    'name_desc' => 'p.name DESC',
    'rating' => 'p.rating DESC',
    default => 'p.created_at DESC'
};

// Get total count
$countSql = "SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE $whereClause";
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalProducts = (int)$stmt->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);

// Get products
$sql = "SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE $whereClause 
        ORDER BY $orderBy 
        LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get wishlisted product IDs for current user
$wishlistedIds = [];
$isWishlisted = function($id) { return '0'; };
if (isCustomerLoggedIn() && !empty($products)) {
    $allIds = array_map(function($p) { return (int)$p['id']; }, $products);
    $placeholders = implode(',', array_fill(0, count($allIds), '?'));
    $stmt2 = $pdo->prepare("SELECT product_id FROM wishlist WHERE customer_id = ? AND product_id IN ($placeholders)");
    $stmt2->execute(array_merge([$_SESSION['customer_id']], $allIds));
    while ($row = $stmt2->fetch()) {
        $wishlistedIds[$row['product_id']] = true;
    }
    $isWishlisted = function($id) use ($wishlistedIds) {
        return isset($wishlistedIds[$id]) ? '1' : '0';
    };
}

// Get categories for filter
$catStmt = $pdo->query("SELECT id, name, slug FROM categories WHERE status = 1 ORDER BY name");
$categories = $catStmt->fetchAll();
?>
<?php require_once __DIR__ . '/../includes/customer_header.php'; ?>

<style>
.products-page-container { max-width: 1248px; margin: 16px auto; padding: 0 16px; display: grid; grid-template-columns: 260px 1fr; gap: 20px; }
.filter-sidebar { background: #fff; border-radius: 2px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 20px; height: fit-content; }
.filter-sidebar h3 { font-size: 15px; font-weight: 600; color: #212121; margin: 0 0 16px 0; }
.filter-section { margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid #f0f0f0; }
.filter-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.filter-section h4 { font-size: 13px; font-weight: 500; color: #878787; text-transform: uppercase; letter-spacing: 0.3px; margin: 0 0 10px 0; }
.filter-section label { display: flex; align-items: center; gap: 8px; font-size: 14px; color: #212121; cursor: pointer; padding: 4px 0; }
.filter-section label input[type="checkbox"] { accent-color: #2874f0; }
.filter-section label:hover { color: #2874f0; }
.filter-section input[type="text"], .filter-section input[type="number"] { width: 100%; padding: 8px 10px; border: 1px solid #e0e0e0; border-radius: 2px; font-size: 13px; outline: none; box-sizing: border-box; }
.filter-section input[type="text"]:focus, .filter-section input[type="number"]:focus { border-color: #2874f0; }
.price-range-inputs { display: flex; gap: 8px; align-items: center; }
.price-range-inputs input { width: 100%; }
.price-range-inputs span { color: #878787; font-size: 13px; }
.filter-btn { width: 100%; padding: 8px; background: #2874f0; color: #fff; border: none; border-radius: 2px; font-size: 13px; font-weight: 500; cursor: pointer; }
.filter-btn:hover { background: #1c5dc9; }
.clear-filter { display: block; text-align: center; margin-top: 8px; font-size: 12px; color: #2874f0; text-decoration: none; }
.clear-filter:hover { text-decoration: underline; }
.products-main { min-height: 400px; }
.products-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px; }
.products-header h1 { font-size: 18px; font-weight: 600; color: #212121; margin: 0; }
.products-count { font-size: 13px; color: #878787; }
.products-header-right { display: flex; align-items: center; gap: 12px; }
.products-header-right select { padding: 6px 10px; border: 1px solid #e0e0e0; border-radius: 2px; font-size: 13px; outline: none; background: #fff; }
.products-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; }
.product-card { background: #fff; border-radius: 2px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); overflow: hidden; transition: box-shadow 0.2s, transform 0.2s; cursor: pointer; }
.product-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); transform: translateY(-2px); }
.product-card-img { width: 100%; height: 200px; display: flex; align-items: center; justify-content: center; padding: 16px; background: #fafafa; }
.product-card-img img { max-width: 100%; max-height: 100%; object-fit: contain; }
.product-card-body { padding: 12px 14px 14px; }
.product-card-title { font-size: 14px; font-weight: 500; color: #212121; margin: 0 0 4px 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.product-card-brand { font-size: 12px; color: #878787; margin-bottom: 6px; }
.product-card-pricing { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }
.product-card-price { font-size: 16px; font-weight: 600; color: #212121; }
.product-card-original { font-size: 13px; color: #878787; text-decoration: line-through; }
.product-card-discount { font-size: 12px; color: #388e3c; font-weight: 500; }
.product-card-rating { display: inline-flex; align-items: center; gap: 4px; background: #388e3c; color: #fff; padding: 2px 6px; border-radius: 2px; font-size: 11px; font-weight: 500; }
.product-card-reviews { font-size: 12px; color: #878787; margin-left: 4px; }
.pagination { display: flex; justify-content: center; align-items: center; gap: 8px; margin-top: 24px; }
.pagination a, .pagination span { padding: 8px 14px; border: 1px solid #e0e0e0; border-radius: 2px; font-size: 14px; color: #212121; text-decoration: none; background: #fff; }
.pagination a:hover { border-color: #2874f0; color: #2874f0; }
.pagination .active { background: #2874f0; color: #fff; border-color: #2874f0; }

@media (max-width: 768px) {
    .products-page-container { grid-template-columns: 1fr; }
    .filter-sidebar { display: none; }
    .products-grid { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
}
</style>

<div class="products-page-container">
    <!-- Filter Sidebar -->
    <div class="filter-sidebar">
        <h3>Filters</h3>

        <form method="GET" action="">
            <?php if ($search): ?>
                <input type="hidden" name="search" value="<?php echo escapeOutput($search); ?>">
            <?php endif; ?>

            <div class="filter-section">
                <h4>Categories</h4>
                <label>
                    <input type="radio" name="category" value="" <?php echo empty($categorySlug) ? 'checked' : ''; ?> onchange="this.form.submit()">
                    All Categories
                </label>
                <?php foreach ($categories as $cat): ?>
                    <label>
                        <input type="radio" name="category" value="<?php echo escapeOutput($cat['slug']); ?>" <?php echo $categorySlug === $cat['slug'] ? 'checked' : ''; ?> onchange="this.form.submit()">
                        <?php echo escapeOutput($cat['name']); ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="filter-section">
                <h4>Price Range</h4>
                <div class="price-range-inputs">
                    <input type="number" name="min_price" placeholder="Min" value="<?php echo $minPrice > 0 ? (int)$minPrice : ''; ?>">
                    <span>to</span>
                    <input type="number" name="max_price" placeholder="Max" value="<?php echo $maxPrice > 0 ? (int)$maxPrice : ''; ?>">
                </div>
                <button type="submit" class="filter-btn" style="margin-top:8px;">Apply Price</button>
            </div>
        </form>

        <?php if ($search || $categorySlug || $minPrice || $maxPrice): ?>
            <a href="<?php echo getBaseUrl(); ?>/products/products.php" class="clear-filter">Clear all filters</a>
        <?php endif; ?>
    </div>

    <!-- Products Main -->
    <div class="products-main">
        <div class="products-header">
            <div>
                <h1>
                    <?php if ($search): ?>
                        Results for "<?php echo escapeOutput($search); ?>"
                    <?php elseif ($categorySlug): ?>
                        <?php
                        $catStmt = $pdo->prepare("SELECT name FROM categories WHERE slug = ?");
                        $catStmt->execute([$categorySlug]);
                        $catName = $catStmt->fetchColumn();
                        echo escapeOutput($catName ?: 'Products');
                        ?>
                    <?php else: ?>
                        All Products
                    <?php endif; ?>
                </h1>
                <div class="products-count"><?php echo $totalProducts; ?> product(s) found</div>
            </div>
            <div class="products-header-right">
                <select onchange="window.location.href=this.value">
                    <option value="<?php echo $_SERVER['SCRIPT_NAME'] . '?' . http_build_query(array_merge($_GET, ['sort' => 'newest'])); ?>" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Sort: Newest</option>
                    <option value="<?php echo $_SERVER['SCRIPT_NAME'] . '?' . http_build_query(array_merge($_GET, ['sort' => 'price_low'])); ?>" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                    <option value="<?php echo $_SERVER['SCRIPT_NAME'] . '?' . http_build_query(array_merge($_GET, ['sort' => 'price_high'])); ?>" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                    <option value="<?php echo $_SERVER['SCRIPT_NAME'] . '?' . http_build_query(array_merge($_GET, ['sort' => 'rating'])); ?>" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Rating</option>
                    <option value="<?php echo $_SERVER['SCRIPT_NAME'] . '?' . http_build_query(array_merge($_GET, ['sort' => 'name_asc'])); ?>" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name: A-Z</option>
                    <option value="<?php echo $_SERVER['SCRIPT_NAME'] . '?' . http_build_query(array_merge($_GET, ['sort' => 'name_desc'])); ?>" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name: Z-A</option>
                </select>
            </div>
        </div>

        <?php if (empty($products)): ?>
            <div style="text-align:center;padding:60px 20px;">
                <svg viewBox="0 0 24 24" width="80" height="80" fill="none" stroke="#e0e0e0" stroke-width="1"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <h3 style="font-size:18px;color:#212121;margin:16px 0 8px;">No products found</h3>
                <p style="font-size:14px;color:#878787;margin:0;">Try adjusting your search or filter criteria</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($products as $product): ?>
                    <a href="<?php echo getBaseUrl(); ?>/products/product.php?slug=<?php echo escapeOutput($product['slug']); ?>" class="product-card" style="text-decoration:none;">
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
                            <div class="product-card-pricing">
                                <span class="product-card-price">&#8377;<?php echo number_format($product['price']); ?></span>
                                <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                                    <span class="product-card-original">&#8377;<?php echo number_format($product['original_price']); ?></span>
                                    <span class="product-card-discount"><?php echo (int)$product['discount']; ?>% off</span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <?php if ($product['rating'] > 0): ?>
                                    <span class="product-card-rating"><?php echo number_format($product['rating'], 1); ?>&#9733;</span>
                                    <span class="product-card-reviews">(<?php echo (int)$product['reviews']; ?>)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">&laquo; Prev</a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="active"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/customer_footer.php'; ?>
