<?php
/**
 * View Categories
 * 
 * Displays all categories in a responsive table with edit/delete actions.
 */

$page_title = 'Categories';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../config/database.php';

$pdo = getConnection();

// Generate CSRF token for delete forms
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch all categories
$stmt = $pdo->query("SELECT * FROM categories ORDER BY created_at DESC");
$categories = $stmt->fetchAll();
?>
<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h4><i class="bi bi-collection me-2"></i>Categories</h4>
            <a href="add.php" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Add Category
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

        <!-- Categories Table -->
        <div class="table-container">
            <?php if (count($categories) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><?php echo $cat['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($cat['name']); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($cat['description'] ? substr($cat['description'], 0, 60) . '...' : '-'); ?>
                                    </td>
                                    <td>
                                        <?php if ($cat['status'] == 1): ?>
                                            <span class="badge-active">Active</span>
                                        <?php else: ?>
                                            <span class="badge-inactive">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('d M Y', strtotime($cat['created_at'])); ?></td>
                                    <td>
                                        <a href="edit.php?id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-warning btn-action me-1">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form method="POST" action="delete.php" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                            <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
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
                    <i class="bi bi-collection display-1 text-muted"></i>
                    <p class="text-muted mt-3 mb-3">No categories found.</p>
                    <a href="add.php" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i>Add Your First Category
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
