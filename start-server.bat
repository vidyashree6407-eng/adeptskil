@echo off
REM Adeptskil Python Server Starter
REM This script installs Python dependencies and starts the server

echo ========================================
echo Adeptskil Python Server Setup
echo ========================================
echo.

REM Check if Python is installed
python --version >nul 2>&1
if errorlevel 1 (
    echo ERROR: Python is not installed or not in PATH
    echo.
    echo Please install Python from: https://www.python.org/downloads/
    echo Make sure to check "Add Python to PATH" during installation
    echo.
    pause
    exit /b 1
)

echo Python is installed!
python --version
echo.

REM Install required packages
echo Installing required packages...
pip install flask flask-cors --quiet

if errorlevel 1 (
    echo ERROR: Failed to install packages
    pause
    exit /b 1
)

echo.
echo ========================================
echo Starting Server...
echo ========================================
echo.
echo Open browser: http://localhost:8000
echo Database: enrollments.db
echo Press Ctrl+C to stop
echo.

REM Start the server
python app.py

pause
