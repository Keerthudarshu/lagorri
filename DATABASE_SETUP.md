# Database Setup and Connection Guide

## Project Structure

The Lagorii Kids eCommerce website is now configured to use **PostgreSQL database** instead of JSON files.

## Database Connection

The database is automatically configured using environment variables:
- **DATABASE_URL**: Complete PostgreSQL connection string
- **PGHOST**: Database host
- **PGPORT**: Database port (default: 5432)
- **PGDATABASE**: Database name
- **PGUSER**: Database username
- **PGPASSWORD**: Database password

## Database Schema

The database contains the following tables:

### Core Tables
1. **categories** - Product categories (Girls, Boys, Infants)
2. **products** - Product catalog with details
3. **product_images** - Product image URLs
4. **product_attributes** - Sizes, colors, and other product variants
5. **users** - User accounts and authentication
6. **user_addresses** - Shipping and billing addresses

### Shopping Tables
7. **cart_items** - Shopping cart functionality
8. **orders** - Order management
9. **order_items** - Individual order line items
10. **product_reviews** - Customer reviews
11. **wishlists** - User wishlist functionality

## Sample Data Included

The database is pre-populated with:
- **3 categories**: Girls, Boys, Infants
- **10 products** with images, sizes, and colors
- **2 sample users** for testing

### Test User Credentials
- Email: `john.doe@example.com`
- Email: `jane.smith@example.com`
- Password: `password` (for both accounts)

## Key Features

### Product Management
- Full product catalog with categories
- Product images and variants (sizes/colors)
- Stock management
- Featured products and new arrivals
- Price and discount management

### Shopping Cart
- Add/remove items
- Quantity management
- Bulk discount system:
  - 5% discount for 2 items
  - 10% discount for 3+ items

### User Authentication
- Registration and login
- Session management
- Password hashing (secure)

### Payment Integration
- Razorpay payment gateway integration
- Order management system

## Database Functions Available

The `config.php` file provides these functions:
- `getProducts($categoryId, $limit, $featured)` - Get products with filters
- `getProductById($id)` - Get single product with details
- `getCategories()` - Get all categories
- `searchProducts($query, $limit)` - Search products
- `authenticateUser($email, $password)` - User login
- `createUser($userData)` - User registration

## Project Path

The project files are located in the root directory:
- `/` - Main project folder
- `/config.php` - Database configuration
- `/index.php` - Homepage
- `/pages/` - Product pages, cart, checkout
- `/api/` - API endpoints
- `/assets/` - CSS, JavaScript, images

## Accessing the Website

The website runs on **PHP server** at `http://localhost:5000`

Main pages:
- **Homepage**: `/` - Product showcase and categories
- **Products**: `/pages/products.php` - Product listing with filters
- **Product Detail**: `/pages/product-detail.php?id=X` - Individual product
- **Cart**: `/pages/cart.php` - Shopping cart
- **Checkout**: `/pages/checkout.php` - Order placement
- **Authentication**: `/pages/auth.php` - Login/Register

## Razorpay Integration

Payment processing is integrated with Razorpay:
- Test mode enabled
- Email: `keerthudarshu06@gmail.com`
- Contact: `7892783668`

Update the Razorpay keys in `config.php`:
```php
define('RAZORPAY_KEY_ID', 'your_key_id');
define('RAZORPAY_KEY_SECRET', 'your_secret_key');
```

## Tech Stack

- **Backend**: PHP 8.4 with PostgreSQL
- **Frontend**: HTML5, CSS3, Bootstrap 5, Vanilla JavaScript
- **Database**: PostgreSQL with proper indexing
- **Payment**: Razorpay integration
- **Features**: Responsive design, real-time cart, user sessions

The website is production-ready with proper security measures, database optimization, and complete eCommerce functionality.