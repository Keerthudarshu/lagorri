-- MySQL Database Setup for AgoraCart eCommerce
-- Run this script in your MySQL database

CREATE DATABASE IF NOT EXISTS agoracart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agoracart;

-- Create Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2),
    original_price DECIMAL(10,2),
    stock_quantity INT DEFAULT 0,
    subcategory VARCHAR(100),
    brand VARCHAR(100),
    sku VARCHAR(100) UNIQUE,
    is_featured BOOLEAN DEFAULT FALSE,
    is_new_arrival BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Create Product Images table
CREATE TABLE IF NOT EXISTS product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    image_url VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255),
    is_primary BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Create Product Attributes table (for sizes, colors, etc.)
CREATE TABLE IF NOT EXISTS product_attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    attribute_name VARCHAR(50) NOT NULL,
    attribute_value VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Create Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    total_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    shipping_amount DECIMAL(10,2) DEFAULT 0,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    payment_status VARCHAR(50) DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_id VARCHAR(255),
    razorpay_order_id VARCHAR(255),
    razorpay_payment_id VARCHAR(255),
    razorpay_signature VARCHAR(255),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create OTP verification table
CREATE TABLE IF NOT EXISTS user_otp_verification (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255),
    phone VARCHAR(20),
    email_otp VARCHAR(6),
    phone_otp VARCHAR(6),
    email_otp_expires_at TIMESTAMP,
    phone_otp_expires_at TIMESTAMP,
    email_verified BOOLEAN DEFAULT FALSE,
    phone_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create Cart Items table
CREATE TABLE IF NOT EXISTS cart_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    quantity INT NOT NULL DEFAULT 1,
    size VARCHAR(50),
    color VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Insert sample categories
INSERT INTO categories (name, description, image_url) VALUES
('Girls', 'Fashion and clothing for girls', 'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?w=500&h=500&fit=crop&crop=face'),
('Boys', 'Fashion and clothing for boys', 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=500&h=500&fit=crop&crop=face'),
('Infants', 'Clothing and accessories for infants', 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=500&h=500&fit=crop&crop=face')
ON DUPLICATE KEY UPDATE name=name;

-- Insert sample products
INSERT INTO products (category_id, name, description, price, discount_price, original_price, stock_quantity, subcategory, brand, sku, is_featured, is_new_arrival) VALUES
-- Girls products
(1, 'Floral Summer Dress', 'Beautiful floral print summer dress for girls', 24.99, NULL, 29.99, 50, 'Dresses', 'Lagorii Kids', 'LK-GD-001', TRUE, TRUE),
(1, 'Princess Party Dress', 'Elegant party dress perfect for special occasions', 39.99, NULL, 45.99, 25, 'Dresses', 'Lagorii Kids', 'LK-GD-002', TRUE, FALSE),
(1, 'Casual T-Shirt Set', 'Comfortable cotton t-shirt and shorts set', 19.99, NULL, NULL, 75, 'Sets', 'Lagorii Kids', 'LK-GS-001', FALSE, TRUE),
(1, 'Denim Jacket', 'Stylish denim jacket for girls', 29.99, NULL, 34.99, 30, 'Outerwear', 'Lagorii Kids', 'LK-GJ-001', FALSE, FALSE),
-- Boys products
(2, 'Superhero T-Shirt', 'Cool superhero themed t-shirt', 15.99, NULL, NULL, 60, 'T-Shirts', 'Lagorii Kids', 'LK-BT-001', TRUE, TRUE),
(2, 'Cargo Shorts', 'Comfortable cargo shorts with multiple pockets', 19.99, NULL, 22.99, 40, 'Shorts', 'Lagorii Kids', 'LK-BS-001', FALSE, FALSE),
(2, 'Formal Shirt', 'Elegant formal shirt for special occasions', 32.99, NULL, NULL, 35, 'Shirts', 'Lagorii Kids', 'LK-BS-002', FALSE, FALSE),
(2, 'Sports Tracksuit', 'Comfortable tracksuit for sports activities', 34.99, NULL, 39.99, 20, 'Sets', 'Lagorii Kids', 'LK-BTS-001', TRUE, TRUE),
-- Infant products
(3, 'Baby Onesie Set', 'Soft cotton onesie set for babies', 21.99, NULL, 24.99, 80, 'Onesies', 'Lagorii Kids', 'LK-IO-001', TRUE, TRUE),
(3, 'Infant Sleep Sack', 'Cozy sleep sack for comfortable sleep', 18.99, NULL, NULL, 45, 'Sleepwear', 'Lagorii Kids', 'LK-IS-001', FALSE, FALSE)
ON DUPLICATE KEY UPDATE name=name;

-- Insert product images
INSERT INTO product_images (product_id, image_url, alt_text, is_primary, sort_order) VALUES
(1, 'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?w=600&h=600&fit=crop&crop=face', 'Floral Summer Dress - Front View', TRUE, 1),
(2, 'https://images.unsplash.com/photo-1566400619465-8a82b4ba1ba3?w=600&h=600&fit=crop&crop=face', 'Princess Party Dress - Front View', TRUE, 1),
(3, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=600&h=600&fit=crop&crop=face', 'Casual T-Shirt Set - Front View', TRUE, 1),
(4, 'https://images.unsplash.com/photo-1545558014-8692077e9b5c?w=600&h=600&fit=crop&crop=face', 'Denim Jacket - Front View', TRUE, 1),
(5, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=600&h=600&fit=crop&crop=face', 'Superhero T-Shirt - Front View', TRUE, 1),
(6, 'https://images.unsplash.com/photo-1566400619465-8a82b4ba1ba3?w=600&h=600&fit=crop&crop=face', 'Cargo Shorts - Front View', TRUE, 1),
(7, 'https://images.unsplash.com/photo-1545558014-8692077e9b5c?w=600&h=600&fit=crop&crop=face', 'Formal Shirt - Front View', TRUE, 1),
(8, 'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=600&h=600&fit=crop&crop=face', 'Sports Tracksuit - Front View', TRUE, 1),
(9, 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=600&h=600&fit=crop&crop=face', 'Baby Onesie Set - Front View', TRUE, 1),
(10, 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=600&h=600&fit=crop&crop=face', 'Infant Sleep Sack - Front View', TRUE, 1);

-- Insert sample product attributes
INSERT INTO product_attributes (product_id, attribute_name, attribute_value) VALUES
-- Sizes for products
(1, 'size', 'XS'), (1, 'size', 'S'), (1, 'size', 'M'), (1, 'size', 'L'),
(2, 'size', 'XS'), (2, 'size', 'S'), (2, 'size', 'M'), (2, 'size', 'L'),
(3, 'size', 'XS'), (3, 'size', 'S'), (3, 'size', 'M'), (3, 'size', 'L'),
(4, 'size', 'XS'), (4, 'size', 'S'), (4, 'size', 'M'), (4, 'size', 'L'),
(5, 'size', 'XS'), (5, 'size', 'S'), (5, 'size', 'M'), (5, 'size', 'L'),
(6, 'size', 'XS'), (6, 'size', 'S'), (6, 'size', 'M'), (6, 'size', 'L'),
(7, 'size', 'XS'), (7, 'size', 'S'), (7, 'size', 'M'), (7, 'size', 'L'),
(8, 'size', 'XS'), (8, 'size', 'S'), (8, 'size', 'M'), (8, 'size', 'L'),
(9, 'size', '0-3M'), (9, 'size', '3-6M'), (9, 'size', '6-12M'),
(10, 'size', '0-3M'), (10, 'size', '3-6M'), (10, 'size', '6-12M'),
-- Colors for products
(1, 'color', 'Pink'), (1, 'color', 'Blue'), (1, 'color', 'White'),
(2, 'color', 'Purple'), (2, 'color', 'Pink'), (2, 'color', 'Gold'),
(3, 'color', 'Red'), (3, 'color', 'Blue'), (3, 'color', 'Green'),
(4, 'color', 'Blue'), (4, 'color', 'Black'),
(5, 'color', 'Red'), (5, 'color', 'Blue'), (5, 'color', 'Black'),
(6, 'color', 'Khaki'), (6, 'color', 'Navy'), (6, 'color', 'Black'),
(7, 'color', 'White'), (7, 'color', 'Blue'), (7, 'color', 'Black'),
(8, 'color', 'Navy'), (8, 'color', 'Gray'), (8, 'color', 'Black'),
(9, 'color', 'Pink'), (9, 'color', 'Blue'), (9, 'color', 'Yellow'),
(10, 'color', 'White'), (10, 'color', 'Gray'), (10, 'color', 'Pink');