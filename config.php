<?php
session_start();

// Database configuration - PostgreSQL (Replit compatible)
define('DB_HOST', $_ENV['PGHOST'] ?? 'localhost');
define('DB_PORT', $_ENV['PGPORT'] ?? 5432);
define('DB_NAME', $_ENV['PGDATABASE'] ?? 'agoracart');
define('DB_USER', $_ENV['PGUSER'] ?? 'postgres');
define('DB_PASS', $_ENV['PGPASSWORD'] ?? '');

define('BASE_URL', '/');

// Razorpay configuration
define('RAZORPAY_KEY_ID', $_ENV['RAZORPAY_KEY_ID'] ?? 'rzp_test_your_key_id');
define('RAZORPAY_KEY_SECRET', $_ENV['RAZORPAY_KEY_SECRET'] ?? 'your_secret_key');

// Site configuration
define('SITE_NAME', 'Lagorii Kids');
define('SITE_DESCRIPTION', 'Premium Children\'s Clothing - Trusted by 1 Lakh+ Parents');

// Currency settings
if (!defined('DEFAULT_CURRENCY')) {
    define('DEFAULT_CURRENCY', 'EUR');
}
if (!defined('CURRENCY_SYMBOL')) {
    define('CURRENCY_SYMBOL', '€');
}

// Database connection function
function getDbConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            // PostgreSQL connection for Replit
            $dsn = sprintf(
                "pgsql:host=%s;port=%s;dbname=%s",
                DB_HOST,
                DB_PORT,
                DB_NAME
            );
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
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
               STRING_AGG(DISTINCT pi.image_url, ',') as images,
               STRING_AGG(DISTINCT CASE WHEN pa.attribute_name = 'size' THEN pa.attribute_value END, ',') as sizes,
               STRING_AGG(DISTINCT CASE WHEN pa.attribute_name = 'color' THEN pa.attribute_value END, ',') as colors
        FROM products p 
        LEFT JOIN product_images pi ON p.id = pi.product_id
        LEFT JOIN product_attributes pa ON p.id = pa.product_id
        WHERE p.id = ? AND p.is_active = true
        GROUP BY p.id
    ");
    
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if ($product) {
        // Convert comma-separated strings to arrays
        $product['images'] = $product['images'] ? explode(',', $product['images']) : [];
        $product['sizes'] = $product['sizes'] ? array_filter(explode(',', $product['sizes'])) : [];
        $product['colors'] = $product['colors'] ? array_filter(explode(',', $product['colors'])) : [];
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
    ");
    
    $passwordHash = password_hash($userData['password'], PASSWORD_DEFAULT);
    
    $stmt->execute([
        $userData['firstName'],
        $userData['lastName'],
        $userData['email'],
        $passwordHash,
        $userData['phone'] ?? null
    ]);
    
    $userId = $pdo->lastInsertId();
    
    // Get the created user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
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

// Helper functions for data management
define('DATA_DIR', __DIR__ . '/data/');

function loadJsonData($filename) {
    $filepath = DATA_DIR . $filename;
    if (!file_exists($filepath)) {
        return [];
    }
    $content = file_get_contents($filepath);
    return $content ? json_decode($content, true) : [];
}

function saveJsonData($filename, $data) {
    $filepath = DATA_DIR . $filename;
    $dir = dirname($filepath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    return file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
}

// Initialize database connection on first load
try {
    getDbConnection();
} catch (Exception $e) {
    error_log("Failed to initialize database connection: " . $e->getMessage());
}

// Helper function to get base path for assets
function getBasePath() {
    $currentPath = $_SERVER['REQUEST_URI'];
    if (strpos($currentPath, '/pages/') !== false) {
        return '../';
    }
    return '';
}
?>
