<?php
/**
 * Admin Top Navigation Bar
 * 
 * Displays the top navigation bar with admin info and logout.
 * To be included after the sidebar in admin pages.
 */
?>
<!-- Top Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container-fluid">
        <!-- Sidebar Toggle (Mobile) -->
        <button class="btn btn-primary d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarCanvas">
            <i class="bi bi-list"></i>
        </button>
        
        <!-- Brand -->
        <a class="navbar-brand" href="<?php echo $adminBase; ?>/dashboard.php">
            <i class="bi bi-shop me-2"></i>Admin Panel
        </a>

        <!-- Right Side Items -->
        <div class="d-flex align-items-center">
            <span class="text-white me-3 d-none d-md-inline">
                <i class="bi bi-person-circle me-1"></i>
                <?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?>
            </span>
            <a href="<?php echo $adminBase; ?>/logout.php" class="btn btn-outline-light btn-sm" onclick="return confirm('Are you sure you want to logout?');">
                <i class="bi bi-box-arrow-right me-1"></i>Logout
            </a>
        </div>
    </div>
</nav>
