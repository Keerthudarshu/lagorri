# MySQL Database Setup Instructions

This guide will help you set up the MySQL database for your Lagorii Kids eCommerce website.

## Prerequisites

- MySQL Server (5.7 or higher) or MariaDB
- PHP with PDO MySQL extension
- Web server (Apache/Nginx/PHP built-in server)

## Step 1: Install MySQL (if not already installed)

### For Windows (XAMPP):
1. Download and install XAMPP from https://www.apachefriends.org/
2. Start Apache and MySQL services from XAMPP Control Panel

### For Linux:
```bash
sudo apt update
sudo apt install mysql-server php-mysql
sudo systemctl start mysql
sudo systemctl enable mysql
```

### For Mac:
```bash
brew install mysql
brew services start mysql
```

## Step 2: Create Database and User

1. Login to MySQL as root:
```bash
mysql -u root -p
```

2. Create the database:
```sql
CREATE DATABASE agoracart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Create a user (optional, for production):
```sql
CREATE USER 'agoracart_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON agoracart.* TO 'agoracart_user'@'localhost';
FLUSH PRIVILEGES;
```

## Step 3: Import Database Schema

1. Exit MySQL prompt and run:
```bash
mysql -u root -p agoracart < database_setup_mysql.sql
```

Or if you prefer to use the MySQL prompt:
```bash
mysql -u root -p
USE agoracart;
SOURCE database_setup_mysql.sql;
```

## Step 4: Update Configuration

The `config.php` file is already configured for MySQL with these default settings:
- Host: localhost
- Port: 3306
- Database: agoracart
- Username: root
- Password: (empty)

If you created a custom user in Step 2, update these values in `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_PORT', 3306);
define('DB_NAME', 'agoracart');
define('DB_USER', 'agoracart_user');     // Change if needed
define('DB_PASS', 'your_secure_password'); // Change if needed
```

## Step 5: Test Database Connection

1. Run the PHP server:
```bash
php -S localhost:5000
```

2. Visit `http://localhost:5000` in your browser
3. If you see the website with product categories, the database is working correctly

## Database Schema Overview

The database includes these main tables:
- `categories` - Product categories (Girls, Boys, Infants)
- `products` - Product information with pricing and inventory
- `product_images` - Product images and media
- `product_attributes` - Size, color, and other product options
- `users` - Customer accounts and profiles
- `user_otp_verification` - Email/SMS OTP verification
- `cart_items` - Shopping cart contents
- `orders` - Order management and payment tracking

## Sample Data

The database setup includes sample data:
- 3 categories (Girls, Boys, Infants)
- 10 sample products with images
- Product attributes (sizes and colors)

## Troubleshooting

### Common Issues:

1. **Connection Error**: Check MySQL service is running
2. **Access Denied**: Verify username and password in config.php
3. **Database Not Found**: Make sure you created the database first
4. **Permission Issues**: Grant proper privileges to the database user

### Test Connection Script:
Create a file `test_db.php`:
```php
<?php
require_once 'config.php';
try {
    $pdo = getDbConnection();
    echo "Database connection successful!\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
    $result = $stmt->fetch();
    echo "Found " . $result['count'] . " categories in database.\n";
} catch (Exception $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
?>
```

Run: `php test_db.php`

## Security Notes

- Change default passwords in production
- Use environment variables for sensitive data
- Enable SSL/TLS for MySQL connections in production
- Regularly backup your database