<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../config.php';

// Include autoloader for packages
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

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
define('SMTP_FROM_NAME', 'AgoraCart');

// Twilio SMS API
define('TWILIO_SID', 'YOUR_TWILIO_SID');
define('TWILIO_TOKEN', 'YOUR_TWILIO_TOKEN');
define('TWILIO_PHONE_NUMBER', 'YOUR_TWILIO_PHONE_NUMBER');

// TextLocal SMS API (Alternative)
define('TEXTLOCAL_API_KEY', 'YOUR_TEXTLOCAL_API_KEY');
define('TEXTLOCAL_SENDER', 'TXTLCL'); // Or your approved sender ID

// Include PHPMailer classes
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';

// OTP Functions
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
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
    
    $emailContent = "
    <html>
    <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px;'>
        <div style='max-width: 600px; margin: 0 auto; background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);'>
            <h2 style='color: #333; text-align: center; margin-bottom: 30px;'>Verify Your Email</h2>
            <p style='color: #666; font-size: 16px; line-height: 1.5; margin-bottom: 30px;'>
                Please use the verification code below to verify your email address:
            </p>
            <div style='text-align: center; margin: 30px 0;'>
                <span style='font-size: 32px; font-weight: bold; color: #007bff; background-color: #f8f9fa; padding: 15px 30px; border-radius: 8px; border: 2px dashed #007bff; letter-spacing: 3px;'>$otp</span>
            </div>
            <p style='color: #666; font-size: 14px; text-align: center; margin-top: 30px;'>
                This code will expire in 10 minutes for security reasons.
            </p>
            <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>
            <p style='color: #999; font-size: 12px; text-align: center;'>
                If you didn't request this verification, please ignore this email.
            </p>
        </div>
    </body>
    </html>";
    
    try {
        $mail = new PHPMailer(true);
        
        // Use mock mode for development (since we don't have real SMTP configured)
        error_log("EMAIL OTP DEBUG: Sending OTP $otp to $email");
        
        // For development - simulate email sending
        if (SMTP_USERNAME === 'YOUR_GMAIL_ADDRESS@gmail.com') {
            // Mock mode - log the email instead of sending
            error_log("MOCK EMAIL OTP: $otp sent to $email");
            return [
                'success' => true,
                'message' => 'OTP sent successfully to your email (Development Mode)',
                'debug_otp' => $otp // Remove in production
            ];
        }
        
        // Real email sending configuration
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - AgoraCart';
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
            'error' => 'Failed to send email OTP: ' . $e->getMessage()
        ];
    }
}

function sendPhoneOTP($data) {
    $phone = $data['phone'] ?? '';
    if (empty($phone)) {
        return ['success' => false, 'error' => 'Phone number is required'];
    }
    
    // Clean and validate phone number
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    if (strlen($phone) < 10) {
        return ['success' => false, 'error' => 'Invalid phone number'];
    }
    
    $otp = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
    $_SESSION['phone_otp'] = $otp;
    $_SESSION['phone_otp_phone'] = $phone;
    $_SESSION['phone_otp_time'] = time();
    
    $smsContent = "Your AgoraCart verification code is: $otp. Valid for 10 minutes. Do not share this code.";
    
    try {
        // Check if Twilio is configured
        if (!defined('TWILIO_SID') || TWILIO_SID === 'YOUR_TWILIO_SID') {
            // Mock mode for development
            error_log("MOCK SMS OTP: $otp sent to $phone");
            return [
                'success' => true,
                'message' => 'OTP sent successfully to your phone (Development Mode)',
                'debug_otp' => $otp // Remove in production
            ];
        }
        
        // For Twilio SDK - when properly installed
        if (class_exists('Twilio\Rest\Client')) {
            $client = new \Twilio\Rest\Client(TWILIO_SID, TWILIO_TOKEN);
            
            $client->messages->create(
                $phone,
                [
                    'from' => TWILIO_PHONE_NUMBER,
                    'body' => $smsContent
                ]
            );
            
            return [
                'success' => true,
                'message' => 'OTP sent successfully to your phone'
            ];
        }
        
        // Alternative SMS service (TextLocal) - for manual implementation
        if (defined('TEXTLOCAL_API_KEY') && TEXTLOCAL_API_KEY !== 'YOUR_TEXTLOCAL_API_KEY') {
            $data = [
                'apikey' => TEXTLOCAL_API_KEY,
                'numbers' => $phone,
                'message' => $smsContent,
                'sender' => TEXTLOCAL_SENDER
            ];
            
            $ch = curl_init('https://api.textlocal.in/send/');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            
            $response = curl_exec($ch);
            curl_close($ch);
            
            $result = json_decode($response, true);
            
            if ($result && $result['status'] === 'success') {
                return [
                    'success' => true,
                    'message' => 'OTP sent successfully to your phone'
                ];
            } else {
                throw new Exception('SMS sending failed');
            }
        }
        
        // Fallback to mock mode
        error_log("SMS OTP DEBUG: No SMS service configured, using mock mode");
        return [
            'success' => true,
            'message' => 'OTP sent successfully to your phone (Mock Mode)',
            'debug_otp' => $otp
        ];
        
    } catch (Exception $e) {
        error_log('SMS OTP exception: ' . $e->getMessage());
        return [
            'success' => false,
            'error' => 'Failed to send phone OTP: ' . $e->getMessage()
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
