<?php
require_once '../config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed');
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'create_order':
            $result = createOrder($input);
            echo json_encode($result);
            break;
            
        case 'verify_payment':
            $result = verifyPayment($input);
            echo json_encode($result);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function createOrder($data) {
    $customerData = $data['customer'] ?? [];
    $billingData = $data['billing'] ?? [];
    $shippingData = $data['shipping'] ?? $billingData;
    $items = $data['items'] ?? [];
    $total = floatval($data['total'] ?? 0);
    
    // Validate required fields
    if (!$customerData['email'] || !$customerData['firstName'] || !$customerData['lastName']) {
        throw new Exception('Customer information is required');
    }
    
    if (!$billingData['address'] || !$billingData['city'] || !$billingData['country']) {
        throw new Exception('Billing address is required');
    }
    
    if (empty($items)) {
        throw new Exception('No items in order');
    }
    
    // Generate order ID
    $orderId = 'LK' . time() . rand(1000, 9999);
    
    // Create order
    $order = [
        'id' => $orderId,
        'customer' => $customerData,
        'billing' => $billingData,
        'shipping' => $shippingData,
        'items' => $items,
        'subtotal' => $total,
        'discount' => calculateDiscount($items),
        'shipping_cost' => 0, // Free shipping
        'total' => $total,
        'status' => 'pending',
        'payment_status' => 'pending',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Save order
    $orders = loadJsonData('orders.json');
    $orders[] = $order;
    saveJsonData('orders.json', $orders);
    
    return [
        'success' => true,
        'order_id' => $orderId,
        'amount' => $total * 100, // Amount in paise for Razorpay
        'currency' => DEFAULT_CURRENCY
    ];
}

function verifyPayment($data) {
    $paymentId = $data['payment_id'] ?? '';
    $orderId = $data['order_id'] ?? '';
    $signature = $data['signature'] ?? '';
    
    if (!$paymentId || !$orderId) {
        throw new Exception('Payment ID and Order ID are required');
    }
    
    // Load order
    $orders = loadJsonData('orders.json');
    $orderIndex = -1;
    $order = null;
    
    foreach ($orders as $index => $o) {
        if ($o['id'] === $orderId) {
            $order = $o;
            $orderIndex = $index;
            break;
        }
    }
    
    if (!$order) {
        throw new Exception('Order not found');
    }
    
    // In a real implementation, you would verify the payment with Razorpay API
    // For this demo, we'll assume the payment is successful
    
    // Update order status
    $orders[$orderIndex]['payment_id'] = $paymentId;
    $orders[$orderIndex]['payment_status'] = 'completed';
    $orders[$orderIndex]['status'] = 'confirmed';
    $orders[$orderIndex]['updated_at'] = date('Y-m-d H:i:s');
    
    saveJsonData('orders.json', $orders);
    
    // Clear cart (in a real app, you'd clear the specific user's cart)
    $cartId = session_id();
    $cartFile = DATA_DIR . 'carts/' . $cartId . '.json';
    if (file_exists($cartFile)) {
        unlink($cartFile);
    }
    
    return [
        'success' => true,
        'message' => 'Payment verified successfully',
        'order' => $orders[$orderIndex]
    ];
}

function calculateDiscount($items) {
    $totalQuantity = array_sum(array_column($items, 'quantity'));
    $subtotal = 0;
    
    // Calculate subtotal (simplified - in real app, fetch actual prices)
    foreach ($items as $item) {
        $subtotal += 25 * $item['quantity']; // Using average price
    }
    
    if ($totalQuantity >= 3) {
        return $subtotal * 0.10; // 10% discount
    } elseif ($totalQuantity >= 2) {
        return $subtotal * 0.05; // 5% discount
    }
    
    return 0;
}
?>
