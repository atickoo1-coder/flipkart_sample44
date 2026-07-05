-- ============================================================
-- E-Commerce Admin Panel Database Schema
-- ============================================================
-- Database: ecommerce_db
-- Created for: quickkart_sample admin panel
-- ============================================================

CREATE DATABASE IF NOT EXISTS `ecommerce_db`;
USE `ecommerce_db`;

-- ============================================================
-- Table: admins
-- Stores admin user credentials for authentication
-- ============================================================
CREATE TABLE IF NOT EXISTS `admins` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: categories
-- Stores product categories
-- ============================================================
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Table: products
-- Stores all product information with image upload support
-- ============================================================
CREATE TABLE IF NOT EXISTS `products` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `category_id` INT(11) NOT NULL,
    `name` VARCHAR(200) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `brand` VARCHAR(100) DEFAULT NULL,
    `product_url` VARCHAR(500) DEFAULT NULL COMMENT 'External product website URL',
    `stock_quantity` INT(11) NOT NULL DEFAULT 0,
    `image` VARCHAR(255) DEFAULT NULL,
    `multiple_images` TEXT DEFAULT NULL COMMENT 'Comma-separated image filenames',
    `status` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `category_id` (`category_id`),
    CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Sample Data
-- ============================================================

-- Insert a default admin (password: admin123)
INSERT INTO `admins` (`username`, `email`, `password`) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Insert sample categories
INSERT INTO `categories` (`name`, `description`, `status`) VALUES
('Mobiles', 'Mobile phones and accessories', 1),
('Laptops', 'Laptop computers and accessories', 1),
('Fashion', 'Clothing, footwear and accessories', 1),
('Electronics', 'Electronic gadgets and devices', 1),
('Home & Furniture', 'Home decor and furniture items', 1),
('Beauty', 'Beauty and personal care products', 1),
('Books', 'Books and educational materials', 1),
('Sports', 'Sports equipment and fitness gear', 1);

-- Insert sample products
INSERT INTO `products` (`category_id`, `name`, `description`, `price`, `brand`, `stock_quantity`, `image`, `status`) VALUES
(1, 'iPhone 15 Pro Max', 'Apple iPhone 15 Pro Max with A17 Pro chip, 48MP camera system', 159999.00, 'Apple', 25, 'iphone15promax.png', 1),
(1, 'Samsung Galaxy S24 Ultra', 'Samsung Galaxy S24 Ultra with AI features and S Pen', 134999.00, 'Samsung', 30, 'samsung_s24u.png', 1),
(1, 'OnePlus 12', 'OnePlus 12 with Snapdragon 8 Gen 3 processor', 69999.00, 'OnePlus', 40, 'oneplus12.png', 1),
(2, 'MacBook Air M3', 'Apple MacBook Air with M3 chip, 15-inch display', 134900.00, 'Apple', 15, 'macbook_air_m3.png', 1),
(2, 'Dell XPS 15', 'Dell XPS 15 with Intel Core i7 and NVIDIA RTX 4060', 189990.00, 'Dell', 10, 'dell_xps15.png', 1),
(3, 'Cotton T-Shirt', 'Premium quality cotton t-shirt available in multiple colors', 799.00, 'Nike', 200, 'cotton_tshirt.png', 1),
(4, 'Sony WH-1000XM5', 'Sony wireless noise cancellation headphones', 29990.00, 'Sony', 50, 'sony_wh1000xm5.png', 1),
(4, 'JBL Flip 6', 'JBL Flip 6 portable Bluetooth speaker', 12999.00, 'JBL', 75, 'jbl_flip6.png', 1);
