<?php
/**
 * Admin Sidebar Navigation
 * 
 * Displays the sidebar with navigation links.
 * Highlights the current active page.
 */

// Get current page for active link highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Calculate admin base URL (works from any nesting depth)
$scriptPath = $_SERVER['SCRIPT_NAME'];
$adminPos = strrpos($scriptPath, '/admin/');
$adminBase = $adminPos !== false ? substr($scriptPath, 0, $adminPos + 6) : dirname($scriptPath);
?>
<!-- Desktop Sidebar -->
<div class="sidebar d-none d-lg-block">
    <div class="sidebar-header">
        <i class="bi bi-shop me-2"></i>Admin Panel
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo $adminBase; ?>/dashboard.php">
                <i class="bi bi-speedometer2 me-2"></i>Dashboard
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_dir === 'categories' ? 'active' : ''; ?>" href="<?php echo $adminBase; ?>/categories/view.php">
                <i class="bi bi-collection me-2"></i>Categories
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_dir === 'products' ? 'active' : ''; ?>" href="<?php echo $adminBase; ?>/products/view.php">
                <i class="bi bi-box-seam me-2"></i>Products
            </a>
        </li>
    </ul>
</div>

<!-- Mobile Offcanvas Sidebar -->
<div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarCanvas">
    <div class="offcanvas-header bg-primary text-white">
        <h5 class="offcanvas-title"><i class="bi bi-shop me-2"></i>Admin Panel</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0">
        <ul class="nav flex-column mt-2">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="<?php echo $adminBase; ?>/dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_dir === 'categories' ? 'active' : ''; ?>" href="<?php echo $adminBase; ?>/categories/view.php">
                    <i class="bi bi-collection me-2"></i>Categories
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_dir === 'products' ? 'active' : ''; ?>" href="<?php echo $adminBase; ?>/products/view.php">
                    <i class="bi bi-box-seam me-2"></i>Products
                </a>
            </li>
        </ul>
        <hr>
        <div class="px-3">
            <span class="text-muted small">
                <i class="bi bi-person-circle me-1"></i>
                <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>
            </span>
        </div>
    </div>
</div>
