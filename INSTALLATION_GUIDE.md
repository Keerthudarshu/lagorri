# Manual Installation Guide for PHPMailer & Twilio SDK

## Current Status
✅ **PHPMailer**: Already installed in `/PHPMailer/` directory
❌ **Twilio SDK**: Needs manual installation due to network connectivity issues

## Option 1: Install Composer and use it (Recommended)

### Step 1: Download Composer manually
1. Go to https://getcomposer.org/download/
2. Download `composer.phar` or `Composer-Setup.exe` for Windows
3. Place `composer.phar` in your project root directory

### Step 2: Install dependencies
```bash
# If you downloaded composer.phar
C:\xampp\php\php.exe composer.phar install

# If you installed Composer globally
composer install
```

## Option 2: Manual Twilio SDK Installation

### Step 1: Download Twilio SDK
1. Go to https://github.com/twilio/twilio-php/releases
2. Download the latest release (v7.x)
3. Extract to `vendor/twilio/sdk/` directory

### Step 2: Create autoloader
Create a file `vendor/autoload.php` with the following content:

```php
<?php
// Simple autoloader for Twilio SDK
spl_autoload_register(function ($className) {
    if (strpos($className, 'Twilio\\') === 0) {
        $file = __DIR__ . '/twilio/sdk/src/' . str_replace('\\', '/', substr($className, 7)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});
?>
```

## Option 3: Use Alternative SMS Services (Current Implementation)

The current code supports multiple SMS providers:

### TextLocal (India)
1. Sign up at https://www.textlocal.in/
2. Get your API key
3. Update `TEXTLOCAL_API_KEY` in auth.php

### Direct cURL implementation for other SMS services
The code can be easily extended for other SMS providers.

## Configuration

### Email Setup (Gmail)
1. Enable 2-factor authentication on your Gmail account
2. Generate an App Password:
   - Go to Google Account Settings
   - Security → 2-Step Verification → App passwords
   - Generate password for "Mail"
3. Update these constants in `auth.php`:
   ```php
   define('SMTP_USERNAME', 'your.email@gmail.com');
   define('SMTP_PASSWORD', 'your-app-password');
   define('SMTP_FROM_EMAIL', 'your.email@gmail.com');
   ```

### Twilio Setup
1. Sign up at https://www.twilio.com/
2. Get your Account SID, Auth Token, and Phone Number
3. Update these constants in `auth.php`:
   ```php
   define('TWILIO_SID', 'your-account-sid');
   define('TWILIO_TOKEN', 'your-auth-token');
   define('TWILIO_PHONE_NUMBER', '+1234567890');
   ```

## Testing

The current implementation includes development/mock modes:
- If credentials are not configured, it will use mock mode
- OTP will be logged to error log and returned in response (for testing)
- Remove `debug_otp` from responses in production

## Production Deployment

1. Configure real email credentials
2. Configure SMS service credentials
3. Remove debug OTP from responses
4. Set up proper error logging
5. Configure HTTPS for security
