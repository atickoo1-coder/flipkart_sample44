<?php
/**
 * Customer-Facing Header
 * 
 * QuickKart-inspired design header for customer pages.
 * Separate from the admin header.
 */
require_once __DIR__ . '/customer_auth.php';
$baseUrl = getBaseUrl();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? escapeOutput($pageTitle) . ' - QuickKart Clone' : 'QuickKart - Online Shopping Site'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>/css/style.css">
    <meta name="csrf-token" content="<?php echo generateCSRFToken(); ?>">
</head>
<body>

    <!-- ===== HEADER ===== -->
    <header class="header">
        <div class="header-inner">
            <div class="logo-area">
                <a href="<?php echo $baseUrl; ?>/index.php">
                    <div class="logo-main">QuickKart</div>
                    <div class="logo-tagline">Explore <span>Plus</span></div>
                </a>
            </div>

            <div class="search-box">
                <form action="<?php echo $baseUrl; ?>/products/products.php" method="GET" style="display:flex;flex:1;">
                    <input type="text" name="search" placeholder="Search for products, brands and more" id="searchInput" value="<?php echo isset($_GET['search']) ? escapeOutput($_GET['search']) : ''; ?>">
                    <button class="search-btn" type="submit" aria-label="Search">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </button>
                </form>
                <ul class="search-suggestions" id="searchSuggestions">
                    <li>iPhone 15 Pro Max</li>
                    <li>Samsung Galaxy S24 Ultra</li>
                    <li>MacBook Air M3</li>
                    <li>Smart Watches</li>
                    <li>Mens Shoes</li>
                </ul>
            </div>

            <div class="header-actions">
                <?php if (isCustomerLoggedIn()): ?>
                    <?php $customer = getCustomer(); ?>
                    <div class="login-btn">
                        <a href="#">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <?php echo escapeOutput($customer['full_name'] ?? 'Account'); ?>
                            <svg class="chevron" width="10" height="10" viewBox="0 0 14 11"><path d="M3 2L7 6L11 2" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>
                        </a>
                        <div class="login-dropdown">
                            <div class="login-dropdown-header"><span>Hello, <?php echo escapeOutput($customer['full_name'] ?? 'User'); ?></span></div>
                            <a href="<?php echo $baseUrl; ?>/customer/profile.php" class="login-dropdown-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> My Profile
                            </a>
                            <a href="<?php echo $baseUrl; ?>/customer/orders.php" class="login-dropdown-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg> My Orders
                            </a>
                            <a href="<?php echo $baseUrl; ?>/cart/cart.php" class="login-dropdown-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg> My Cart
                            </a>
                            <a href="<?php echo $baseUrl; ?>/wishlist.php" class="login-dropdown-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg> Wishlist
                            </a>
                            <a href="<?php echo $baseUrl; ?>/index.php" class="login-dropdown-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg> Deals of the Day
                            </a>
                            <a href="<?php echo $baseUrl; ?>/customer/logout.php" class="login-dropdown-item" style="border-top:1px solid #f0f0f0;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg> Logout
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="login-btn">
                        <a href="<?php echo $baseUrl; ?>/customer/login.php">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Login
                            <svg class="chevron" width="10" height="10" viewBox="0 0 14 11"><path d="M3 2L7 6L11 2" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg>
                        </a>
                        <div class="login-dropdown">
                            <div class="login-dropdown-header"><span>New customer?</span><a href="<?php echo $baseUrl; ?>/customer/register.php">Sign Up</a></div>
                            <a href="<?php echo $baseUrl; ?>/customer/profile.php" class="login-dropdown-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg> My Profile
                            </a>
                            <a href="#" class="login-dropdown-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg> QuickKart Plus Zone
                            </a>
                            <a href="<?php echo $baseUrl; ?>/customer/orders.php" class="login-dropdown-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11L2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg> Orders
                            </a>
                            <a href="<?php echo $baseUrl; ?>/wishlist.php" class="login-dropdown-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg> Wishlist
                            </a>
                            <a href="<?php echo $baseUrl; ?>/index.php" class="login-dropdown-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg> Deals of the Day
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="more-btn">
                    <a href="#">More <svg class="chevron" width="10" height="10" viewBox="0 0 14 11"><path d="M3 2L7 6L11 2" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/></svg></a>
                    <div class="more-dropdown">
                        <a href="#" class="more-dropdown-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg> Notification Preferences
                        </a>
                        <a href="#" class="more-dropdown-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg> 24x7 Customer Care
                        </a>
                        <a href="#" class="more-dropdown-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg> Advertise
                        </a>
                        <a href="#" class="more-dropdown-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg> Download App
                        </a>
                    </div>
                </div>

                <div class="wishlist-btn">
                    <a href="<?php echo $baseUrl; ?>/wishlist.php">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                        Wishlist
                        <span class="wishlist-badge" id="wishlistBadge" style="display:none;">0</span>
                    </a>
                </div>
                <div class="cart-btn">
                    <a href="<?php echo $baseUrl; ?>/cart/cart.php">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                        Cart
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- ===== NAV BAR ===== -->
    <nav class="nav-bar">
        <div class="nav-inner">
            <a href="<?php echo $baseUrl; ?>/index.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> For You
            </a>
            <a href="<?php echo $baseUrl; ?>/products/products.php?category=fashion" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg> Fashion
            </a>
            <a href="<?php echo $baseUrl; ?>/products/products.php?category=mobiles" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg> Mobiles
            </a>
            <a href="<?php echo $baseUrl; ?>/products/products.php?category=beauty" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg> Beauty
            </a>
            <a href="<?php echo $baseUrl; ?>/products/products.php?category=electronics" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 12 16.5"/></svg> Electronics
            </a>
            <a href="<?php echo $baseUrl; ?>/products/products.php?category=home-furniture" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg> Home
            </a>
            <a href="<?php echo $baseUrl; ?>/products/products.php?category=electronics" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/></svg> Appliances
            </a>
            <a href="<?php echo $baseUrl; ?>/products/products.php" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg> Toys, Baby
            </a>
            <a href="<?php echo $baseUrl; ?>/products/products.php?category=sports" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg> Sports
            </a>
            <a href="<?php echo $baseUrl; ?>/products/products.php?category=books" class="nav-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg> Books
            </a>
        </div>
    </nav>

    <!-- ===== FLASH MESSAGES ===== -->
    <?php $flash = getFlashMessage(); ?>
    <?php if ($flash): ?>
        <div style="max-width:1248px;margin:8px auto 0;padding:0 16px;">
            <div style="padding:12px 16px;border-radius:2px;font-size:14px;<?php
                echo $flash['type'] === 'success' ? 'background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;' : '';
                echo $flash['type'] === 'error' ? 'background:#ffebee;color:#c62828;border:1px solid #ef9a9a;' : '';
                echo $flash['type'] === 'info' ? 'background:#e3f2fd;color:#1565c0;border:1px solid #90caf9;' : '';
                echo $flash['type'] === 'warning' ? 'background:#fff8e1;color:#f57f17;border:1px solid #ffe082;' : '';
            ?>">
                <?php echo escapeOutput($flash['message']); ?>
            </div>
        </div>
    <?php endif; ?>
