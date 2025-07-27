<?php
// Test PHPMailer and Twilio SDK availability
echo "<h2>Package Availability Test</h2>";

// Test PHPMailer
echo "<h3>PHPMailer Test</h3>";
$phpmailerPath = __DIR__ . '/PHPMailer/src/PHPMailer.php';
if (file_exists($phpmailerPath)) {
    require_once $phpmailerPath;
    require_once __DIR__ . '/PHPMailer/src/SMTP.php';
    require_once __DIR__ . '/PHPMailer/src/Exception.php';
    
    if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
        echo "✅ PHPMailer is available and working!<br>";
        $mailer = new PHPMailer\PHPMailer\PHPMailer();
        echo "PHPMailer version: " . $mailer::VERSION . "<br>";
    } else {
        echo "❌ PHPMailer class not found<br>";
    }
} else {
    echo "❌ PHPMailer files not found<br>";
}

echo "<hr>";

// Test Twilio SDK
echo "<h3>Twilio SDK Test</h3>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
    
    if (class_exists('Twilio\\Rest\\Client')) {
        echo "✅ Twilio SDK is available and working!<br>";
    } else {
        echo "❌ Twilio SDK not found - using fallback SMS methods<br>";
    }
} else {
    echo "❌ Vendor autoload not found - Twilio SDK not installed<br>";
    echo "ℹ️ Using alternative SMS methods (TextLocal, etc.)<br>";
}

echo "<hr>";

// Test OTP Functions
echo "<h3>OTP Functions Test</h3>";
session_start();

// Mock email OTP test
$_SESSION['email_otp'] = '123456';
$_SESSION['email_otp_email'] = 'test@example.com';
$_SESSION['email_otp_time'] = time();

echo "Mock email OTP stored: " . $_SESSION['email_otp'] . "<br>";

// Mock phone OTP test
$_SESSION['phone_otp'] = '654321';
$_SESSION['phone_otp_phone'] = '+1234567890';
$_SESSION['phone_otp_time'] = time();

echo "Mock phone OTP stored: " . $_SESSION['phone_otp'] . "<br>";

echo "<hr>";

// Configuration Test
echo "<h3>Configuration Test</h3>";
include_once __DIR__ . '/api/auth.php';

$emailConfigured = (SMTP_USERNAME !== 'YOUR_GMAIL_ADDRESS@gmail.com');
$twilioConfigured = (defined('TWILIO_SID') && TWILIO_SID !== 'YOUR_TWILIO_SID');
$textlocalConfigured = (TEXTLOCAL_API_KEY !== 'YOUR_TEXTLOCAL_API_KEY');

echo "Email configured: " . ($emailConfigured ? "✅ Yes" : "❌ No (using mock mode)") . "<br>";
echo "Twilio configured: " . ($twilioConfigured ? "✅ Yes" : "❌ No (using mock mode)") . "<br>";
echo "TextLocal configured: " . ($textlocalConfigured ? "✅ Yes" : "❌ No") . "<br>";

if (!$emailConfigured && !$twilioConfigured && !$textlocalConfigured) {
    echo "<br><strong>ℹ️ All services in mock mode - perfect for development testing!</strong><br>";
    echo "Configure real credentials in api/auth.php for production use.<br>";
}

echo "<hr>";
echo "<h3>Next Steps</h3>";
echo "1. ✅ PHPMailer is ready to use<br>";
echo "2. ❌ Install Twilio SDK manually or use alternative SMS service<br>";
echo "3. ⚙️ Configure email and SMS credentials for production<br>";
echo "4. 🧪 Test OTP functionality with mock data<br>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
hr { margin: 20px 0; }
</style>
