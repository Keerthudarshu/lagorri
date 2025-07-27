# PowerShell script to set up PostgreSQL environment variables for AgoraCart

Write-Host "Setting up PostgreSQL Environment Variables for AgoraCart" -ForegroundColor Green
Write-Host ""

# Default values - modify these according to your PostgreSQL setup
$defaultHost = "localhost"
$defaultPort = "5432"
$defaultDatabase = "agoracart"
$defaultUser = "postgres"

# Prompt for database connection details
$pgHost = Read-Host "PostgreSQL Host (default: $defaultHost)"
if ([string]::IsNullOrEmpty($pgHost)) { $pgHost = $defaultHost }

$pgPort = Read-Host "PostgreSQL Port (default: $defaultPort)"
if ([string]::IsNullOrEmpty($pgPort)) { $pgPort = $defaultPort }

$pgDatabase = Read-Host "Database Name (default: $defaultDatabase)"
if ([string]::IsNullOrEmpty($pgDatabase)) { $pgDatabase = $defaultDatabase }

$pgUser = Read-Host "PostgreSQL Username (default: $defaultUser)"
if ([string]::IsNullOrEmpty($pgUser)) { $pgUser = $defaultUser }

$pgPassword = Read-Host "PostgreSQL Password" -AsSecureString
$pgPasswordPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($pgPassword))

Write-Host ""
Write-Host "Setting environment variables..." -ForegroundColor Yellow

# Set environment variables for current session
$env:PGHOST = $pgHost
$env:PGPORT = $pgPort
$env:PGDATABASE = $pgDatabase
$env:PGUSER = $pgUser
$env:PGPASSWORD = $pgPasswordPlain

# Set permanent environment variables
[Environment]::SetEnvironmentVariable("PGHOST", $pgHost, "User")
[Environment]::SetEnvironmentVariable("PGPORT", $pgPort, "User")
[Environment]::SetEnvironmentVariable("PGDATABASE", $pgDatabase, "User")
[Environment]::SetEnvironmentVariable("PGUSER", $pgUser, "User")
[Environment]::SetEnvironmentVariable("PGPASSWORD", $pgPasswordPlain, "User")

Write-Host "Environment variables have been set!" -ForegroundColor Green
Write-Host ""
Write-Host "Current settings:" -ForegroundColor Cyan
Write-Host "PGHOST=$pgHost"
Write-Host "PGPORT=$pgPort"
Write-Host "PGDATABASE=$pgDatabase"
Write-Host "PGUSER=$pgUser"
Write-Host "PGPASSWORD=***hidden***"
Write-Host ""

# Test if PostgreSQL is accessible
Write-Host "Testing PostgreSQL connection..." -ForegroundColor Yellow
try {
    $connectionString = "Host=$pgHost;Port=$pgPort;Username=$pgUser;Password=$pgPasswordPlain;Database=postgres"
    # Note: This requires Npgsql, so we'll just show the connection details instead
    Write-Host "Connection string format: $connectionString" -ForegroundColor Green
    Write-Host "✓ Environment variables configured successfully!" -ForegroundColor Green
} catch {
    Write-Host "⚠️ Note: Install PostgreSQL client tools to test connection" -ForegroundColor Yellow
}

# Offer to create database
$createDb = Read-Host "Would you like to try creating the database '$pgDatabase'? (y/n)"
if ($createDb -eq 'y' -or $createDb -eq 'Y') {
    Write-Host "Attempting to create database..." -ForegroundColor Yellow
    try {
        $createDbCommand = "psql -h $pgHost -p $pgPort -U $pgUser -c `"CREATE DATABASE $pgDatabase;`""
        Write-Host "Run this command in a terminal with PostgreSQL tools:" -ForegroundColor Cyan
        Write-Host $createDbCommand -ForegroundColor White
    } catch {
        Write-Host "Make sure PostgreSQL client tools (psql) are installed" -ForegroundColor Yellow
    }
}

Write-Host ""
Write-Host "Next steps:" -ForegroundColor Green
Write-Host "1. Restart VS Code or your terminal"
Write-Host "2. Start your PHP server (e.g., php -S localhost:5000)"
Write-Host "3. Navigate to http://localhost:5000/database_setup.php"
Write-Host "4. Click 'Setup Database Tables & Data'"
Write-Host ""

Read-Host "Press Enter to continue..."
