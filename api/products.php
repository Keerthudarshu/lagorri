<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $products = loadJsonData('products.json');
    
    // Apply filters based on query parameters
    $category = $_GET['category'] ?? '';
    $subcategory = $_GET['subcategory'] ?? '';
    $search = $_GET['search'] ?? '';
    $featured = $_GET['featured'] ?? '';
    $newArrival = $_GET['new'] ?? '';
    $limit = intval($_GET['limit'] ?? 0);
    $priceRange = $_GET['price'] ?? '';
    
    $filteredProducts = $products;
    
    // Filter by category
    if ($category) {
        $filteredProducts = array_filter($filteredProducts, function($product) use ($category) {
            return $product['category'] === $category;
        });
    }
    
    // Filter by subcategory
    if ($subcategory) {
        $filteredProducts = array_filter($filteredProducts, function($product) use ($subcategory) {
            return $product['subcategory'] === $subcategory;
        });
    }
    
    // Filter by search term
    if ($search) {
        $searchLower = strtolower($search);
        $filteredProducts = array_filter($filteredProducts, function($product) use ($searchLower) {
            return strpos(strtolower($product['name']), $searchLower) !== false ||
                   strpos(strtolower($product['description']), $searchLower) !== false ||
                   in_array($searchLower, array_map('strtolower', $product['tags'] ?? []));
        });
    }
    
    // Filter by featured
    if ($featured === 'true') {
        $filteredProducts = array_filter($filteredProducts, function($product) {
            return $product['featured'] ?? false;
        });
    }
    
    // Filter by new arrivals
    if ($newArrival === 'true') {
        $filteredProducts = array_filter($filteredProducts, function($product) {
            return $product['newArrival'] ?? false;
        });
    }
    
    // Filter by price range
    if ($priceRange) {
        $filteredProducts = array_filter($filteredProducts, function($product) use ($priceRange) {
            $price = $product['price'];
            switch ($priceRange) {
                case '0-25':
                    return $price < 25;
                case '25-50':
                    return $price >= 25 && $price < 50;
                case '50-100':
                    return $price >= 50 && $price < 100;
                case '100+':
                    return $price >= 100;
                default:
                    return true;
            }
        });
    }
    
    // Sort products (default by name)
    $sortBy = $_GET['sort'] ?? 'name';
    usort($filteredProducts, function($a, $b) use ($sortBy) {
        switch ($sortBy) {
            case 'price_low':
                return $a['price'] <=> $b['price'];
            case 'price_high':
                return $b['price'] <=> $a['price'];
            case 'newest':
                return ($b['newArrival'] ?? false) <=> ($a['newArrival'] ?? false);
            default:
                return $a['name'] <=> $b['name'];
        }
    });
    
    // Apply limit
    if ($limit > 0) {
        $filteredProducts = array_slice($filteredProducts, 0, $limit);
    }
    
    // Re-index array to ensure proper JSON encoding
    $filteredProducts = array_values($filteredProducts);
    
    echo json_encode($filteredProducts);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load products: ' . $e->getMessage()]);
}
?>
