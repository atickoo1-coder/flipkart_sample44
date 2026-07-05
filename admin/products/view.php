<?php
/**
 * View Products
 * 
 * Displays all products in a responsive table with edit/delete actions.
 * Shows product image, name, category, price, stock, status, date added.
 */

$page_title = 'Products';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../config/database.php';

$pdo = getConnection();

// Generate CSRF token for delete forms
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch all products with category name
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC
");
$products = $stmt->fetchAll();
?>
<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h4><i class="bi bi-box-seam me-2"></i>Products</h4>
            <a href="add.php" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Add Product
            </a>
        </div>

        <!-- Flash Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?php 
                    echo htmlspecialchars($_SESSION['success_message']);
                    unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php 
                    echo htmlspecialchars($_SESSION['error_message']);
                    unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Products Table -->
        <div class="table-container">
            <?php if (count($products) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Status</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
                                        <?php if ($product['image']): ?>
                                            <?php if ($product['product_url']): ?>
                                                <a href="<?php echo htmlspecialchars($product['product_url']); ?>" target="_blank" title="View product">
                                            <?php endif; ?>
                                            <img src="<?php echo rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/'); ?>/uploads/<?php echo htmlspecialchars($product['image']); ?>" 
                                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                                 class="product-image-thumb">
                                            <?php if ($product['product_url']): ?>
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <div class="product-image-thumb d-flex align-items-center justify-content-center text-muted">
                                                <i class="bi bi-image" style="font-size: 24px;"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                                        <?php if ($product['brand']): ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($product['brand']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td>₹<?php echo number_format($product['price'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $product['stock_quantity'] > 10 ? 'success' : ($product['stock_quantity'] > 0 ? 'warning' : 'danger'); ?>">
                                            <?php echo $product['stock_quantity']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($product['status'] == 1): ?>
                                            <span class="badge-active">Active</span>
                                        <?php else: ?>
                                            <span class="badge-inactive">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($product['created_at'])); ?></td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning btn-action me-1">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form method="POST" action="delete.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger btn-action">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-box-seam display-1 text-muted"></i>
                    <p class="text-muted mt-3 mb-3">No products found.</p>
                    <a href="add.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Add Your First Product
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
