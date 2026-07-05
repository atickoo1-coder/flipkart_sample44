<?php
/**
 * Add Product
 * 
 * Handles adding a new product with image upload, validation, and security.
 * Supports single image upload with file type and size validation.
 */

$page_title = 'Add Product';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
require_once __DIR__ . '/../../includes/navbar.php';
require_once __DIR__ . '/../../config/database.php';

$pdo = getConnection();

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch active categories for dropdown
$stmt = $pdo->query("SELECT id, name FROM categories WHERE status = 1 ORDER BY name");
$categories = $stmt->fetchAll();

$name = $description = $brand = $product_url = '';
$price = $stock_quantity = 0;
$category_id = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $brand = trim($_POST['brand'] ?? '');
        $product_url = trim($_POST['product_url'] ?? '');
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $stock_quantity = filter_input(INPUT_POST, 'stock_quantity', FILTER_VALIDATE_INT);
        $category_id = (int)($_POST['category_id'] ?? 0);
        $status = isset($_POST['status']) ? 1 : 0;

        // Validation
        if (empty($name)) {
            $error = 'Product name is required.';
        } elseif (strlen($name) > 200) {
            $error = 'Product name must be less than 200 characters.';
        } elseif ($category_id <= 0) {
            $error = 'Please select a category.';
        } elseif ($price === false || $price < 0) {
            $error = 'Please enter a valid price.';
        } elseif ($stock_quantity === false || $stock_quantity < 0) {
            $error = 'Please enter a valid stock quantity.';
        } else {
            // Handle image upload
            $imageName = '';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = uploadImage($_FILES['image']);
                if ($uploadResult['success']) {
                    $imageName = $uploadResult['filename'];
                } else {
                    $error = $uploadResult['message'];
                }
            }

            if (empty($error)) {
                try {
                    $stmt = $pdo->prepare("
                        INSERT INTO products (category_id, name, description, price, brand, product_url, stock_quantity, image, status)
                        VALUES (:category_id, :name, :description, :price, :brand, :product_url, :stock_quantity, :image, :status)
                    ");
                    $stmt->execute([
                        ':category_id' => $category_id,
                        ':name' => $name,
                        ':description' => $description,
                        ':price' => $price,
                        ':brand' => $brand,
                        ':product_url' => $product_url ?: null,
                        ':stock_quantity' => $stock_quantity,
                        ':image' => $imageName,
                        ':status' => $status,
                    ]);

                    $_SESSION['success_message'] = 'Product "' . htmlspecialchars($name) . '" added successfully.';
                    header('Location: view.php');
                    exit();
                } catch (PDOException $e) {
                    // If insert fails, delete uploaded image
                    if ($imageName && file_exists(__DIR__ . '/../../uploads/' . $imageName)) {
                        unlink(__DIR__ . '/../../uploads/' . $imageName);
                    }
                    $error = 'Failed to add product. Please try again.';
                }
            } else {
                // If validation failed but image was uploaded, delete it
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = uploadImage($_FILES['image']);
                    if ($uploadResult['success'] && file_exists(__DIR__ . '/../../uploads/' . $uploadResult['filename'])) {
                        unlink(__DIR__ . '/../../uploads/' . $uploadResult['filename']);
                    }
                }
            }
        }
    }
}

/**
 * Upload product image with validation
 * 
 * @param array $file Uploaded file data
 * @return array Success status and message/filename
 */
function uploadImage($file) {
    $uploadDir = __DIR__ . '/../../uploads/';
    
    // Create uploads directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Allowed file types
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'];
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

    // Max file size (2MB)
    $maxSize = 2 * 1024 * 1024;

    // Validate file type
    $fileType = mime_content_type($file['tmp_name']);
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($fileType, $allowedTypes) || !in_array($fileExtension, $allowedExtensions)) {
        return ['success' => false, 'message' => 'Only JPG, JPEG, PNG, and WEBP files are allowed.'];
    }

    // Validate file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size must be less than 2MB.'];
    }

    // Generate unique filename to prevent overwrites
    $uniqueName = uniqid('prod_', true) . '.' . $fileExtension;

    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $uniqueName)) {
        return ['success' => true, 'filename' => $uniqueName];
    } else {
        return ['success' => false, 'message' => 'Failed to upload image. Please try again.'];
    }
}
?>
<div class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h4><i class="bi bi-plus-circle me-2"></i>Add Product</h4>
            <a href="view.php" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Products
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

        <!-- Add Product Form -->
        <div class="form-card">
            <form method="POST" action="add.php" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

                <div class="row">
                    <div class="col-md-8">
                        <!-- Product Name -->
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($name); ?>" 
                                   placeholder="Enter product name" required>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="4" 
                                      placeholder="Enter product description"><?php echo htmlspecialchars($description); ?></textarea>
                        </div>

                        <!-- Price and Stock Row -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="price" class="form-label">Price (₹) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="price" name="price" 
                                       value="<?php echo $price > 0 ? htmlspecialchars($price) : ''; ?>" 
                                       placeholder="0.00" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label for="stock_quantity" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" 
                                       value="<?php echo $stock_quantity > 0 ? htmlspecialchars($stock_quantity) : ''; ?>" 
                                       placeholder="0" min="0" required>
                            </div>
                        </div>

                        <!-- Category, Brand and Product URL Row -->
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" 
                                                <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="brand" class="form-label">Brand</label>
                                <input type="text" class="form-control" id="brand" name="brand" 
                                       value="<?php echo htmlspecialchars($brand); ?>" 
                                       placeholder="Enter brand name">
                            </div>
                            <div class="col-md-4">
                                <label for="product_url" class="form-label">Product URL</label>
                                <input type="url" class="form-control" id="product_url" name="product_url" 
                                       value="<?php echo htmlspecialchars($product_url); ?>" 
                                       placeholder="https://example.com/product">
                            </div>
                        </div>

                        <!-- Product Image Upload -->
                        <div class="mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="image" name="image" 
                                   accept="image/jpeg,image/png,image/webp" data-preview="imagePreview">
                            <div class="form-text">Allowed: JPG, JPEG, PNG, WEBP. Max size: 2MB.</div>
                            <div class="mt-2">
                                <img id="imagePreview" class="image-preview" style="display: none;" alt="Image preview">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="status" name="status" checked>
                                <label class="form-check-label" for="status">Active</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Save Product
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
