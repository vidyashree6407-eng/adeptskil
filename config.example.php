<?php
/**
 * Configuration Template - COPY THIS FILE
 * 
 * DO NOT COMMIT THIS FILE TO GIT IF YOU ADD REAL CREDENTIALS!
 * 
 * Instructions:
 * 1. Copy this file to config.php
 * 2. Fill in your SMTP credentials
 * 3. Add config.php to .gitignore
 * 4. NEVER push config.php to GitHub
 */

// Email Configuration
define('ADMIN_EMAIL', 'info@adeptskil.com');
define('SITE_NAME', 'Adeptskil');
define('SITE_URL', 'https://adeptskil.com');

// SMTP Configuration - Using Brevo (Sendinblue)
// Sign up free at https://brevo.com/ (300 emails/day free)
// Get credentials from: https://app.brevo.com/dashboard/smtp-api
define('SMTP_HOST', 'smtp-relay.brevo.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', ''); // Your Brevo email/username here
define('SMTP_PASSWORD', ''); // Your SMTP API key here
define('SMTP_FROM_EMAIL', 'info@adeptskil.com');
define('SMTP_FROM_NAME', 'Adeptskil');

// Mail Configuration
define('MAIL_METHOD', 'smtp'); // Options: 'smtp', 'php', 'log'
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

// Function to send email via SMTP
function sendEmail($to, $subject, $body, $from = SMTP_FROM_EMAIL, $replyTo = null) {
    // Always log the email attempt
    logEmail($to, $subject, $body, $from);
    
    // Send via SMTP
    if (MAIL_METHOD === 'smtp') {
        return sendViaSMTP($to, $subject, $body, $from, $replyTo);
    }
    
    // Fallback to PHP mail()
    $headers = "From: $from\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    if ($replyTo) {
        $headers .= "Reply-To: $replyTo\r\n";
    }
    return @mail($to, $subject, $body, $headers);
}

// Function to send email via SMTP
function sendViaSMTP($to, $subject, $body, $from, $replyTo = null) {
    try {
        // Validate credentials are set
        if (empty(SMTP_USERNAME) || empty(SMTP_PASSWORD)) {
            error_log("SMTP: Credentials not configured in config.php");
            return false;
        }
        
        // Create SMTP connection
        $socket = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
        
        if (!$socket) {
            error_log("SMTP Error: Could not connect to " . SMTP_HOST . ":" . SMTP_PORT);
            return false;
        }
        
        // Read server response
        $response = fgets($socket, 1024);
        if (substr($response, 0, 3) !== '220') {
            fclose($socket);
            return false;
        }
        
        // Send EHLO
        fputs($socket, "EHLO adeptskil.com\r\n");
        while (substr($response = fgets($socket, 1024), 3, 1) !== ' ') { }
        
        // Start TLS
        fputs($socket, "STARTTLS\r\n");
        $response = fgets($socket, 1024);
        
        // Upgrade to TLS
        stream_context_set_option($socket, 'ssl', 'verify_peer', false);
        stream_context_set_option($socket, 'ssl', 'verify_peer_name', false);
        stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        
        // Read response
        $response = fgets($socket, 1024);
        
        // Authenticate
        fputs($socket, "EHLO adeptskil.com\r\n");
        while (substr($response = fgets($socket, 1024), 3, 1) !== ' ') { }
        
        fputs($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 1024);
        
        // Send username
        fputs($socket, base64_encode(SMTP_USERNAME) . "\r\n");
        $response = fgets($socket, 1024);
        
        // Send password
        fputs($socket, base64_encode(SMTP_PASSWORD) . "\r\n");
        $response = fgets($socket, 1024);
        
        if (substr($response, 0, 3) !== '235') {
            fclose($socket);
            error_log("SMTP Auth Error: " . trim($response));
            return false;
        }
        
        // Send FROM
        fputs($socket, "MAIL FROM: <$from>\r\n");
        $response = fgets($socket, 1024);
        
        // Send TO
        fputs($socket, "RCPT TO: <$to>\r\n");
        $response = fgets($socket, 1024);
        
        // Send DATA
        fputs($socket, "DATA\r\n");
        $response = fgets($socket, 1024);
        
        // Build email headers
        $headers = "From: " . SMTP_FROM_NAME . " <$from>\r\n";
        $headers .= "To: $to\r\n";
        $headers .= "Subject: $subject\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        if ($replyTo) {
            $headers .= "Reply-To: $replyTo\r\n";
        }
        $headers .= "Date: " . date('r') . "\r\n";
        
        // Send email content
        fputs($socket, $headers . "\r\n" . $body . "\r\n.\r\n");
        $response = fgets($socket, 1024);
        
        // Send QUIT
        fputs($socket, "QUIT\r\n");
        
        fclose($socket);
        
        return substr($response, 0, 3) === '250';
        
    } catch (Exception $e) {
        error_log("SMTP Error: " . $e->getMessage());
        return false;
    }
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
