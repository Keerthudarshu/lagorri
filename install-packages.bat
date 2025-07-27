@echo off
echo Installing PHPMailer and Twilio SDK...
echo.

REM Check if PHP is available
C:\xampp\php\php.exe --version >nul 2>&1
if %errorlevel% neq 0 (
    echo Error: PHP not found. Make sure XAMPP is installed.
    pause
    exit /b 1
)

echo PHP found. Proceeding with installation...
echo.

REM Try to download Composer
echo Downloading Composer...
C:\xampp\php\php.exe -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

if exist composer-setup.php (
    echo Installing Composer...
    C:\xampp\php\php.exe composer-setup.php
    del composer-setup.php
    
    if exist composer.phar (
        echo Composer installed successfully!
        echo.
        echo Installing packages...
        C:\xampp\php\php.exe composer.phar require phpmailer/phpmailer twilio/sdk
        
        if %errorlevel% equ 0 (
            echo.
            echo ✅ All packages installed successfully!
            echo You can now configure your email and SMS credentials in api/auth.php
        ) else (
            echo.
            echo ❌ Failed to install packages via Composer.
            echo Please check your internet connection or install manually.
            echo See INSTALLATION_GUIDE.md for manual installation steps.
        )
    ) else (
        echo ❌ Failed to install Composer.
    )
) else (
    echo ❌ Failed to download Composer installer.
    echo Please check your internet connection.
    echo.
    echo Alternative: Download composer.phar manually from https://getcomposer.org/download/
    echo and run: C:\xampp\php\php.exe composer.phar require phpmailer/phpmailer twilio/sdk
)

echo.
echo Installation complete. See INSTALLATION_GUIDE.md for configuration details.
pause
