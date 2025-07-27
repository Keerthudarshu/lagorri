<?php
session_start();

// Database configuration - PostgreSQL
try {
    $database_url = getenv('DATABASE_URL');
    if ($database_url) {
        $db_parts = parse_url($database_url);
        define('DB_HOST', $db_parts['host']);
        define('DB_PORT', isset($db_parts['port']) ? $db_parts['port'] : 5432);
        define('DB_NAME', ltrim($db_parts['path'], '/'));
        define('DB_USER', $db_parts['user']);
        define('DB_PASS', $db_parts['pass']);
    } else {
        // Fallback to individual environment variables
        define('DB_HOST', getenv('PGHOST') ?: 'localhost');
        define('DB_PORT', getenv('PGPORT') ?: 5432);
        define('DB_NAME', getenv('PGDATABASE') ?: 'lagorii_kids');
        define('DB_USER', getenv('PGUSER') ?: 'postgres');
        define('DB_PASS', getenv('PGPASSWORD') ?: '');
    }
} catch (Exception $e) {
    error_log("Database configuration error: " . $e->getMessage());
    // Use individual environment variables as fallback
    if (!defined('DB_HOST')) {
        define('DB_HOST', getenv('PGHOST') ?: 'localhost');
        define('DB_PORT', getenv('PGPORT') ?: 5432);
        define('DB_NAME', getenv('PGDATABASE') ?: 'lagorii_kids');
        define('DB_USER', getenv('PGUSER') ?: 'postgres');
        define('DB_PASS', getenv('PGPASSWORD') ?: '');
    }
}

define('BASE_URL', '/');

// Razorpay configuration
define('RAZORPAY_KEY_ID', 'rzp_test_your_key_id');
define('RAZORPAY_KEY_SECRET', 'your_secret_key');

// Site configuration
define('SITE_NAME', 'Lagorii Kids');
define('SITE_DESCRIPTION', 'Premium Children\'s Clothing - Trusted by 1 Lakh+ Parents');

// Currency settings
if (!defined('DEFAULT_CURRENCY')) {
    define('DEFAULT_CURRENCY', 'EUR');
    define('CURRENCY_SYMBOL', '€');
}

// Database connection function
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            // Use individual environment variables (works with Neon PostgreSQL)
            $dsn = sprintf(
                "pgsql:host=%s;port=%s;dbname=%s;sslmode=require",
                getenv('PGHOST'),
                getenv('PGPORT'),
                getenv('PGDATABASE')
            );
            
            $pdo = new PDO($dsn, getenv('PGUSER'), getenv('PGPASSWORD'), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

// Helper functions for database operations
function getProducts($categoryId = null, $limit = null, $featured = false) {
    $pdo = getDbConnection();
    
    $sql = "SELECT p.*, pi.image_url as primary_image 
            FROM products p 
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = true 
            WHERE p.is_active = true";
    
    $params = [];
    
    if ($categoryId) {
        $sql .= " AND p.category_id = ?";
        $params[] = $categoryId;
    }
    
    if ($featured) {
        $sql .= " AND p.is_featured = true";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT ?";
        $params[] = $limit;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll();
}

function getProductById($id) {
    $pdo = getDbConnection();
    
    $stmt = $pdo->prepare("
        SELECT p.*, 
               ARRAY_AGG(DISTINCT pi.image_url) as images,
               ARRAY_AGG(DISTINCT CASE WHEN pa.attribute_name = 'size' THEN pa.attribute_value END) as sizes,
               ARRAY_AGG(DISTINCT CASE WHEN pa.attribute_name = 'color' THEN pa.attribute_value END) as colors
        FROM products p 
        LEFT JOIN product_images pi ON p.id = pi.product_id
        LEFT JOIN product_attributes pa ON p.id = pa.product_id
        WHERE p.id = ? AND p.is_active = true
        GROUP BY p.id
    ");
    
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if ($product) {
        // Clean up arrays (remove nulls)
        $product['images'] = array_filter($product['images']);
        $product['sizes'] = array_filter($product['sizes']);
        $product['colors'] = array_filter($product['colors']);
    }
    
    return $product;
}

function getCategories() {
    $pdo = getDbConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE is_active = true ORDER BY name");
    $stmt->execute();
    
    return $stmt->fetchAll();
}

function searchProducts($query, $limit = 20) {
    $pdo = getDbConnection();
    
    $stmt = $pdo->prepare("
        SELECT p.*, pi.image_url as primary_image 
        FROM products p 
        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = true 
        WHERE p.is_active = true 
        AND (p.name ILIKE ? OR p.description ILIKE ? OR p.subcategory ILIKE ?)
        ORDER BY p.name
        LIMIT ?
    ");
    
    $searchTerm = "%{$query}%";
    $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit]);
    
    return $stmt->fetchAll();
}

function formatPrice($price, $currency = DEFAULT_CURRENCY) {
    return CURRENCY_SYMBOL . number_format($price, 0);
}

function generateOrderNumber() {
    return 'LK' . date('Ymd') . rand(1000, 9999);
}

// User authentication functions
function authenticateUser($email, $password) {
    $pdo = getDbConnection();
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = true");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password_hash'])) {
        return [
            'id' => $user['id'],
            'name' => $user['first_name'] . ' ' . $user['last_name'],
            'email' => $user['email'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'phone' => $user['phone']
        ];
    }
    
    return false;
}

function createUser($userData) {
    $pdo = getDbConnection();
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$userData['email']]);
    if ($stmt->fetch()) {
        throw new Exception("Email already exists");
    }
    
    // Create new user
    $stmt = $pdo->prepare("
        INSERT INTO users (first_name, last_name, email, password_hash, phone) 
        VALUES (?, ?, ?, ?, ?) 
        RETURNING id, first_name, last_name, email, phone
    ");
    
    $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
    
    $stmt->execute([
        $userData['firstName'],
        $userData['lastName'],
        $userData['email'],
        $passwordHash,
        $userData['phone'] ?? null
    ]);
    
    $user = $stmt->fetch();
    
    return [
        'id' => $user['id'],
        'name' => $user['first_name'] . ' ' . $user['last_name'],
        'email' => $user['email'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'phone' => $user['phone']
    ];
}

// Initialize database connection on first load
try {
    getDbConnection();
} catch (Exception $e) {
    error_log("Failed to initialize database connection: " . $e->getMessage());
}
?>
