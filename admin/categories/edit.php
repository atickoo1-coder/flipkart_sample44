<?php
/**
 * Edit Category
 * 
 * Handles updating an existing category with validation.
 */

$page_title = 'Edit Category';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../config/database.php';

$pdo = getConnection();

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch category data
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
$stmt->execute([':id' => $id]);
$category = $stmt->fetch();

if (!$category) {
    $_SESSION['error_message'] = 'Category not found.';
    header('Location: view.php');
    exit();
}

$name = $category['name'];
$description = $category['description'];
$status = $category['status'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $new_status = isset($_POST['status']) ? 1 : 0;

        // Validation
        if (empty($name)) {
            $error = 'Category name is required.';
        } elseif (strlen($name) > 100) {
            $error = 'Category name must be less than 100 characters.';
        } else {
            try {
                // Check if name already exists (excluding current category)
                $check = $pdo->prepare("SELECT id FROM categories WHERE name = :name AND id != :id");
                $check->execute([':name' => $name, ':id' => $id]);
                
                if ($check->fetch()) {
                    $error = 'A category with this name already exists.';
                } else {
                    // Update category
                    $stmt = $pdo->prepare("
                        UPDATE categories 
                        SET name = :name, description = :description, status = :status 
                        WHERE id = :id
                    ");
                    $stmt->execute([
                        ':name' => $name,
                        ':description' => $description,
                        ':status' => $new_status,
                        ':id' => $id,
                    ]);

                    $_SESSION['success_message'] = 'Category "' . htmlspecialchars($name) . '" updated successfully.';
                    header('Location: view.php');
                    exit();
                }
            } catch (PDOException $e) {
                $error = 'Failed to update category. Please try again.';
            }
        }
    }
}
?>
<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h4><i class="bi bi-pencil-square me-2"></i>Edit Category</h4>
            <a href="view.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Categories
            </a>
        </div>

        <!-- Error Messages -->
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Edit Category Form -->
        <div class="form-card">
            <form method="POST" action="edit.php?id=<?php echo $id; ?>" novalidate>
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
                                <input class="form-check-input" type="checkbox" id="status" name="status" 
                                       <?php echo $status ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Update Category
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
