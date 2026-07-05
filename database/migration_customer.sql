-- ============================================================
-- Migration: Customer System Tables & Product Enhancements
-- Adds customer authentication, cart, orders, and product fields
-- ============================================================
USE `ecommerce_db`;

-- ============================================================
-- Add columns to existing products table for customer features
-- ============================================================
ALTER TABLE `products`
    ADD COLUMN IF NOT EXISTS `slug` VARCHAR(200) DEFAULT NULL AFTER `name`,
    ADD COLUMN IF NOT EXISTS `original_price` DECIMAL(10,2) DEFAULT NULL AFTER `price`,
    ADD COLUMN IF NOT EXISTS `discount` INT DEFAULT 0 AFTER `original_price`,
    ADD COLUMN IF NOT EXISTS `rating` DECIMAL(2,1) DEFAULT 0.0 AFTER `stock_quantity`,
    ADD COLUMN IF NOT EXISTS `reviews` INT DEFAULT 0 AFTER `rating`,
    ADD COLUMN IF NOT EXISTS `featured` TINYINT(1) DEFAULT 0 AFTER `status`,
    ADD INDEX IF NOT EXISTS `idx_slug` (`slug`),
    ADD INDEX IF NOT EXISTS `idx_featured` (`featured`);

-- Update product slugs for existing products
UPDATE `products` SET `slug` = LOWER(REPLACE(REPLACE(REPLACE(`name`, ' ', '-'), ',', ''), '&', 'and')) WHERE `slug` IS NULL;

-- ============================================================
-- Add slug column to categories
-- ============================================================
ALTER TABLE `categories`
    ADD COLUMN IF NOT EXISTS `slug` VARCHAR(100) DEFAULT NULL AFTER `name`,
    ADD INDEX IF NOT EXISTS `idx_cat_slug` (`slug`);

UPDATE `categories` SET `slug` = LOWER(REPLACE(`name`, ' & ', '-')) WHERE `slug` IS NULL;
UPDATE `categories` SET `slug` = LOWER(REPLACE(`slug`, ' ', '-')) WHERE `slug` LIKE '% %';
UPDATE `categories` SET `slug` = LOWER(REPLACE(`slug`, '--', '-')) WHERE `slug` LIKE '%--%';
UPDATE `categories` SET `slug` = LOWER(REPLACE(`slug`, '--', '-')) WHERE `slug` LIKE '%--%';

-- ============================================================
-- Table: customers
-- Stores registered customer accounts
-- ============================================================
CREATE TABLE IF NOT EXISTS `customers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `phone` VARCHAR(20) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `address` TEXT DEFAULT NULL,
    `city` VARCHAR(50) DEFAULT NULL,
    `state` VARCHAR(50) DEFAULT NULL,
    `postal_code` VARCHAR(20) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_customer_email` (`email`),
    INDEX `idx_customer_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: wishlist
-- Stores customer wishlist items (unique per customer per product)
-- ============================================================
CREATE TABLE IF NOT EXISTS `wishlist` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_wishlist_item` (`customer_id`, `product_id`),
    INDEX `idx_wishlist_customer` (`customer_id`),
    INDEX `idx_wishlist_product` (`product_id`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: cart
-- Shopping cart items for customers
-- ============================================================
CREATE TABLE IF NOT EXISTS `cart` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_cart_item` (`customer_id`, `product_id`),
    INDEX `idx_cart_customer` (`customer_id`),
    INDEX `idx_cart_product` (`product_id`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: orders
-- Order headers placed by customers
-- ============================================================
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_number` VARCHAR(20) NOT NULL UNIQUE,
    `customer_id` INT NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `address` TEXT NOT NULL,
    `city` VARCHAR(50) NOT NULL,
    `state` VARCHAR(50) NOT NULL,
    `postal_code` VARCHAR(20) NOT NULL,
    `total_amount` DECIMAL(10,2) NOT NULL,
    `order_status` ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    `payment_status` ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_order_customer` (`customer_id`),
    INDEX `idx_order_number` (`order_number`),
    INDEX `idx_order_status` (`order_status`),
    FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: order_items
-- Individual items within an order
-- ============================================================
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `product_id` INT NOT NULL,
    `product_name` VARCHAR(200) NOT NULL,
    `product_image` VARCHAR(255) DEFAULT NULL,
    `quantity` INT NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `total` DECIMAL(10,2) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_order_item_order` (`order_id`),
    INDEX `idx_order_item_product` (`product_id`),
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Sample Data
-- ============================================================

-- Insert sample categories if not exist (for slug reference)
UPDATE `categories` SET `slug` = 'mobiles' WHERE `name` = 'Mobiles';
UPDATE `categories` SET `slug` = 'laptops' WHERE `name` = 'Laptops';
UPDATE `categories` SET `slug` = 'fashion' WHERE `name` = 'Fashion';
UPDATE `categories` SET `slug` = 'electronics' WHERE `name` = 'Electronics';
UPDATE `categories` SET `slug` = 'home-furniture' WHERE `name` = 'Home & Furniture';
UPDATE `categories` SET `slug` = 'beauty' WHERE `name` = 'Beauty';
UPDATE `categories` SET `slug` = 'books' WHERE `name` = 'Books';
UPDATE `categories` SET `slug` = 'sports' WHERE `name` = 'Sports';

-- Update existing products with original price and discount
UPDATE `products` SET `original_price` = `price` * 1.15, `discount` = 13, `rating` = 4.3, `reviews` = FLOOR(RAND() * 5000) + 100 WHERE `original_price` IS NULL;

-- Assign product-specific images based on name
UPDATE `products` SET `image` = 'iphone15promax.png' WHERE `name` LIKE '%iPhone 15 Pro Max%' AND (`image` IS NULL OR `image` = '');
UPDATE `products` SET `image` = 'samsung_s24u.png' WHERE `name` LIKE '%Samsung Galaxy S24 Ultra%' AND (`image` IS NULL OR `image` = '');
UPDATE `products` SET `image` = 'oneplus12.png' WHERE `name` LIKE '%OnePlus 12%' AND (`image` IS NULL OR `image` = '');
UPDATE `products` SET `image` = 'macbook_air_m3.png' WHERE `name` LIKE '%MacBook Air M3%' AND (`image` IS NULL OR `image` = '');
UPDATE `products` SET `image` = 'dell_xps15.png' WHERE `name` LIKE '%Dell XPS 15%' AND (`image` IS NULL OR `image` = '');
UPDATE `products` SET `image` = 'cotton_tshirt.png' WHERE `name` LIKE '%Cotton T-Shirt%' AND (`image` IS NULL OR `image` = '');
UPDATE `products` SET `image` = 'sony_wh1000xm5.png' WHERE `name` LIKE '%Sony WH-1000XM5%' AND (`image` IS NULL OR `image` = '');
UPDATE `products` SET `image` = 'jbl_flip6.png' WHERE `name` LIKE '%JBL Flip 6%' AND (`image` IS NULL OR `image` = '');

-- Fallback: set placeholder image for any remaining products without one
UPDATE `products` SET `image` = 'placeholder.png' WHERE `image` IS NULL OR `image` = '';

-- Set featured products
UPDATE `products` SET `featured` = 1 ORDER BY `id` ASC LIMIT 4;

-- Insert a test customer (password: Test@123)
INSERT INTO `customers` (`full_name`, `username`, `email`, `phone`, `password`) VALUES
('Test User', 'testuser', 'test@example.com', '9876543210', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE `full_name` = `full_name`;
