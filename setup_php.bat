@echo off
REM ============================================
REM Adeptskil PHP Installation Helper
REM Run this as Administrator
REM ============================================

SETLOCAL ENABLEDELAYEDEXPANSION

echo.
echo ======================================
echo   Adeptskil PHP Setup Helper
echo ======================================
echo.

REM Check if running as admin
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: Please run this script as Administrator
    echo Right-click cmd.exe and select "Run as Administrator"
    pause
    exit /b 1
)

REM Check if PHP already installed
where php >nul 2>&1
if %errorLevel% equ 0 (
    echo.
    echo ✓ PHP is already installed!
    php --version
    echo.
    echo You can now start the server with:
    echo   cd "%CD%"
    echo   php -S localhost:8000
    echo.
    pause
    exit /b 0
)

echo ⚠ PHP not installed or not in PATH
echo.
echo This script will help you set up PHP on Windows.
echo.
echo Requirements:
echo - Internet connection to download PHP
echo - Administrator privileges (which you have)
echo.
echo What would you like to do?
echo 1 = Download and install PHP automatically (requires 7-Zip or WinRAR)
echo 2 = Add existing PHP folder to PATH
echo 3 = Manual installation (exit and follow PDF guide)
echo 4 = Exit
echo.

set /p choice="Select option (1-4): "

if "%choice%"=="1" goto download_php
if "%choice%"=="2" goto add_path
if "%choice%"=="3" goto manual
if "%choice%"=="4" goto end
goto invalid

:download_php
echo.
echo Downloading PHP...
echo.

REM Note: PowerShell downloads are more reliable on modern Windows
powershell -Command "$ProgressPreference = 'SilentlyContinue'; Invoke-WebRequest -Uri 'https://windows.php.net/downloads/releases/php-8.2.0-Win32-vc15-x64.zip' -OutFile '%TEMP%\php.zip'"

if not exist "%TEMP%\php.zip" (
    echo ERROR: Failed to download PHP
    pause
    goto end
)

echo ✓ Downloaded PHP
echo.
echo Extracting to C:\php...

REM Create C:\php if it doesn't exist
if not exist "C:\php" mkdir C:\php

REM Extract - requires PowerShell
powershell -Command "Expand-Archive -Path '%TEMP%\php.zip' -DestinationPath 'C:\php' -Force"

if %errorLevel% neq 0 (
    echo ERROR: Failed to extract PHP
    pause
    goto end
)

echo ✓ Extracted PHP
echo.
goto add_path

:add_path
echo.
echo Adding PHP to Windows PATH...

REM Check if PHP folder exists
if not exist "C:\php" (
    set /p phppath="Enter the full path to PHP folder (e.g., C:\php): "
    if not exist "!phppath!" (
        echo ERROR: Path does not exist: !phppath!
        pause
        goto end
    )
) else (
    set "phppath=C:\php"
)

REM Check if already in PATH
for /f "tokens=2*" %%A in ('reg query "HKLM\SYSTEM\CurrentControlSet\Control\Session Manager\Environment" /v PATH') do set "current_path=%%B"

echo %current_path% | findstr /I "!phppath!" >nul
if not errorLevel 1 (
    echo ✓ PHP already in PATH
) else (
    echo Adding !phppath! to system PATH...
    setx PATH "%current_path%;!phppath!" /M
    if %errorLevel% neq 0 (
        echo ERROR: Failed to update PATH
        echo Try manually adding C:\php to your system PATH
        pause
        goto end
    )
    echo ✓ Added to PATH
    echo Note: You may need to restart your terminal for changes to take effect
)

echo.
echo ✓ PHP Installation Complete!
echo.
echo Next steps:
echo 1. Restart PowerShell or Command Prompt
echo 2. Navigate to your project folder
echo 3. Run: php -S localhost:8000
echo.
pause
goto end

:manual
cd /d "%CD%"
start PHP_INSTALLATION_GUIDE.md
goto end

:invalid
echo Invalid option. Please select 1, 2, 3, or 4.
pause
goto download_php

:end
echo.
echo For help, see: PHP_INSTALLATION_GUIDE.md
echo.
exit /b 0
