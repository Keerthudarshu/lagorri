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
    // Get query parameters
    $category = $_GET['category'] ?? '';
    $subcategory = $_GET['subcategory'] ?? '';
    $search = $_GET['search'] ?? '';
    $featured = $_GET['featured'] ?? '';
    $newArrival = $_GET['new'] ?? '';
    $limit = intval($_GET['limit'] ?? 20);
    $priceRange = $_GET['price'] ?? '';
    
    // Get category ID if category slug is provided
    $categoryId = null;
    if ($category) {
        $categories = getCategories();
        foreach ($categories as $cat) {
            if ($cat['slug'] === $category) {
                $categoryId = $cat['id'];
                break;
            }
        }
    }
    
    // Get products based on search or category
    if ($search) {
        $filteredProducts = searchProducts($search, $limit);
    } else {
        $filteredProducts = getProducts($categoryId, $limit, $featured === 'true');
    }
    
    // Additional filtering for subcategory if needed
    if ($subcategory && !empty($filteredProducts)) {
        $filteredProducts = array_filter($filteredProducts, function($product) use ($subcategory) {
            return $product['subcategory'] === $subcategory;
        });
    }
    
    // Filter by price range if needed
    if ($priceRange && !empty($filteredProducts)) {
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
    
    // Format product data for frontend
    $formattedProducts = array_map(function($product) {
        return [
            'id' => $product['id'],
            'name' => $product['name'],
            'description' => $product['description'],
            'price' => $product['price'],
            'originalPrice' => $product['original_price'],
            'category' => $product['category_id'],
            'subcategory' => $product['subcategory'],
            'sku' => $product['sku'],
            'inStock' => $product['stock_quantity'] > 0,
            'featured' => (bool)$product['is_featured'],
            'newArrival' => (bool)$product['is_new_arrival'],
            'images' => [$product['primary_image'] ?? ''],
            'stockQuantity' => $product['stock_quantity']
        ];
    }, $filteredProducts);
    
    // Re-index array to ensure proper JSON encoding
    $formattedProducts = array_values($formattedProducts);
    
    echo json_encode($formattedProducts);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load products: ' . $e->getMessage()]);
}
?>
