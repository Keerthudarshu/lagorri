<?php
/**
 * Simple Autoloader for manually installed packages
 * This file provides autoloading for Twilio SDK and other packages
 * when Composer is not available
 */

// Register autoloader
spl_autoload_register(function ($className) {
    // Handle Twilio classes
    if (strpos($className, 'Twilio\\') === 0) {
        $relativePath = str_replace('\\', '/', substr($className, 7));
        $file = __DIR__ . '/twilio/sdk/src/' . $relativePath . '.php';
        
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    
    // Handle PHPMailer classes (fallback if needed)
    if (strpos($className, 'PHPMailer\\PHPMailer\\') === 0) {
        $className = str_replace('PHPMailer\\PHPMailer\\', '', $className);
        $file = dirname(__DIR__) . '/PHPMailer/src/' . $className . '.php';
        
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
    }
    
    return false;
});

// Include manually if autoloader fails
if (!class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
    $phpmailerPath = dirname(__DIR__) . '/PHPMailer/src/';
    if (file_exists($phpmailerPath . 'PHPMailer.php')) {
        require_once $phpmailerPath . 'PHPMailer.php';
        require_once $phpmailerPath . 'SMTP.php';
        require_once $phpmailerPath . 'Exception.php';
    }
}

// Check if Twilio SDK is available
function isTwilioAvailable() {
    return class_exists('Twilio\\Rest\\Client');
}

// Function to check what SMS services are available
function getAvailableSmsServices() {
    $services = [];
    
    if (isTwilioAvailable() && defined('TWILIO_SID') && TWILIO_SID !== 'YOUR_TWILIO_SID') {
        $services[] = 'twilio';
    }
    
    if (defined('TEXTLOCAL_API_KEY') && TEXTLOCAL_API_KEY !== 'YOUR_TEXTLOCAL_API_KEY') {
        $services[] = 'textlocal';
    }
    
    return $services;
}
?>
