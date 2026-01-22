<?php
/**
 * Configuration file for Adeptskil
 * Centralized settings for email and system configuration
 */

// Email Configuration
define('ADMIN_EMAIL', 'info@adeptskil.com');
define('SITE_NAME', 'Adeptskil');
define('SITE_URL', 'https://adeptskil.com');

// Mail Configuration
define('MAIL_METHOD', 'log'); // Options: 'php' (uses mail()), 'log' (file-based), 'api' (future)
define('MAIL_LOG_FILE', __DIR__ . '/mail_log.txt');

// Directories
define('DATA_DIR', __DIR__ . '/data');
define('LOGS_DIR', __DIR__ . '/logs');

// Ensure directories exist
if (!is_dir(DATA_DIR)) {
    mkdir(DATA_DIR, 0755, true);
}
if (!is_dir(LOGS_DIR)) {
    mkdir(LOGS_DIR, 0755, true);
}

// Function to send email with fallback logging
function sendEmail($to, $subject, $body, $from = ADMIN_EMAIL, $replyTo = null) {
    $headers = "From: $from\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    if ($replyTo) {
        $headers .= "Reply-To: $replyTo\r\n";
    }
    
    // Always log the email attempt
    logEmail($to, $subject, $body, $from);
    
    // Try to send via PHP mail() function
    if (MAIL_METHOD === 'php') {
        return @mail($to, $subject, $body, $headers);
    } else if (MAIL_METHOD === 'log') {
        // If using log method, return true (email is logged and will be reviewed)
        return true;
    }
    
    return false;
}

// Function to log email attempts to file
function logEmail($to, $subject, $body, $from) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] TO: $to | SUBJECT: $subject | FROM: $from\n";
    $logEntry .= "---\n$body\n---\n\n";
    
    $logFile = MAIL_LOG_FILE;
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
}

?>
