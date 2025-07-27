-- MySQL Database Setup for AgoraCart
-- Drop existing tables if they exist (in correct order due to foreign key constraints)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart_items;
DROP TABLE IF EXISTS product_reviews;
DROP TABLE IF EXISTS wishlists;
DROP TABLE IF EXISTS user_addresses;
DROP TABLE IF EXISTS product_attributes;
DROP TABLE IF EXISTS product_images;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- Create Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2),
    stock_quantity INT DEFAULT 0,
    subcategory VARCHAR(100),
    brand VARCHAR(100),
    sku VARCHAR(100) UNIQUE,
    is_featured BOOLEAN DEFAULT false,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- Create Product Images table
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    image_url VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255),
    is_primary BOOLEAN DEFAULT false,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Create Product Attributes table (for sizes, colors, etc.)
CREATE TABLE product_attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    attribute_name VARCHAR(50) NOT NULL,
    attribute_value VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Create User Addresses table
CREATE TABLE user_addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    type VARCHAR(20) DEFAULT 'shipping',
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    company VARCHAR(100),
    address_line_1 VARCHAR(255) NOT NULL,
    address_line_2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100),
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    is_default BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create Cart Items table
CREATE TABLE cart_items (
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

-- Create Orders table
CREATE TABLE orders (
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
    shipping_address_id INT,
    billing_address_id INT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (shipping_address_id) REFERENCES user_addresses(id),
    FOREIGN KEY (billing_address_id) REFERENCES user_addresses(id)
);

-- Create Order Items table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    size VARCHAR(50),
    color VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Create Product Reviews table
CREATE TABLE product_reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    user_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(255),
    review_text TEXT,
    is_verified_purchase BOOLEAN DEFAULT false,
    is_approved BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create Wishlists table
CREATE TABLE wishlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    product_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Create indexes for better performance
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_featured ON products(is_featured);
CREATE INDEX idx_products_active ON products(is_active);
CREATE INDEX idx_product_images_product ON product_images(product_id);
CREATE INDEX idx_product_images_primary ON product_images(is_primary);
CREATE INDEX idx_product_attributes_product ON product_attributes(product_id);
CREATE INDEX idx_cart_items_user ON cart_items(user_id);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_order_items_order ON order_items(order_id);
CREATE INDEX idx_reviews_product ON product_reviews(product_id);
CREATE INDEX idx_wishlists_user ON wishlists(user_id);

-- Insert sample categories
INSERT INTO categories (name, description, image_url) VALUES
('Girls', 'Fashion and clothing for girls', 'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?w=500'),
('Boys', 'Fashion and clothing for boys', 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=500'),
('Infants', 'Clothing and accessories for infants', 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=500');

-- Insert sample users (password is 'password' hashed)
INSERT INTO users (first_name, last_name, email, password_hash, phone) VALUES
('John', 'Doe', 'john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567890'),
('Jane', 'Smith', 'jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567891');

-- Insert sample products
INSERT INTO products (category_id, name, description, price, discount_price, stock_quantity, subcategory, brand, sku, is_featured) VALUES
-- Girls products
(1, 'Floral Summer Dress', 'Beautiful floral print summer dress for girls', 29.99, 24.99, 50, 'Dresses', 'AgoraCart', 'AC-GD-001', true),
(1, 'Princess Party Dress', 'Elegant party dress perfect for special occasions', 45.99, 39.99, 25, 'Dresses', 'AgoraCart', 'AC-GD-002', true),
(1, 'Casual T-Shirt Set', 'Comfortable cotton t-shirt and shorts set', 19.99, null, 75, 'Sets', 'AgoraCart', 'AC-GS-001', false),
(1, 'Denim Jacket', 'Stylish denim jacket for girls', 34.99, 29.99, 30, 'Outerwear', 'AgoraCart', 'AC-GJ-001', false),

-- Boys products
(2, 'Superhero T-Shirt', 'Cool superhero themed t-shirt', 15.99, null, 60, 'T-Shirts', 'AgoraCart', 'AC-BT-001', true),
(2, 'Cargo Shorts', 'Comfortable cargo shorts with multiple pockets', 22.99, 19.99, 40, 'Shorts', 'AgoraCart', 'AC-BS-001', false),
(2, 'Formal Shirt', 'Elegant formal shirt for special occasions', 32.99, null, 35, 'Shirts', 'AgoraCart', 'AC-BS-002', false),
(2, 'Sports Tracksuit', 'Comfortable tracksuit for sports activities', 39.99, 34.99, 20, 'Sets', 'AgoraCart', 'AC-BTS-001', true),

-- Infant products
(3, 'Baby Onesie Set', 'Soft cotton onesie set for babies', 24.99, 21.99, 80, 'Onesies', 'AgoraCart', 'AC-IO-001', true),
(3, 'Infant Sleep Sack', 'Cozy sleep sack for comfortable sleep', 18.99, null, 45, 'Sleepwear', 'AgoraCart', 'AC-IS-001', false);

-- Insert product images
INSERT INTO product_images (product_id, image_url, alt_text, is_primary, sort_order) VALUES
-- Floral Summer Dress
(1, 'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?w=500', 'Floral Summer Dress - Front View', true, 1),
(1, 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=500', 'Floral Summer Dress - Side View', false, 2),

-- Princess Party Dress
(2, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=500', 'Princess Party Dress - Front View', true, 1),
(2, 'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?w=500', 'Princess Party Dress - Detail View', false, 2),

-- Casual T-Shirt Set
(3, 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=500', 'Casual T-Shirt Set', true, 1),

-- Denim Jacket
(4, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=500', 'Denim Jacket - Front View', true, 1),

-- Superhero T-Shirt
(5, 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=500', 'Superhero T-Shirt', true, 1),

-- Cargo Shorts
(6, 'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?w=500', 'Cargo Shorts', true, 1),

-- Formal Shirt
(7, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=500', 'Formal Shirt', true, 1),

-- Sports Tracksuit
(8, 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=500', 'Sports Tracksuit', true, 1),

-- Baby Onesie Set
(9, 'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?w=500', 'Baby Onesie Set', true, 1),

-- Infant Sleep Sack
(10, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=500', 'Infant Sleep Sack', true, 1);

-- Insert product attributes (sizes and colors)
INSERT INTO product_attributes (product_id, attribute_name, attribute_value) VALUES
-- Girls products sizes and colors
(1, 'size', '2T'), (1, 'size', '3T'), (1, 'size', '4T'), (1, 'size', '5T'),
(1, 'color', 'Pink'), (1, 'color', 'Blue'), (1, 'color', 'Yellow'),
(2, 'size', '2T'), (2, 'size', '3T'), (2, 'size', '4T'), (2, 'size', '5T'), (2, 'size', '6T'),
(2, 'color', 'Purple'), (2, 'color', 'Pink'), (2, 'color', 'White'),
(3, 'size', '2T'), (3, 'size', '3T'), (3, 'size', '4T'),
(3, 'color', 'Red'), (3, 'color', 'Blue'), (3, 'color', 'Green'),
(4, 'size', '3T'), (4, 'size', '4T'), (4, 'size', '5T'), (4, 'size', '6T'),
(4, 'color', 'Blue'), (4, 'color', 'Black'),

-- Boys products sizes and colors
(5, 'size', '2T'), (5, 'size', '3T'), (5, 'size', '4T'), (5, 'size', '5T'),
(5, 'color', 'Red'), (5, 'color', 'Blue'), (5, 'color', 'Black'),
(6, 'size', '3T'), (6, 'size', '4T'), (6, 'size', '5T'), (6, 'size', '6T'),
(6, 'color', 'Khaki'), (6, 'color', 'Navy'), (6, 'color', 'Green'),
(7, 'size', '3T'), (7, 'size', '4T'), (7, 'size', '5T'), (7, 'size', '6T'),
(7, 'color', 'White'), (7, 'color', 'Light Blue'), (7, 'color', 'Navy'),
(8, 'size', '2T'), (8, 'size', '3T'), (8, 'size', '4T'), (8, 'size', '5T'),
(8, 'color', 'Gray'), (8, 'color', 'Navy'), (8, 'color', 'Black'),

-- Infant products sizes and colors
(9, 'size', '0-3M'), (9, 'size', '3-6M'), (9, 'size', '6-9M'), (9, 'size', '9-12M'),
(9, 'color', 'White'), (9, 'color', 'Pink'), (9, 'color', 'Blue'), (9, 'color', 'Yellow'),
(10, 'size', '0-6M'), (10, 'size', '6-12M'), (10, 'size', '12-18M'),
(10, 'color', 'Cream'), (10, 'color', 'Gray'), (10, 'color', 'Mint');

-- Insert sample addresses for users
INSERT INTO user_addresses (user_id, type, first_name, last_name, address_line_1, city, state, postal_code, country, phone, is_default) VALUES
(1, 'shipping', 'John', 'Doe', '123 Main Street', 'New York', 'NY', '10001', 'USA', '+1234567890', true),
(1, 'billing', 'John', 'Doe', '123 Main Street', 'New York', 'NY', '10001', 'USA', '+1234567890', true),
(2, 'shipping', 'Jane', 'Smith', '456 Oak Avenue', 'Los Angeles', 'CA', '90210', 'USA', '+1234567891', true),
(2, 'billing', 'Jane', 'Smith', '456 Oak Avenue', 'Los Angeles', 'CA', '90210', 'USA', '+1234567891', true);

-- Insert sample reviews
INSERT INTO product_reviews (product_id, user_id, rating, title, review_text, is_verified_purchase) VALUES
(1, 1, 5, 'Beautiful dress!', 'My daughter loves this dress. The quality is excellent and the fit is perfect.', true),
(1, 2, 4, 'Great quality', 'Nice dress, good material. Only wish it came in more colors.', true),
(2, 1, 5, 'Perfect for parties', 'This dress is gorgeous! Got so many compliments at the birthday party.', true),
(5, 2, 5, 'Son loves it!', 'Great superhero design, good quality cotton. Washes well too.', true),
(9, 1, 4, 'Soft and comfortable', 'Baby seems very comfortable in these onesies. Good value for money.', true);

-- Insert sample cart items (optional - for testing)
INSERT INTO cart_items (user_id, product_id, quantity, size, color) VALUES
(1, 1, 1, '3T', 'Pink'),
(1, 5, 2, '4T', 'Blue'),
(2, 2, 1, '4T', 'Purple');

-- Display completion message
SELECT 'MySQL Database setup completed successfully!' as message;
