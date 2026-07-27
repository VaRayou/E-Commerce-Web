-- ============================================================
-- E-Commerce Database Setup
-- Compatible with existing tables: categorytbl, producttbl
-- ============================================================

CREATE DATABASE IF NOT EXISTS `db_sv13.23`;
USE `db_sv13.23`;

-- ============================================================
-- EXISTING TABLES (Keep as-is for backward compatibility)
-- ============================================================

CREATE TABLE IF NOT EXISTS `categorytbl` (
    `cateid` INT AUTO_INCREMENT PRIMARY KEY,
    `catename` VARCHAR(255) NOT NULL,
    `catelevel` VARCHAR(50) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `producttbl` (
    `productid` INT AUTO_INCREMENT PRIMARY KEY,
    `productname` VARCHAR(255) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `discount` INT DEFAULT 0,
    `cateid` INT DEFAULT NULL,
    `photo1` VARCHAR(255) DEFAULT NULL,
    `photo2` VARCHAR(255) DEFAULT NULL,
    `photo3` VARCHAR(255) DEFAULT NULL,
    `description` TEXT DEFAULT NULL,
    `stock` INT DEFAULT 0,
    `brand` VARCHAR(100) DEFAULT NULL,
    `sku` VARCHAR(50) DEFAULT NULL,
    `status` TINYINT DEFAULT 1,
    `is_featured` TINYINT DEFAULT 0,
    `is_new` TINYINT DEFAULT 0,
    `sales_count` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`cateid`) REFERENCES `categorytbl`(`cateid`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- NEW TABLES
-- ============================================================

-- Users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `address` TEXT DEFAULT NULL,
    `city` VARCHAR(100) DEFAULT NULL,
    `state` VARCHAR(100) DEFAULT NULL,
    `zip_code` VARCHAR(20) DEFAULT NULL,
    `country` VARCHAR(100) DEFAULT 'Cambodia',
    `role` ENUM('admin', 'customer') DEFAULT 'customer',
    `avatar` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin user (username: admin, password: admin123)
INSERT INTO `users` (`first_name`, `last_name`, `username`, `email`, `password`, `role`) VALUES
('Admin', 'User', 'admin', 'admin@setec.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Product categories (brand-level, for filtering)
CREATE TABLE IF NOT EXISTS `brands` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `logo` VARCHAR(255) DEFAULT NULL,
    `status` TINYINT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product attributes (colors, sizes)
CREATE TABLE IF NOT EXISTS `product_attributes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `productid` INT NOT NULL,
    `color` VARCHAR(50) DEFAULT NULL,
    `size` VARCHAR(50) DEFAULT NULL,
    `stock` INT DEFAULT 0,
    `sku` VARCHAR(50) DEFAULT NULL,
    FOREIGN KEY (`productid`) REFERENCES `producttbl`(`productid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Shopping cart
CREATE TABLE IF NOT EXISTS `cart` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `session_id` VARCHAR(255) DEFAULT NULL,
    `productid` INT NOT NULL,
    `quantity` INT DEFAULT 1,
    `color` VARCHAR(50) DEFAULT NULL,
    `size` VARCHAR(50) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`productid`) REFERENCES `producttbl`(`productid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Wishlist
CREATE TABLE IF NOT EXISTS `wishlist` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `session_id` VARCHAR(255) DEFAULT NULL,
    `productid` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`productid`) REFERENCES `producttbl`(`productid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Orders
CREATE TABLE IF NOT EXISTS `orders` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT DEFAULT NULL,
    `order_number` VARCHAR(50) NOT NULL UNIQUE,
    `first_name` VARCHAR(100) NOT NULL,
    `last_name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `address` TEXT NOT NULL,
    `city` VARCHAR(100) NOT NULL,
    `state` VARCHAR(100) DEFAULT NULL,
    `zip_code` VARCHAR(20) DEFAULT NULL,
    `country` VARCHAR(100) DEFAULT 'Cambodia',
    `subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `shipping_cost` DECIMAL(10,2) DEFAULT 0,
    `discount_amount` DECIMAL(10,2) DEFAULT 0,
    `total` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `coupon_code` VARCHAR(50) DEFAULT NULL,
    `payment_method` VARCHAR(50) DEFAULT 'cod',
    `payment_status` ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    `order_status` ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    `notes` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Order items
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `order_id` INT NOT NULL,
    `productid` INT NOT NULL,
    `product_name` VARCHAR(255) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL,
    `quantity` INT NOT NULL DEFAULT 1,
    `color` VARCHAR(50) DEFAULT NULL,
    `size` VARCHAR(50) DEFAULT NULL,
    `total` DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`productid`) REFERENCES `producttbl`(`productid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Reviews
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `productid` INT NOT NULL,
    `rating` TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `comment` TEXT DEFAULT NULL,
    `status` TINYINT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`productid`) REFERENCES `producttbl`(`productid`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Coupons
CREATE TABLE IF NOT EXISTS `coupons` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL UNIQUE,
    `type` ENUM('percentage', 'fixed') DEFAULT 'percentage',
    `value` DECIMAL(10,2) NOT NULL,
    `min_purchase` DECIMAL(10,2) DEFAULT 0,
    `max_uses` INT DEFAULT NULL,
    `used_count` INT DEFAULT 0,
    `start_date` DATE DEFAULT NULL,
    `end_date` DATE DEFAULT NULL,
    `is_active` TINYINT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Site settings
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default settings
INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('site_name', 'WE YOUNG Shop'),
('site_tagline', 'Your Fashion Destination'),
('site_email', 'info@setec.com'),
('site_phone', '+855 12 345 678'),
('site_address', 'Phnom Penh, Cambodia'),
('currency', 'USD'),
('currency_symbol', '$'),
('shipping_cost', '5.00'),
('free_shipping_min', '100.00');
