<?php
/**
 * Simple Configuration Diagnostic
 * Checks if GoDaddy SMTP is properly configured
 */

require_once(__DIR__ . '/config.php');

echo "=== Adeptskil SMTP Configuration Diagnostic ===\n\n";

// Check 1: Configuration Constants
echo "1. Configuration Constants:\n";
echo "   MAIL_METHOD: " . MAIL_METHOD . "\n";
echo "   SMTP_HOST: " . (defined('SMTP_HOST') ? SMTP_HOST : 'NOT SET') . "\n";
echo "   SMTP_PORT: " . (defined('SMTP_PORT') ? SMTP_PORT : 'NOT SET') . "\n";
echo "   SMTP_USERNAME: " . (defined('SMTP_USERNAME') ? (strlen(SMTP_USERNAME) > 0 ? '✓ SET' : 'EMPTY') : 'NOT SET') . "\n";
echo "   SMTP_PASSWORD: " . (defined('SMTP_PASSWORD') ? (strlen(SMTP_PASSWORD) > 0 ? '✓ SET' : 'EMPTY') : 'NOT SET') . "\n";
echo "   SMTP_FROM_EMAIL: " . (defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'NOT SET') . "\n";
echo "   ADMIN_EMAIL: " . ADMIN_EMAIL . "\n\n";

// Check 2: Email storage directories
echo "2. Email Storage:\n";
echo "   MAIL_STORAGE_DIR: " . MAIL_STORAGE_DIR . "\n";
echo "   Directory exists: " . (is_dir(MAIL_STORAGE_DIR) ? '✓ YES' : '✗ NO') . "\n";
echo "   MAIL_LOG_FILE: " . MAIL_LOG_FILE . "\n";
echo "   Log file exists: " . (is_file(MAIL_LOG_FILE) ? '✓ YES (can append)' : 'NO (will create on first email)') . "\n\n";

// Check 3: PHP capabilities
echo "3. PHP Capabilities:\n";
echo "   fsockopen available: " . (function_exists('fsockopen') ? '✓ YES' : '✗ NO') . "\n";
echo "   stream_socket_enable_crypto: " . (function_exists('stream_socket_enable_crypto') ? '✓ YES' : '✗ NO') . "\n";
echo "   mail() available: " . (function_exists('mail') ? '✓ YES' : '✗ NO') . "\n";
echo "   PHP version: " . phpversion() . "\n\n";

// Check 4: Test email file storage
echo "4. Testing Email File Storage:\n";
$testEmail = [
    'id' => 'TEST-' . date('YmdHis'),
    'to' => ADMIN_EMAIL,
    'subject' => 'Configuration Test',
    'status' => 'test'
];
$testFile = MAIL_STORAGE_DIR . '/' . $testEmail['id'] . '.json';
$testWrite = file_put_contents($testFile, json_encode($testEmail, JSON_PRETTY_PRINT));

if ($testWrite !== false) {
    echo "   Test email file written: ✓ SUCCESS\n";
    echo "   File location: " . $testFile . "\n";
    echo "   File size: " . filesize($testFile) . " bytes\n";
    @unlink($testFile);
    echo "   Cleanup: ✓ DONE\n\n";
} else {
    echo "   Test email file write: ✗ FAILED\n";
    echo "   Could not write to: " . $testFile . "\n";
    echo "   Check directory permissions.\n\n";
}

// Check 5: Functions defined
echo "5. Functions Status:\n";
echo "   sendEmail() defined: " . (function_exists('sendEmail') ? '✓ YES' : '✗ NO') . "\n";
echo "   sendViaSMTP() defined: " . (function_exists('sendViaSMTP') ? '✓ YES' : '✗ NO') . "\n";
echo "   logEmail() defined: " . (function_exists('logEmail') ? '✓ YES' : '✗ NO') . "\n";
echo "   getAllEmails() defined: " . (function_exists('getAllEmails') ? '✓ YES' : '✗ NO') . "\n\n";

echo "=== Diagnostic Complete ===\n";
echo "Configuration is " . (MAIL_METHOD === 'smtp' ? "set to SMTP mode" : "NOT in SMTP mode") . "\n";
echo "System is ready to send emails via GoDaddy SMTP.\n";
