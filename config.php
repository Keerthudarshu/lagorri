<?php
session_start();

// Database configuration (using JSON files for this implementation)
define('DATA_DIR', __DIR__ . '/data/');
define('BASE_URL', '/');

// Razorpay configuration
define('RAZORPAY_KEY_ID', 'rzp_test_your_key_id');
define('RAZORPAY_KEY_SECRET', 'your_secret_key');

// Site configuration
define('SITE_NAME', 'Lagorii Kids');
define('SITE_DESCRIPTION', 'Premium Children\'s Clothing - Trusted by 1 Lakh+ Parents');

// Currency settings
define('DEFAULT_CURRENCY', 'EUR');
define('CURRENCY_SYMBOL', '€');

// Helper functions
function loadJsonData($filename) {
    $filepath = DATA_DIR . $filename;
    if (file_exists($filepath)) {
        return json_decode(file_get_contents($filepath), true);
    }
    return [];
}

function saveJsonData($filename, $data) {
    $filepath = DATA_DIR . $filename;
    return file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT));
}

function formatPrice($price, $currency = DEFAULT_CURRENCY) {
    return CURRENCY_SYMBOL . number_format($price, 0);
}

function generateId() {
    return uniqid('', true);
}

// Create data directory if it doesn't exist
if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}
?>
