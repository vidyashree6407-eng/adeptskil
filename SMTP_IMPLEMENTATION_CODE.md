# SMTP Implementation Code - Full Technical Details

## 🔧 What Needs to Be Modified

Your current `config.php` has file-based storage. Here's what needs to be added for SMTP support.

---

## Option 1: Simple Addition (Just Add to config.php)

### Add these constants after line 15:

```php
// SMTP Configuration (Add these lines to config.php)
define('MAIL_METHOD', 'smtp');  // Change from 'file' to 'smtp'
define('SMTP_HOST', 'smtp-relay.brevo.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-brevo-email@example.com');
define('SMTP_PASSWORD', 'your-brevo-api-key');
define('SMTP_FROM_EMAIL', 'info@adeptskil.com');
define('SMTP_FROM_NAME', 'Adeptskil');
```

### Then, replace the sendEmail() function with this:

```php
/**
 * Send email - supports SMTP and file storage
 * Works on production and local development
 */
function sendEmail($to, $subject, $body, $from = null, $replyTo = null) {
    if ($from === null) {
        $from = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : ADMIN_EMAIL;
    }
    
    // Always log the email
    logEmail($to, $subject, $body, $from);
    
    // Create email record
    $emailRecord = [
        'id' => 'EMAIL-' . date('YmdHis') . '-' . random_int(10000, 99999),
        'timestamp' => date('Y-m-d H:i:s'),
        'to' => $to,
        'from' => $from,
        'subject' => $subject,
        'body' => $body,
        'replyTo' => $replyTo,
        'status' => 'queued'
    ];
    
    // Save email to file storage (backup)
    $filename = MAIL_STORAGE_DIR . '/' . $emailRecord['id'] . '.json';
    @file_put_contents($filename, json_encode($emailRecord, JSON_PRETTY_PRINT));
    
    // Send based on method
    if (MAIL_METHOD === 'smtp') {
        $sent = sendViaSMTP($to, $subject, $body, $from, $replyTo);
        $emailRecord['status'] = $sent ? 'sent' : 'failed';
    } else if (MAIL_METHOD === 'php') {
        $sent = sendViaPhpMail($to, $subject, $body, $from, $replyTo);
        $emailRecord['status'] = $sent ? 'sent' : 'failed';
    } else {
        // File method - just saved above
        $sent = true;
        $emailRecord['status'] = 'stored';
    }
    
    // Update email record with final status
    @file_put_contents($filename, json_encode($emailRecord, JSON_PRETTY_PRINT));
    
    return $sent !== false;
}

/**
 * Send via SMTP (Brevo, SendGrid, etc.)
 * Handles SMTP connection and authentication
 */
function sendViaSMTP($to, $subject, $body, $from, $replyTo = null) {
    try {
        // Validate credentials
        if (!defined('SMTP_USERNAME') || !defined('SMTP_PASSWORD')) {
            error_log("SMTP: Credentials not configured in config.php");
            return false;
        }
        
        if (empty(SMTP_USERNAME) || empty(SMTP_PASSWORD)) {
            error_log("SMTP: Credentials are empty in config.php");
            return false;
        }
        
        // Connect to SMTP server
        $socket = @fsockopen(
            SMTP_HOST,
            SMTP_PORT,
            $errno,
            $errstr,
            10  // timeout
        );
        
        if (!$socket) {
            error_log("SMTP Error: Cannot connect to " . SMTP_HOST . ":" . SMTP_PORT . " - $errstr ($errno)");
            return false;
        }
        
        // Read server greeting
        $response = fgets($socket, 1024);
        if (substr($response, 0, 3) !== '220') {
            error_log("SMTP: Bad server greeting: $response");
            @fclose($socket);
            return false;
        }
        
        // Send EHLO
        fputs($socket, "EHLO adeptskil.com\r\n");
        while (trim($response = fgets($socket, 1024))) {
            if (substr($response, 3, 1) === ' ') break;
        }
        
        // Start TLS encryption
        fputs($socket, "STARTTLS\r\n");
        $response = fgets($socket, 1024);
        
        if (substr($response, 0, 3) !== '220') {
            error_log("SMTP: STARTTLS failed: $response");
            @fclose($socket);
            return false;
        }
        
        // Upgrade connection to TLS
        stream_context_set_option($socket, 'ssl', 'verify_peer', false);
        stream_context_set_option($socket, 'ssl', 'verify_peer_name', false);
        
        if (!@stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            error_log("SMTP: Failed to enable TLS");
            @fclose($socket);
            return false;
        }
        
        // Read response after STARTTLS
        $response = fgets($socket, 1024);
        
        // Send EHLO again after TLS
        fputs($socket, "EHLO adeptskil.com\r\n");
        while (trim($response = fgets($socket, 1024))) {
            if (substr($response, 3, 1) === ' ') break;
        }
        
        // Authenticate using LOGIN method
        fputs($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 1024);
        
        if (substr($response, 0, 3) !== '334') {
            error_log("SMTP: AUTH LOGIN not supported: $response");
            @fclose($socket);
            return false;
        }
        
        // Send base64-encoded username
        fputs($socket, base64_encode(SMTP_USERNAME) . "\r\n");
        $response = fgets($socket, 1024);
        
        if (substr($response, 0, 3) !== '334') {
            error_log("SMTP: Username rejected: $response");
            @fclose($socket);
            return false;
        }
        
        // Send base64-encoded password
        fputs($socket, base64_encode(SMTP_PASSWORD) . "\r\n");
        $response = fgets($socket, 1024);
        
        if (substr($response, 0, 3) !== '235') {
            error_log("SMTP: Authentication failed: $response");
            @fclose($socket);
            return false;
        }
        
        // Send FROM command
        fputs($socket, "MAIL FROM: <$from>\r\n");
        $response = fgets($socket, 1024);
        
        // Send TO command
        fputs($socket, "RCPT TO: <$to>\r\n");
        $response = fgets($socket, 1024);
        
        // Send DATA command
        fputs($socket, "DATA\r\n");
        $response = fgets($socket, 1024);
        
        if (substr($response, 0, 3) !== '354') {
            error_log("SMTP: DATA command failed: $response");
            @fclose($socket);
            return false;
        }
        
        // Build email headers
        $headers = "From: " . SMTP_FROM_NAME . " <$from>\r\n";
        $headers .= "To: $to\r\n";
        $headers .= "Subject: $subject\r\n";
        
        // Check if body is HTML
        if (strpos($body, '<html') !== false || strpos($body, '<!DOCTYPE') !== false) {
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        } else {
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        }
        
        if ($replyTo) {
            $headers .= "Reply-To: $replyTo\r\n";
        }
        
        $headers .= "Date: " . date('r') . "\r\n";
        $headers .= "Message-ID: <" . time() . "@adeptskil.com>\r\n";
        
        // Send headers and body
        fputs($socket, $headers . "\r\n" . $body . "\r\n.\r\n");
        $response = fgets($socket, 1024);
        
        if (substr($response, 0, 3) !== '250') {
            error_log("SMTP: Message rejected: $response");
            @fclose($socket);
            return false;
        }
        
        // Send QUIT
        fputs($socket, "QUIT\r\n");
        fgets($socket, 1024);
        
        // Close connection
        @fclose($socket);
        
        error_log("SMTP: Email sent successfully to $to");
        return true;
        
    } catch (Exception $e) {
        error_log("SMTP Exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Send via PHP mail() function
 * Fallback for servers with mail configured
 */
function sendViaPhpMail($to, $subject, $body, $from, $replyTo = null) {
    $headers = "From: $from\r\n";
    
    if (strpos($body, '<html') !== false || strpos($body, '<!DOCTYPE') !== false) {
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    } else {
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    }
    
    if ($replyTo) {
        $headers .= "Reply-To: $replyTo\r\n";
    }
    
    $result = @mail($to, $subject, $body, $headers);
    
    if ($result) {
        error_log("PHP mail: Email sent to $to");
    } else {
        error_log("PHP mail: Failed to send to $to");
    }
    
    return $result;
}

/**
 * Log email attempts for debugging
 */
function logEmail($to, $subject, $body, $from) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[{$timestamp}] TO: $to | SUBJECT: $subject | FROM: $from\n";
    $logEntry .= "Body length: " . strlen($body) . " bytes\n";
    $logEntry .= "---\n" . substr($body, 0, 200) . "...\n---\n\n";
    
    $logFile = MAIL_LOG_FILE;
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
}
```

---

## Option 2: Production-Ready Full Implementation

If you want more features, replace the entire config.php with better structure:

```php
<?php
/**
 * Adeptskil Configuration - Production Ready
 * Supports SMTP, PHP mail(), and file-based fallback
 */

// ===== EMAIL CONFIGURATION =====
define('ADMIN_EMAIL', 'info@adeptskil.com');
define('SITE_NAME', 'Adeptskil');
define('SITE_URL', 'https://adeptskil.com');

// ===== MAIL METHOD SELECTION =====
// Options: 'smtp', 'php', 'file'
// PRODUCTION: Use 'smtp' (Brevo) or keep as fallback
define('MAIL_METHOD', 'smtp');

// ===== SMTP CONFIGURATION =====
define('SMTP_HOST', 'smtp-relay.brevo.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-brevo-email@example.com');
define('SMTP_PASSWORD', 'your-brevo-api-key');
define('SMTP_FROM_EMAIL', 'info@adeptskil.com');
define('SMTP_FROM_NAME', 'Adeptskil');
define('SMTP_TIMEOUT', 10);

// ===== FILE STORAGE =====
define('MAIL_LOG_FILE', __DIR__ . '/mail_log.txt');
define('MAIL_STORAGE_DIR', __DIR__ . '/emails');

// ===== DIRECTORIES =====
define('DATA_DIR', __DIR__ . '/data');
define('LOGS_DIR', __DIR__ . '/logs');

// Create directories if needed
@mkdir(MAIL_STORAGE_DIR, 0755, true);
@mkdir(DATA_DIR, 0755, true);
@mkdir(LOGS_DIR, 0755, true);

// ... rest of code ...
```

---

## 🧪 Testing the SMTP Implementation

### Test File (create test-smtp.php):

```php
<?php
require_once(__DIR__ . '/config.php');

echo "=== SMTP Configuration Test ===\n\n";

echo "Mail Method: " . MAIL_METHOD . "\n";
echo "SMTP Host: " . SMTP_HOST . "\n";
echo "SMTP Port: " . SMTP_PORT . "\n";
echo "SMTP Username: " . SMTP_USERNAME . "\n";
echo "From Email: " . SMTP_FROM_EMAIL . "\n\n";

echo "Sending test email...\n";

$result = sendEmail(
    'your-test-email@example.com',
    'Test Email - Adeptskil SMTP',
    '<html><body><h1>Test Email</h1><p>If you see this, SMTP is working!</p></body></html>',
    SMTP_FROM_EMAIL,
    ADMIN_EMAIL
);

if ($result) {
    echo "✅ Email sent successfully!\n";
    echo "Check your inbox and /emails/ directory\n";
} else {
    echo "❌ Email sending failed\n";
    echo "Check /mail_log.txt for details\n";
}

echo "\nCheck these files:\n";
echo "- /mail_log.txt\n";
echo "- /emails/ directory\n";
?>
```

### Run test:
```bash
php test-smtp.php
```

---

## 🔍 Debugging

### Check logs:
```bash
cat /mail_log.txt
```

### Check stored emails:
```bash
ls -la /emails/
cat /emails/EMAIL-*.json
```

### Check database:
```bash
# If using database
sqlite3 enrollments.db "SELECT * FROM enrollments WHERE email = 'test@example.com';"
```

---

## 📊 Summary of Changes

| Component | Before | After |
|-----------|--------|-------|
| **Method** | file | smtp |
| **Real emails** | ❌ No | ✅ Yes |
| **Error handling** | Basic | Advanced |
| **TLS encryption** | No | ✅ Yes |
| **Logging** | Simple | Detailed |
| **Fallback** | None | File backup |
| **Cost** | $0 | $0 (free tier) |

---

**Status:** Ready to implement SMTP support!
