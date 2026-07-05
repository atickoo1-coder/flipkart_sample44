<?php
/**
 * Admin Dashboard
 * 
 * Displays overview statistics and recent products.
 * Shows Total Products, Categories, Orders (placeholder), Users (placeholder),
 * and a list of recently added products.
 */

$page_title = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/sidebar.php';
require_once __DIR__ . '/../includes/navbar.php';
require_once __DIR__ . '/../config/database.php';

$pdo = getConnection();

// Fetch statistics
$totalProducts = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCategories = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

// Fetch recent products (last 5)
$stmt = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.created_at DESC 
    LIMIT 5
");
$recentProducts = $stmt->fetchAll();
?>
<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h4><i class="bi bi-speedometer2 me-2"></i>Dashboard</h4>
            <span class="text-muted">
                <i class="bi bi-calendar me-1"></i>
                <?php echo date('l, F j, Y'); ?>
            </span>
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

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="card-number"><?php echo $totalProducts; ?></div>
                                <div class="card-label">Total Products</div>
                            </div>
                            <div class="card-icon bg-primary bg-opacity-10 text-primary">
                                <i class="bi bi-box-seam"></i>
                            </div>
                        </div>
                        <a href="products/view.php" class="stretched-link"></a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="card-number"><?php echo $totalCategories; ?></div>
                                <div class="card-label">Total Categories</div>
                            </div>
                            <div class="card-icon bg-success bg-opacity-10 text-success">
                                <i class="bi bi-collection"></i>
                            </div>
                        </div>
                        <a href="categories/view.php" class="stretched-link"></a>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="card-number">0</div>
                                <div class="card-label">Total Orders</div>
                            </div>
                            <div class="card-icon bg-warning bg-opacity-10 text-warning">
                                <i class="bi bi-cart"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="card-number">0</div>
                                <div class="card-label">Total Users</div>
                            </div>
                            <div class="card-icon bg-info bg-opacity-10 text-info">
                                <i class="bi bi-people"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Products Table -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Products</h5>
                <a href="products/add.php" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i>Add Product
                </a>
            </div>
            
            <?php if (count($recentProducts) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Date Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentProducts as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                    <td>₹<?php echo number_format($product['price'], 2); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $product['stock_quantity'] > 0 ? 'success' : 'danger'; ?>">
                                            <?php echo $product['stock_quantity']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($product['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="bi bi-inbox display-4 text-muted"></i>
                    <p class="text-muted mt-2">No products found. <a href="products/add.php">Add your first product</a></p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
