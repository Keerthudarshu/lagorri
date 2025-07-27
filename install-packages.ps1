# PowerShell script to install PHPMailer and Twilio SDK
Write-Host "Installing PHPMailer and Twilio SDK..." -ForegroundColor Green
Write-Host ""

# Check if PHP is available
try {
    $phpVersion = & "C:\xampp\php\php.exe" --version
    Write-Host "PHP found: $($phpVersion.Split([Environment]::NewLine)[0])" -ForegroundColor Green
    Write-Host ""
} catch {
    Write-Host "Error: PHP not found. Make sure XAMPP is installed." -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

# Try to download Composer installer
Write-Host "Downloading Composer installer..." -ForegroundColor Yellow
try {
    Invoke-WebRequest -Uri "https://getcomposer.org/installer" -OutFile "composer-setup.php"
    Write-Host "Composer installer downloaded successfully!" -ForegroundColor Green
    
    # Install Composer
    Write-Host "Installing Composer..." -ForegroundColor Yellow
    & "C:\xampp\php\php.exe" composer-setup.php
    Remove-Item "composer-setup.php"
    
    if (Test-Path "composer.phar") {
        Write-Host "Composer installed successfully!" -ForegroundColor Green
        Write-Host ""
        
        # Install packages
        Write-Host "Installing packages..." -ForegroundColor Yellow
        & "C:\xampp\php\php.exe" composer.phar require phpmailer/phpmailer twilio/sdk
        
        if ($LASTEXITCODE -eq 0) {
            Write-Host ""
            Write-Host "✅ All packages installed successfully!" -ForegroundColor Green
            Write-Host "You can now configure your email and SMS credentials in api/auth.php" -ForegroundColor Cyan
        } else {
            Write-Host ""
            Write-Host "❌ Failed to install packages via Composer." -ForegroundColor Red
            Write-Host "Please check your internet connection or install manually." -ForegroundColor Yellow
            Write-Host "See INSTALLATION_GUIDE.md for manual installation steps." -ForegroundColor Cyan
        }
    } else {
        Write-Host "❌ Failed to install Composer." -ForegroundColor Red
    }
    
} catch {
    Write-Host "❌ Failed to download Composer installer." -ForegroundColor Red
    Write-Host "Error: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host ""
    Write-Host "Alternative solutions:" -ForegroundColor Yellow
    Write-Host "1. Download composer.phar manually from https://getcomposer.org/download/" -ForegroundColor Cyan
    Write-Host "2. Run: C:\xampp\php\php.exe composer.phar require phpmailer/phpmailer twilio/sdk" -ForegroundColor Cyan
    Write-Host "3. Follow manual installation in INSTALLATION_GUIDE.md" -ForegroundColor Cyan
}

Write-Host ""
Write-Host "Installation complete. See INSTALLATION_GUIDE.md for configuration details." -ForegroundColor Green
Read-Host "Press Enter to exit"
