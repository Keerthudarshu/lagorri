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
            $result = createRazorpayOrder($input);
            echo json_encode($result);
            break;
            
        case 'verify_payment':
            $result = verifyRazorpayPayment($input);
            echo json_encode($result);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function createRazorpayOrder($data) {
    $amount = floatval($data['amount'] ?? 0);
    $currency = $data['currency'] ?? DEFAULT_CURRENCY;
    $customerData = $data['customer'] ?? [];
    
    if ($amount <= 0) {
        throw new Exception('Invalid amount');
    }
    
    // Generate unique order ID
    $orderId = 'order_' . generateOrderNumber();
    
    // For development, we'll simulate Razorpay order creation
    $razorpayOrder = [
        'id' => 'rzp_' . uniqid(),
        'entity' => 'order',
        'amount' => $amount * 100, // Amount in paise
        'amount_paid' => 0,
        'amount_due' => $amount * 100,
        'currency' => $currency,
        'receipt' => $orderId,
        'status' => 'created',
        'created_at' => time()
    ];
    
    // Store order in database
    try {
        $pdo = getDbConnection();
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        
        $stmt = $pdo->prepare("
            INSERT INTO orders (user_id, order_number, razorpay_order_id, total_amount, status, payment_status) 
            VALUES (?, ?, ?, ?, 'pending', 'pending')
        ");
        $stmt->execute([$userId, $orderId, $razorpayOrder['id'], $amount]);
        
    } catch (Exception $e) {
        error_log("Failed to store Razorpay order: " . $e->getMessage());
        throw new Exception('Failed to create order');
    }
    
    return [
        'success' => true,
        'order' => $razorpayOrder,
        'key' => RAZORPAY_KEY_ID
    ];
}

function verifyRazorpayPayment($data) {
    $razorpayPaymentId = $data['razorpay_payment_id'] ?? '';
    $razorpayOrderId = $data['razorpay_order_id'] ?? '';
    $razorpaySignature = $data['razorpay_signature'] ?? '';
    
    if (!$razorpayPaymentId || !$razorpayOrderId) {
        throw new Exception('Payment ID and Order ID are required');
    }
    
    // In production, you would verify the signature using Razorpay's webhook signature verification
    // For development, we'll simulate successful verification
    
    try {
        $pdo = getDbConnection();
        
        // Find the order
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE razorpay_order_id = ?");
        $stmt->execute([$razorpayOrderId]);
        $order = $stmt->fetch();
        
        if (!$order) {
            throw new Exception('Order not found');
        }
        
        // Update order with payment details
        $updateTime = (DB_TYPE === 'postgresql') ? 'NOW()' : 'CURRENT_TIMESTAMP';
        $stmt = $pdo->prepare("
            UPDATE orders 
            SET razorpay_payment_id = ?, razorpay_signature = ?, payment_status = 'completed', status = 'confirmed', updated_at = $updateTime
            WHERE razorpay_order_id = ?
        ");
        $stmt->execute([$razorpayPaymentId, $razorpaySignature, $razorpayOrderId]);
        
        // Clear user's cart
        session_start();
        $userId = $_SESSION['user_id'] ?? null;
        if ($userId) {
            $stmt = $pdo->prepare("DELETE FROM cart_items WHERE user_id = ?");
            $stmt->execute([$userId]);
        }
        
        return [
            'success' => true,
            'message' => 'Payment verified successfully',
            'order_id' => $order['order_number']
        ];
        
    } catch (Exception $e) {
        error_log("Payment verification error: " . $e->getMessage());
        throw new Exception('Payment verification failed');
    }
}

// Helper function for signature verification (production use)
function verifyRazorpaySignature($paymentId, $orderId, $signature, $secret) {
    $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $secret);
    return hash_equals($expectedSignature, $signature);
}
?>