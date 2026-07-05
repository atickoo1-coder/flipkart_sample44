<?php
/**
 * Add Category
 * 
 * Handles adding a new category with server-side validation.
 */

$page_title = 'Add Category';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../config/database.php';

$pdo = getConnection();

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$name = $description = '';
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = isset($_POST['status']) ? 1 : 0;

        // Validation
        if (empty($name)) {
            $error = 'Category name is required.';
        } elseif (strlen($name) > 100) {
            $error = 'Category name must be less than 100 characters.';
        } else {
            try {
                // Check if category already exists
                $check = $pdo->prepare("SELECT id FROM categories WHERE name = :name");
                $check->execute([':name' => $name]);
                
                if ($check->fetch()) {
                    $error = 'A category with this name already exists.';
                } else {
                    // Insert category
                    $stmt = $pdo->prepare("
                        INSERT INTO categories (name, description, status) 
                        VALUES (:name, :description, :status)
                    ");
                    $stmt->execute([
                        ':name' => $name,
                        ':description' => $description,
                        ':status' => $status,
                    ]);

                    $_SESSION['success_message'] = 'Category "' . htmlspecialchars($name) . '" added successfully.';
                    header('Location: view.php');
                    exit();
                }
            } catch (PDOException $e) {
                $error = 'Failed to add category. Please try again.';
            }
        }
    }
}
?>
<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h4><i class="bi bi-plus-circle me-2"></i>Add Category</h4>
            <a href="view.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Categories
            </a>
        </div>

        <!-- Error/Success Messages -->
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Add Category Form -->
        <div class="form-card">
            <form method="POST" action="add.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($name); ?>" 
                                   placeholder="Enter category name" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" 
                                      placeholder="Enter category description (optional)"><?php echo htmlspecialchars($description); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="status" name="status" checked>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Save Category
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
