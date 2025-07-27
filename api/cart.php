<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($method) {
        case 'GET':
            // Get cart contents
            $cartId = $_GET['cart_id'] ?? session_id();
            $cart = getCartContents($cartId);
            echo json_encode($cart);
            break;
            
        case 'POST':
            // Add item to cart
            $cartId = $input['cart_id'] ?? session_id();
            $productId = $input['product_id'] ?? '';
            $quantity = intval($input['quantity'] ?? 1);
            $options = $input['options'] ?? [];
            
            if (!$productId) {
                throw new Exception('Product ID is required');
            }
            
            $result = addToCart($cartId, $productId, $quantity, $options);
            echo json_encode($result);
            break;
            
        case 'PUT':
            // Update cart item
            $cartId = $input['cart_id'] ?? session_id();
            $productId = $input['product_id'] ?? '';
            $quantity = intval($input['quantity'] ?? 1);
            
            if (!$productId) {
                throw new Exception('Product ID is required');
            }
            
            $result = updateCartItem($cartId, $productId, $quantity);
            echo json_encode($result);
            break;
            
        case 'DELETE':
            // Remove item from cart
            $cartId = $_GET['cart_id'] ?? session_id();
            $productId = $_GET['product_id'] ?? '';
            
            if (!$productId) {
                throw new Exception('Product ID is required');
            }
            
            $result = removeFromCart($cartId, $productId);
            echo json_encode($result);
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function getCartContents($cartId) {
    // For database-based cart, we'll use user session
    session_start();
    $userId = $_SESSION['user_id'] ?? null;
    
    if ($userId) {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare("
                SELECT ci.*, p.name, p.price, p.discount_price, pi.image_url
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_primary = true
                WHERE ci.user_id = ?
                ORDER BY ci.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Cart fetch error: " . $e->getMessage());
        }
    }
    
    // Fallback to file-based cart for guest users
    $cartFile = DATA_DIR . 'carts/' . $cartId . '.json';
    if (file_exists($cartFile)) {
        return json_decode(file_get_contents($cartFile), true);
    }
    return [];
}

function saveCart($cartId, $cart) {
    $cartDir = DATA_DIR . 'carts/';
    if (!is_dir($cartDir)) {
        mkdir($cartDir, 0755, true);
    }
    
    $cartFile = $cartDir . $cartId . '.json';
    return file_put_contents($cartFile, json_encode($cart, JSON_PRETTY_PRINT));
}

function addToCart($cartId, $productId, $quantity, $options = []) {
    session_start();
    $userId = $_SESSION['user_id'] ?? null;
    
    if ($userId) {
        try {
            $pdo = getDbConnection();
            
            // Check if item already exists in cart
            $size = $options['size'] ?? null;
            $color = $options['color'] ?? null;
            
            $stmt = $pdo->prepare("
                SELECT id, quantity FROM cart_items 
                WHERE user_id = ? AND product_id = ? AND size = ? AND color = ?
            ");
            $stmt->execute([$userId, $productId, $size, $color]);
            $existingItem = $stmt->fetch();
            
            if ($existingItem) {
                // Update existing item
                $newQuantity = $existingItem['quantity'] + $quantity;
                $stmt = $pdo->prepare("UPDATE cart_items SET quantity = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$newQuantity, $existingItem['id']]);
            } else {
                // Add new item
                $stmt = $pdo->prepare("
                    INSERT INTO cart_items (user_id, product_id, quantity, size, color) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$userId, $productId, $quantity, $size, $color]);
            }
            
            // Get cart count
            $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart_items WHERE user_id = ?");
            $stmt->execute([$userId]);
            $result = $stmt->fetch();
            $cartCount = $result['total'] ?? 0;
            
            return [
                'success' => true,
                'message' => 'Item added to cart',
                'cart_count' => $cartCount
            ];
            
        } catch (Exception $e) {
            error_log("Add to cart error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to add item to cart'];
        }
    }
    
    // Fallback to file-based cart for guests
    $cart = getCartContents($cartId);
    
    // Check if item already exists in cart
    $existingIndex = -1;
    foreach ($cart as $index => $item) {
        if ($item['product_id'] === $productId && 
            json_encode($item['options']) === json_encode($options)) {
            $existingIndex = $index;
            break;
        }
    }
    
    if ($existingIndex >= 0) {
        // Update existing item
        $cart[$existingIndex]['quantity'] += $quantity;
    } else {
        // Add new item
        $cart[] = [
            'product_id' => $productId,
            'quantity' => $quantity,
            'options' => $options,
            'added_at' => date('Y-m-d H:i:s')
        ];
    }
    
    saveCart($cartId, $cart);
    
    return [
        'success' => true,
        'message' => 'Item added to cart',
        'cart_count' => array_sum(array_column($cart, 'quantity'))
    ];
}

function updateCartItem($cartId, $productId, $quantity) {
    $cart = getCartContents($cartId);
    
    foreach ($cart as $index => $item) {
        if ($item['product_id'] === $productId) {
            if ($quantity <= 0) {
                // Remove item if quantity is 0 or less
                array_splice($cart, $index, 1);
            } else {
                $cart[$index]['quantity'] = $quantity;
            }
            break;
        }
    }
    
    saveCart($cartId, $cart);
    
    return [
        'success' => true,
        'message' => 'Cart updated',
        'cart_count' => array_sum(array_column($cart, 'quantity'))
    ];
}

function removeFromCart($cartId, $productId) {
    $cart = getCartContents($cartId);
    
    $cart = array_filter($cart, function($item) use ($productId) {
        return $item['product_id'] !== $productId;
    });
    
    // Re-index array
    $cart = array_values($cart);
    
    saveCart($cartId, $cart);
    
    return [
        'success' => true,
        'message' => 'Item removed from cart',
        'cart_count' => array_sum(array_column($cart, 'quantity'))
    ];
}
?>
