@echo off
echo Setting up PostgreSQL Environment Variables for AgoraCart
echo.

REM Default values - modify these according to your PostgreSQL setup
set PGHOST=localhost
set PGPORT=5432
set PGDATABASE=agoracart
set PGUSER=postgres

REM Prompt for password
set /p PGPASSWORD="Enter your PostgreSQL password: "

echo.
echo Setting environment variables...
setx PGHOST %PGHOST%
setx PGPORT %PGPORT%
setx PGDATABASE %PGDATABASE%
setx PGUSER %PGUSER%
setx PGPASSWORD %PGPASSWORD%

echo.
echo Environment variables have been set!
echo Please restart VS Code or your terminal for changes to take effect.
echo.
echo Current settings:
echo PGHOST=%PGHOST%
echo PGPORT=%PGPORT%
echo PGDATABASE=%PGDATABASE%
echo PGUSER=%PGUSER%
echo PGPASSWORD=***hidden***
echo.

REM Test database creation
echo Would you like to create the database '%PGDATABASE%' if it doesn't exist? (y/n)
set /p CREATE_DB=
if /i "%CREATE_DB%"=="y" (
    echo Creating database %PGDATABASE%...
    psql -h %PGHOST% -p %PGPORT% -U %PGUSER% -c "CREATE DATABASE %PGDATABASE%;" 2>nul
    if %ERRORLEVEL% EQU 0 (
        echo Database created successfully!
    ) else (
        echo Database may already exist or there was an error.
    )
)

echo.
echo Next steps:
echo 1. Restart VS Code or your terminal
echo 2. Navigate to http://localhost:5000/database_setup.php
echo 3. Click "Setup Database Tables & Data"
echo.
pause
