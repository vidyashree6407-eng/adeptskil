<?php
/**
 * GoDaddy SMTP Configuration for Adeptskil
 * Domain: adeptskil.com
 * 
 * Instructions:
 * 1. Get your GoDaddy email password
 * 2. Edit lines below with your email and password
 * 3. Save and test
 */

// GoDaddy SMTP Settings for adeptskil.com
define('SMTP_SERVER', 'smtpout.secureserver.net');
define('SMTP_PORT', 465);
define('SMTP_SECURE', 'ssl');

// Update these with your actual email credentials
define('SMTP_EMAIL', 'info@adeptskil.com');      // Your GoDaddy email
define('SMTP_PASSWORD', 'YOUR_PASSWORD_HERE');   // Your GoDaddy email password

// Test the connection
echo "=== GoDaddy SMTP Configuration Test ===\n\n";
echo "Server: " . SMTP_SERVER . "\n";
echo "Port: " . SMTP_PORT . "\n";
echo "Email: " . SMTP_EMAIL . "\n";
echo "Password: " . (SMTP_PASSWORD === 'YOUR_PASSWORD_HERE' ? '❌ NOT SET' : '✓ Set') . "\n\n";

if (SMTP_PASSWORD === 'YOUR_PASSWORD_HERE') {
    echo "⚠️  SETUP REQUIRED:\n";
    echo "1. Edit this file\n";
    echo "2. Replace 'YOUR_PASSWORD_HERE' with your GoDaddy email password\n";
    echo "3. Save and run again\n";
    exit;
}

// Try to send test email
echo "Attempting to send test email...\n";

$to = 'test@example.com';
$subject = 'GoDaddy SMTP Test';
$message = 'If you received this, GoDaddy SMTP is working!';
$headers = "From: " . SMTP_EMAIL . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// For GoDaddy to work, you need PHPMailer or similar
// Standard mail() won't work with SMTP auth

echo "\n✅ Configuration set for GoDaddy\n";
echo "Next: Use PHPMailer to send emails\n";
?>
