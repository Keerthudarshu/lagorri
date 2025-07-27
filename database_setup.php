<?php
/**
 * Database Setup Script for AgoraCart
 * This script will create the PostgreSQL database schema and populate it with sample data
 */

require_once 'config.php';

function setupDatabase() {
    echo "<h2>Setting up MySQL Database for AgoraCart</h2>\n";
    
    try {
        // First, create the database if it doesn't exist
        $tempPdo = new PDO("mysql:host=" . DB_HOST . ";port=" . DB_PORT, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
        
        echo "<p>✓ Connected to MySQL server</p>\n";
        
        // Create database if it doesn't exist
        $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p>✓ Database '" . DB_NAME . "' created or already exists</p>\n";
        
        // Read the SQL setup file
        $sqlFile = __DIR__ . '/database_setup_mysql.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("MySQL setup file not found: $sqlFile");
        }
        
        $sql = file_get_contents($sqlFile);
        if ($sql === false) {
            throw new Exception("Could not read MySQL setup file");
        }
        
        // Get database connection to the specific database
        $pdo = getDbConnection();
        echo "<p>✓ Connected to AgoraCart database successfully</p>\n";
        
        // Execute the setup SQL
        echo "<p>Creating database schema and inserting sample data...</p>\n";
        
        // Split SQL into individual statements
        $statements = explode(';', $sql);
        $executedCount = 0;
        
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if (empty($statement) || strpos($statement, '--') === 0) {
                continue;
            }
            
            try {
                $pdo->exec($statement);
                $executedCount++;
            } catch (PDOException $e) {
                // Some statements might fail if tables already exist, that's okay
                if (strpos($e->getMessage(), 'already exists') === false && 
                    strpos($e->getMessage(), 'does not exist') === false) {
                    echo "<p>⚠️ Warning executing statement: " . $e->getMessage() . "</p>\n";
                }
            }
        }
        
        echo "<p>✓ Executed $executedCount SQL statements</p>\n";
        
        // Verify the setup by checking if tables exist and have data
        $tables = ['categories', 'users', 'products', 'product_images'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p>✓ Table '$table' has $count records</p>\n";
        }
        
        echo "<h3>✅ Database setup completed successfully!</h3>\n";
        echo "<p><strong>Test user credentials:</strong></p>\n";
        echo "<ul>\n";
        echo "<li>Email: john.doe@example.com - Password: password</li>\n";
        echo "<li>Email: jane.smith@example.com - Password: password</li>\n";
        echo "</ul>\n";
        
        return true;
        
    } catch (Exception $e) {
        echo "<h3>❌ Database setup failed!</h3>\n";
        echo "<p>Error: " . $e->getMessage() . "</p>\n";
        
        // Show database configuration
        echo "<h4>Database Configuration:</h4>\n";
        echo "<ul>\n";
        echo "<li>Host: " . DB_HOST . "</li>\n";
        echo "<li>Port: " . DB_PORT . "</li>\n";
        echo "<li>Database: " . DB_NAME . "</li>\n";
        echo "<li>User: " . DB_USER . "</li>\n";
        echo "</ul>\n";
        
        return false;
    }
}

function testDatabaseConnection() {
    echo "<h2>Testing Database Connection</h2>\n";
    
    try {
        $pdo = getDbConnection();
        
        // Test basic connection
        $stmt = $pdo->query('SELECT VERSION()');
        $version = $stmt->fetchColumn();
        echo "<p>✓ MySQL Version: $version</p>\n";
        
        // Test if our tables exist
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($tables)) {
            echo "<p>⚠️ No tables found. You need to run the database setup.</p>\n";
            return false;
        }
        
        echo "<p>✓ Found " . count($tables) . " tables:</p>\n";
        echo "<ul>\n";
        foreach ($tables as $table) {
            echo "<li>$table</li>\n";
        }
        echo "</ul>\n";
        
        // Test sample data
        $stmt = $pdo->query("SELECT COUNT(*) FROM products");
        $productCount = $stmt->fetchColumn();
        echo "<p>✓ Products in database: $productCount</p>\n";
        
        $stmt = $pdo->query("SELECT COUNT(*) FROM categories");
        $categoryCount = $stmt->fetchColumn();
        echo "<p>✓ Categories in database: $categoryCount</p>\n";
        
        return true;
        
    } catch (Exception $e) {
        echo "<p>❌ Connection failed: " . $e->getMessage() . "</p>\n";
        return false;
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgoraCart - Database Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background-color: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        h2 { color: #007bff; margin-top: 30px; }
        h3 { color: #28a745; }
        .btn { display: inline-block; padding: 12px 24px; margin: 10px 5px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .btn-success { background: #28a745; }
        .btn-success:hover { background: #1e7e34; }
        .alert { padding: 15px; margin: 20px 0; border-radius: 5px; }
        .alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
        ul { line-height: 1.6; }
        code { background: #f8f9fa; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛒 AgoraCart - MySQL Database Setup</h1>
        
        <div class="alert alert-info">
            <strong>Welcome to AgoraCart Database Setup!</strong><br>
            This tool will help you set up your MySQL database with all the necessary tables and sample data.
        </div>

        <?php
        $action = $_GET['action'] ?? '';
        
        if ($action === 'setup') {
            setupDatabase();
            echo '<p><a href="database_setup.php" class="btn">← Back to Menu</a></p>';
        } elseif ($action === 'test') {
            testDatabaseConnection();
            echo '<p><a href="database_setup.php" class="btn">← Back to Menu</a></p>';
        } else {
            // Show main menu
        ?>
        
        <h2>Setup Options</h2>
        
        <p><strong>Choose an action:</strong></p>
        
        <a href="database_setup.php?action=test" class="btn">🔍 Test Database Connection</a>
        <a href="database_setup.php?action=setup" class="btn btn-success">🚀 Setup Database Tables & Data</a>
        
        <h2>Environment Variables Required</h2>
        <div class="alert alert-warning">
            <p><strong>The database is now configured to use MySQL (XAMPP default):</strong></p>
            <ul>
                <li><code>Host</code>: localhost</li>
                <li><code>Port</code>: 3306</li>
                <li><code>Database</code>: agoracart</li>
                <li><code>Username</code>: root</li>
                <li><code>Password</code>: (empty - XAMPP default)</li>
            </ul>
            
            <p><strong>Current MySQL Configuration:</strong></p>
            <ul>
                <li>Host: <?= DB_HOST ?></li>
                <li>Port: <?= DB_PORT ?></li>
                <li>Database: <?= DB_NAME ?></li>
                <li>User: <?= DB_USER ?></li>
                <li>Password: <?= DB_PASS ? 'Set' : 'Empty (XAMPP default)' ?></li>
            </ul>
        </div>
        
        <h2>How to Setup (XAMPP)</h2>
        <p>Since you're using XAMPP, the MySQL database is already configured with default settings:</p>
        
        <h3>Option 1: Start XAMPP MySQL</h3>
        <ol>
            <li>Open XAMPP Control Panel</li>
            <li>Start the "MySQL" module</li>
            <li>The database will be accessible at localhost:3306</li>
            <li>Click "Setup Database Tables & Data" below</li>
        </ol>
        
        <h3>Option 2: Using phpMyAdmin (Optional)</h3>
        <ol>
            <li>Start XAMPP and MySQL module</li>
            <li>Open phpMyAdmin (http://localhost/phpmyadmin)</li>
            <li>You can view the database after setup is complete</li>
        </ol>
        
        <h2>What This Setup Will Do</h2>
        <ul>
            <li>✅ Create MySQL database 'agoracart' if it doesn't exist</li>
            <li>✅ Create all necessary database tables (categories, products, users, orders, etc.)</li>
            <li>✅ Add sample categories (Girls, Boys, Infants)</li>
            <li>✅ Insert 10 sample products with images and attributes</li>
            <li>✅ Create 2 test user accounts</li>
            <li>✅ Add sample product reviews and ratings</li>
            <li>✅ Set up proper database indexes for performance</li>
        </ul>
        
        <p><a href="index.php" class="btn">🏠 Go to Website</a></p>
        
        <?php } ?>
    </div>
</body>
</html>
