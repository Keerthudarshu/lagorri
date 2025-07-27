-- PostgreSQL Database Setup for AgoraCart
-- Drop existing tables if they exist (in correct order due to foreign key constraints)
DROP TABLE IF EXISTS order_items CASCADE;
DROP TABLE IF EXISTS orders CASCADE;
DROP TABLE IF EXISTS cart_items CASCADE;
DROP TABLE IF EXISTS product_reviews CASCADE;
DROP TABLE IF EXISTS wishlists CASCADE;
DROP TABLE IF EXISTS user_addresses CASCADE;
DROP TABLE IF EXISTS product_attributes CASCADE;
DROP TABLE IF EXISTS product_images CASCADE;
DROP TABLE IF EXISTS products CASCADE;
DROP TABLE IF EXISTS categories CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Create Categories table
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image_url VARCHAR(500),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Users table
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    date_of_birth DATE,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Products table
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    category_id INTEGER REFERENCES categories(id),
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2),
    original_price DECIMAL(10,2),
    stock_quantity INTEGER DEFAULT 0,
    subcategory VARCHAR(100),
    brand VARCHAR(100),
    sku VARCHAR(100) UNIQUE,
    is_featured BOOLEAN DEFAULT false,
    is_new_arrival BOOLEAN DEFAULT false,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Product Images table
CREATE TABLE product_images (
    id SERIAL PRIMARY KEY,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    image_url VARCHAR(500) NOT NULL,
    alt_text VARCHAR(255),
    is_primary BOOLEAN DEFAULT false,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Product Attributes table (for sizes, colors, etc.)
CREATE TABLE product_attributes (
    id SERIAL PRIMARY KEY,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    attribute_name VARCHAR(50) NOT NULL,
    attribute_value VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create User Addresses table
CREATE TABLE user_addresses (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Cart Items table
CREATE TABLE cart_items (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    quantity INTEGER NOT NULL DEFAULT 1,
    size VARCHAR(50),
    color VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Orders table
CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
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
    shipping_address_id INTEGER REFERENCES user_addresses(id),
    billing_address_id INTEGER REFERENCES user_addresses(id),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Order Items table
CREATE TABLE order_items (
    id SERIAL PRIMARY KEY,
    order_id INTEGER REFERENCES orders(id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES products(id),
    quantity INTEGER NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    size VARCHAR(50),
    color VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Product Reviews table
CREATE TABLE product_reviews (
    id SERIAL PRIMARY KEY,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    rating INTEGER CHECK (rating >= 1 AND rating <= 5),
    title VARCHAR(255),
    review_text TEXT,
    is_verified_purchase BOOLEAN DEFAULT false,
    is_approved BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create Wishlists table
CREATE TABLE wishlists (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES products(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (user_id, product_id)
);

-- Create OTP table for verification
CREATE TABLE user_otp_verification (
    id SERIAL PRIMARY KEY,
    email VARCHAR(255),
    phone VARCHAR(20),
    email_otp VARCHAR(6),
    phone_otp VARCHAR(6),
    email_otp_expires_at TIMESTAMP,
    phone_otp_expires_at TIMESTAMP,
    email_verified BOOLEAN DEFAULT false,
    phone_verified BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
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

-- Function to update updated_at timestamps
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Create triggers for updated_at
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();
CREATE TRIGGER update_products_updated_at BEFORE UPDATE ON products FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();
CREATE TRIGGER update_cart_items_updated_at BEFORE UPDATE ON cart_items FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();
CREATE TRIGGER update_orders_updated_at BEFORE UPDATE ON orders FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();
CREATE TRIGGER update_otp_updated_at BEFORE UPDATE ON user_otp_verification FOR EACH ROW EXECUTE PROCEDURE update_updated_at_column();

-- Insert sample categories
INSERT INTO categories (name, description, image_url) VALUES
('Girls', 'Fashion and clothing for girls', 'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?w=500&h=500&fit=crop&crop=face'),
('Boys', 'Fashion and clothing for boys', 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=500&h=500&fit=crop&crop=face'),
('Infants', 'Clothing and accessories for infants', 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=500&h=500&fit=crop&crop=face');

-- Insert sample users (password is 'password' hashed)
INSERT INTO users (first_name, last_name, email, password_hash, phone) VALUES
('John', 'Doe', 'john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567890'),
('Jane', 'Smith', 'jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567891');

-- Insert sample products
INSERT INTO products (category_id, name, description, price, discount_price, original_price, stock_quantity, subcategory, brand, sku, is_featured, is_new_arrival) VALUES
-- Girls products
(1, 'Floral Summer Dress', 'Beautiful floral print summer dress for girls', 24.99, NULL, 29.99, 50, 'Dresses', 'Lagorii Kids', 'LK-GD-001', true, true),
(1, 'Princess Party Dress', 'Elegant party dress perfect for special occasions', 39.99, NULL, 45.99, 25, 'Dresses', 'Lagorii Kids', 'LK-GD-002', true, false),
(1, 'Casual T-Shirt Set', 'Comfortable cotton t-shirt and shorts set', 19.99, NULL, NULL, 75, 'Sets', 'Lagorii Kids', 'LK-GS-001', false, true),
(1, 'Denim Jacket', 'Stylish denim jacket for girls', 29.99, NULL, 34.99, 30, 'Outerwear', 'Lagorii Kids', 'LK-GJ-001', false, false),

-- Boys products
(2, 'Superhero T-Shirt', 'Cool superhero themed t-shirt', 15.99, NULL, NULL, 60, 'T-Shirts', 'Lagorii Kids', 'LK-BT-001', true, true),
(2, 'Cargo Shorts', 'Comfortable cargo shorts with multiple pockets', 19.99, NULL, 22.99, 40, 'Shorts', 'Lagorii Kids', 'LK-BS-001', false, false),
(2, 'Formal Shirt', 'Elegant formal shirt for special occasions', 32.99, NULL, NULL, 35, 'Shirts', 'Lagorii Kids', 'LK-BS-002', false, false),
(2, 'Sports Tracksuit', 'Comfortable tracksuit for sports activities', 34.99, NULL, 39.99, 20, 'Sets', 'Lagorii Kids', 'LK-BTS-001', true, true),

-- Infant products
(3, 'Baby Onesie Set', 'Soft cotton onesie set for babies', 21.99, NULL, 24.99, 80, 'Onesies', 'Lagorii Kids', 'LK-IO-001', true, true),
(3, 'Infant Sleep Sack', 'Cozy sleep sack for comfortable sleep', 18.99, NULL, NULL, 45, 'Sleepwear', 'Lagorii Kids', 'LK-IS-001', false, false);

-- Insert product images
INSERT INTO product_images (product_id, image_url, alt_text, is_primary, sort_order) VALUES
-- Floral Summer Dress
(1, 'https://images.unsplash.com/photo-1518831959646-742c3a14ebf7?w=600&h=600&fit=crop&crop=face', 'Floral Summer Dress - Front View', true, 1),
(1, 'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=600&h=600&fit=crop&crop=face', 'Floral Summer Dress - Side View', false, 2),

-- Princess Party Dress
(2, 'https://images.unsplash.com/photo-1566400619465-8a82b4ba1ba3?w=600&h=600&fit=crop&crop=face', 'Princess Party Dress - Front View', true, 1),

-- Casual T-Shirt Set
(3, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=600&h=600&fit=crop&crop=face', 'Casual T-Shirt Set - Front View', true, 1),

-- Denim Jacket
(4, 'https://images.unsplash.com/photo-1545558014-8692077e9b5c?w=600&h=600&fit=crop&crop=face', 'Denim Jacket - Front View', true, 1),

-- Superhero T-Shirt
(5, 'https://images.unsplash.com/photo-1503944583220-79d8926ad5e2?w=600&h=600&fit=crop&crop=face', 'Superhero T-Shirt - Front View', true, 1),

-- Cargo Shorts
(6, 'https://images.unsplash.com/photo-1566400619465-8a82b4ba1ba3?w=600&h=600&fit=crop&crop=face', 'Cargo Shorts - Front View', true, 1),

-- Formal Shirt
(7, 'https://images.unsplash.com/photo-1545558014-8692077e9b5c?w=600&h=600&fit=crop&crop=face', 'Formal Shirt - Front View', true, 1),

-- Sports Tracksuit
(8, 'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=600&h=600&fit=crop&crop=face', 'Sports Tracksuit - Front View', true, 1),

-- Baby Onesie Set
(9, 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=600&h=600&fit=crop&crop=face', 'Baby Onesie Set - Front View', true, 1),

-- Infant Sleep Sack
(10, 'https://images.unsplash.com/photo-1515488042361-ee00e0ddd4e4?w=600&h=600&fit=crop&crop=face', 'Infant Sleep Sack - Front View', true, 1);

-- Insert product attributes (sizes and colors)
INSERT INTO product_attributes (product_id, attribute_name, attribute_value) VALUES
-- Girls products sizes and colors
(1, 'size', '2-3Y'), (1, 'size', '4-5Y'), (1, 'size', '6-7Y'), (1, 'size', '8-9Y'),
(1, 'color', 'Pink'), (1, 'color', 'Blue'), (1, 'color', 'White'),
(2, 'size', '2-3Y'), (2, 'size', '4-5Y'), (2, 'size', '6-7Y'),
(2, 'color', 'Purple'), (2, 'color', 'Pink'),
(3, 'size', '2-3Y'), (3, 'size', '4-5Y'), (3, 'size', '6-7Y'),
(3, 'color', 'Red'), (3, 'color', 'Blue'), (3, 'color', 'Green'),
(4, 'size', '4-5Y'), (4, 'size', '6-7Y'), (4, 'size', '8-9Y'),
(4, 'color', 'Blue'), (4, 'color', 'Black'),

-- Boys products sizes and colors
(5, 'size', '2-3Y'), (5, 'size', '4-5Y'), (5, 'size', '6-7Y'), (5, 'size', '8-9Y'),
(5, 'color', 'Red'), (5, 'color', 'Blue'), (5, 'color', 'Black'),
(6, 'size', '4-5Y'), (6, 'size', '6-7Y'), (6, 'size', '8-9Y'),
(6, 'color', 'Khaki'), (6, 'color', 'Navy'),
(7, 'size', '4-5Y'), (7, 'size', '6-7Y'), (7, 'size', '8-9Y'),
(7, 'color', 'White'), (7, 'color', 'Light Blue'),
(8, 'size', '4-5Y'), (8, 'size', '6-7Y'), (8, 'size', '8-9Y'),
(8, 'color', 'Navy'), (8, 'color', 'Black'), (8, 'color', 'Grey'),

-- Infant products sizes and colors
(9, 'size', '0-3M'), (9, 'size', '3-6M'), (9, 'size', '6-12M'),
(9, 'color', 'Pink'), (9, 'color', 'Blue'), (9, 'color', 'White'), (9, 'color', 'Yellow'),
(10, 'size', '0-3M'), (10, 'size', '3-6M'), (10, 'size', '6-12M'),
(10, 'color', 'Cream'), (10, 'color', 'Light Blue');