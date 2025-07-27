<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';
session_start(); // Start session for OTP storage

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Debug logging
    error_log("Auth API called - Method: $method, Action: " . ($input['action'] ?? 'none'));
    
    switch ($method) {
        case 'POST':
            $action = $input['action'] ?? '';
            
            switch ($action) {
                case 'register':
                    $result = handleRegister($input);
                    echo json_encode($result);
                    break;
                    
                case 'login':
                    $result = handleLogin($input);
                    echo json_encode($result);
                    break;
                    
                case 'logout':
                    $result = handleLogout();
                    echo json_encode($result);
                    break;
                    
                case 'send_email_otp':
                    $result = sendEmailOTP($input);
                    echo json_encode($result);
                    break;
                    
                case 'send_phone_otp':
                    $result = sendPhoneOTP($input);
                    echo json_encode($result);
                    break;
                    
                case 'verify_email_otp':
                    $result = verifyEmailOTP($input);
                    echo json_encode($result);
                    break;
                    
                case 'verify_phone_otp':
                    $result = verifyPhoneOTP($input);
                    echo json_encode($result);
                    break;
                    
                default:
                    throw new Exception('Invalid action');
            }
            break;
            
        case 'GET':
            // Check if user is logged in
            $result = getCurrentUser();
            echo json_encode($result);
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}

function handleRegister($data) {
    // Validate input
    if (!isset($data['email'], $data['password'], $data['firstName'], $data['lastName'])) {
        throw new Exception('All fields are required');
    }
    
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }
    
    if (strlen($data['password']) < 6) {
        throw new Exception('Password must be at least 6 characters long');
    }
    
    // Check if email and phone are verified
    $emailVerified = $_SESSION['email_verified'] ?? false;
    $phoneVerified = $_SESSION['phone_verified'] ?? false;
    $verifiedEmail = $_SESSION['verified_email'] ?? '';
    $verifiedPhone = $_SESSION['verified_phone'] ?? '';
    
    if (!$emailVerified || $verifiedEmail !== $data['email']) {
        throw new Exception('Email not verified. Please verify your email first.');
    }
    
    if (!$phoneVerified || $verifiedPhone !== ($data['phone'] ?? '')) {
        throw new Exception('Phone number not verified. Please verify your phone first.');
    }
    
    try {
        // Create new user using database function
        $user = createUser($data);
        
        // Clean up verification sessions
        unset($_SESSION['email_verified'], $_SESSION['phone_verified'], 
              $_SESSION['verified_email'], $_SESSION['verified_phone']);
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        
        return [
            'success' => true,
            'message' => 'Registration successful',
            'user' => $user
        ];
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

function handleLogin($data) {
    if (!isset($data['email'], $data['password'])) {
        throw new Exception('Email and password are required');
    }
    
    try {
        // Authenticate user using database function
        $user = authenticateUser($data['email'], $data['password']);
        
        if (!$user) {
            throw new Exception('Invalid email or password');
        }
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        
        return [
            'success' => true,
            'message' => 'Login successful',
            'user' => $user
        ];
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

function handleLogout() {
    try {
        return logoutUser();
    } catch (Exception $e) {
        throw new Exception('Logout failed: ' . $e->getMessage());
    }
}

function authenticateUser($email, $password) {
    
    // Load users
    $users = loadJsonData('users.json');
    
    // Find user
    $user = null;
    $userIndex = -1;
    foreach ($users as $index => $u) {
        if ($u['email'] === $email) {
            $user = $u;
            $userIndex = $index;
            break;
        }
    }
    
    if (!$user || !password_verify($password, $user['password'])) {
        throw new Exception('Invalid email or password');
    }
    
    // Update last login
    $users[$userIndex]['last_login'] = date('Y-m-d H:i:s');
    saveJsonData('users.json', $users);
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_name'] = $user['firstName'] . ' ' . $user['lastName'];
    
    return [
        'success' => true,
        'message' => 'Login successful',
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['firstName'] . ' ' . $user['lastName']
        ]
    ];
}

function logoutUser() {
    session_destroy();
    session_start();
    
    return [
        'success' => true,
        'message' => 'Logout successful'
    ];
}

// === CONFIGURATION FOR OTP DELIVERY ===
// Gmail SMTP for PHPMailer
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'YOUR_GMAIL_ADDRESS@gmail.com');
define('SMTP_PASSWORD', 'YOUR_GMAIL_APP_PASSWORD');
define('SMTP_FROM_EMAIL', 'YOUR_GMAIL_ADDRESS@gmail.com');
define('SMTP_FROM_NAME', 'Your App Name');

// TextLocal SMS API
define('TEXTLOCAL_API_KEY', 'YOUR_TEXTLOCAL_API_KEY');
define('TEXTLOCAL_SENDER', 'TXTLCL'); // Or your approved sender ID

// OTP Functions
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmailOTP($data) {
    $email = $data['email'] ?? '';
    if (empty($email)) {
        return ['success' => false, 'error' => 'Email is required'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Invalid email format'];
    }
    $otp = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    $_SESSION['email_otp'] = $otp;
    $_SESSION['email_otp_email'] = $email;
    $_SESSION['email_otp_time'] = time();
    $emailContent = "<h2>Verify Your Email</h2><p>Your verification code is: <strong style='font-size: 24px; color: #007bff;'>$otp</strong></p><p>This code will expire in 10 minutes.</p>";
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'your@email.com';
        $mail->Password = 'yourpassword';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('your@email.com', 'Your App');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email';
        $mail->Body = $emailContent;

        $mail->send();
        return [
            'success' => true,
            'message' => 'OTP sent successfully to your email'
        ];
    } catch (Exception $e) {
        error_log("Email OTP sending failed: " . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Failed to send email OTP'
        ];
    }
}

require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
use Twilio\Rest\Client;

function sendPhoneOTP($data) {
    $phone = $data['phone'] ?? '';
    if (empty($phone)) {
        return ['success' => false, 'error' => 'Phone number is required'];
    }
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    if (strlen($phone) < 10) {
        return ['success' => false, 'error' => 'Invalid phone number'];
    }
    $otp = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    $_SESSION['phone_otp'] = $otp;
    $_SESSION['phone_otp_phone'] = $phone;
    $_SESSION['phone_otp_time'] = time();
    $smsContent = "Your verification code is: $otp. Valid for 10 minutes.";
    try {
        $sid = 'your_twilio_sid';
        $token = 'your_twilio_token';
        $client = new Client($sid, $token);

        $client->messages->create(
            $phone,
            [
                'from' => 'your_twilio_number',
                'body' => $smsContent
            ]
        );
        return [
            'success' => true,
            'message' => 'OTP sent successfully to your phone'
        ];
    } catch (Exception $e) {
        error_log('TextLocal SMS exception: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Failed to send phone OTP'
        ];
    }
}

function verifyEmailOTP($data) {
    $email = $data['email'] ?? '';
    $otp = $data['otp'] ?? '';
    
    if (empty($email) || empty($otp)) {
        return ['success' => false, 'error' => 'Email and OTP are required'];
    }
    
    // Check if OTP exists and is valid
    $storedOTP = $_SESSION['email_otp'] ?? '';
    $storedEmail = $_SESSION['email_otp_email'] ?? '';
    $otpTime = $_SESSION['email_otp_time'] ?? 0;
    
    // Check if OTP has expired (10 minutes = 600 seconds)
    if (time() - $otpTime > 600) {
        unset($_SESSION['email_otp'], $_SESSION['email_otp_email'], $_SESSION['email_otp_time']);
        return ['success' => false, 'error' => 'OTP has expired. Please request a new one.'];
    }
    
    // Check if email matches
    if ($storedEmail !== $email) {
        return ['success' => false, 'error' => 'Invalid email for OTP verification'];
    }
    
    // Check if OTP matches
    if ($storedOTP !== $otp) {
        return ['success' => false, 'error' => 'Invalid OTP. Please try again.'];
    }
    
    // OTP is valid, mark email as verified
    $_SESSION['email_verified'] = true;
    $_SESSION['verified_email'] = $email;
    
    // Clean up OTP from session
    unset($_SESSION['email_otp'], $_SESSION['email_otp_email'], $_SESSION['email_otp_time']);
    
    return [
        'success' => true,
        'message' => 'Email verified successfully'
    ];
}

function verifyPhoneOTP($data) {
    $phone = $data['phone'] ?? '';
    $otp = $data['otp'] ?? '';
    
    if (empty($phone) || empty($otp)) {
        return ['success' => false, 'error' => 'Phone and OTP are required'];
    }
    
    // Check if OTP exists and is valid
    $storedOTP = $_SESSION['phone_otp'] ?? '';
    $storedPhone = $_SESSION['phone_otp_phone'] ?? '';
    $otpTime = $_SESSION['phone_otp_time'] ?? 0;
    
    // Check if OTP has expired (10 minutes = 600 seconds)
    if (time() - $otpTime > 600) {
        unset($_SESSION['phone_otp'], $_SESSION['phone_otp_phone'], $_SESSION['phone_otp_time']);
        return ['success' => false, 'error' => 'OTP has expired. Please request a new one.'];
    }
    
    // Normalize phone numbers for comparison
    $normalizedStored = preg_replace('/[^0-9+]/', '', $storedPhone);
    $normalizedInput = preg_replace('/[^0-9+]/', '', $phone);
    
    // Check if phone matches
    if ($normalizedStored !== $normalizedInput) {
        return ['success' => false, 'error' => 'Invalid phone number for OTP verification'];
    }
    
    // Check if OTP matches
    if ($storedOTP !== $otp) {
        return ['success' => false, 'error' => 'Invalid OTP. Please try again.'];
    }
    
    // OTP is valid, mark phone as verified
    $_SESSION['phone_verified'] = true;
    $_SESSION['verified_phone'] = $phone;
    
    // Clean up OTP from session
    unset($_SESSION['phone_otp'], $_SESSION['phone_otp_phone'], $_SESSION['phone_otp_time']);
    
    return [
        'success' => true,
        'message' => 'Phone number verified successfully'
    ];
}

function getCurrentUser() {
    if (isset($_SESSION['user_id'])) {
        return [
            'success' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'email' => $_SESSION['user_email'],
                'name' => $_SESSION['user_name']
            ]
        ];
    }
    
    return [
        'success' => false,
        'message' => 'User not logged in'
    ];
}

?>
