<?php
/**
 * Test Email Configuration
 * Diagnoses email sending capability
 */

header('Content-Type: text/html; charset=UTF-8');

echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Email Configuration Test - Adeptskil</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #667eea; }
        .section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #667eea; }
        .section h2 { margin-top: 0; color: #2d3748; }
        .ok { color: #10b981; font-weight: bold; }
        .warning { color: #f59e0b; font-weight: bold; }
        .error { color: #ef4444; font-weight: bold; }
        code { background: #eee; padding: 2px 6px; border-radius: 3px; }
        .test-button { 
            background: #667eea; 
            color: white; 
            padding: 10px 20px; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 16px;
        }
        .test-button:hover { background: #764ba2; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📧 Email Configuration Test</h1>
        
        <div class="section">
            <h2>1. PHP Mail Function Status</h2>
HTML;

// Check if mail function exists
if (function_exists('mail')) {
    echo '<p class="ok">✓ mail() function is enabled</p>';
} else {
    echo '<p class="error">✗ mail() function is NOT enabled</p>';
}

// Check mail configuration
$mailConfig = ini_get('sendmail_path');
echo '<p><strong>Sendmail Path:</strong> ' . ($mailConfig ? '<code>'.$mailConfig.'</code>' : '<span class="warning">Not configured</span>') . '</p>';

echo <<<HTML
        </div>
        
        <div class="section">
            <h2>2. PHP Configuration</h2>
            <p><strong>PHP Version:</strong> PHP {$_SERVER['SERVER_SOFTWARE']}</p>
            <p><strong>OS:</strong> {$_SERVER['REQUEST_SCHEME']}</p>
            <p><strong>Script Path:</strong> {$_SERVER['SCRIPT_FILENAME']}</p>
        </div>

        <div class="section">
            <h2>3. Send Test Email</h2>
            <p>Click the button below to send a test email to your configured email address.</p>
            <form method="POST">
                <input type="email" name="test_email" placeholder="Enter test email address" required style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 4px;">
                <button type="submit" name="send_test" class="test-button">📨 Send Test Email</button>
            </form>
HTML;

// Handle test email submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_test'])) {
    $test_email = filter_var($_POST['test_email'], FILTER_SANITIZE_EMAIL);
    
    if (filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
        $subject = 'Adeptskil Test Email - ' . date('Y-m-d H:i:s');
        $message = <<<MSG
<!DOCTYPE html>
<html>
<head><style>
body { font-family: Arial, sans-serif; color: #333; }
.container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f9f9f9; }
.header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; }
.content { padding: 20px; }
</style></head>
<body>
<div class="container">
    <div class="header">
        <h1>✓ Test Email Successful!</h1>
    </div>
    <div class="content">
        <p>This is a test email from Adeptskil's PHP mail configuration.</p>
        <p><strong>Time Sent:</strong> {$subject}</p>
        <p><strong>Test Recipient:</strong> {$test_email}</p>
        <p>If you received this email, your PHP mail configuration is working correctly!</p>
    </div>
</div>
</body>
</html>
MSG;
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: info@adeptskil.com\r\n";
        
        $result = mail($test_email, $subject, $message, $headers);
        
        if ($result) {
            echo '<div style="background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin-top: 10px; border-radius: 4px;">';
            echo '<p class="ok">✓ Test email sent successfully to ' . $test_email . '</p>';
            echo '<p>Check your inbox and spam folder. If you received it, your mail system is working!</p>';
            echo '</div>';
        } else {
            echo '<div style="background: #fee2e2; border-left: 4px solid #ef4444; padding: 15px; margin-top: 10px; border-radius: 4px;">';
            echo '<p class="error">✗ Failed to send test email</p>';
            echo '<p>Your PHP mail() function may not be configured correctly. Check your server\'s mail service.</p>';
            echo '</div>';
        }
    } else {
        echo '<p class="error">✗ Invalid email address</p>';
    }
}

echo <<<HTML
        </div>

        <div class="section">
            <h2>4. Quick Diagnostics</h2>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li><strong>Mail Function:</strong> {$ok}</li>
                <li><strong>Directory Writable:</strong> 
HTML;

if (is_dir('emails') && is_writable('emails')) {
    echo '<span class="ok">✓ Yes</span>';
} else {
    echo '<span class="warning">⚠ Check permissions or create emails/ folder</span>';
}

echo <<<HTML
                </li>
                <li><strong>Error Logs:</strong> Check <code>emails/confirmation_log.txt</code></li>
            </ul>
        </div>

        <div class="section">
            <h2>5. Troubleshooting</h2>
            <p>If emails are not being sent:</p>
            <ul style="margin: 10px 0; padding-left: 20px;">
                <li>Check <code>emails/confirmation_log.txt</code> for error messages</li>
                <li>Verify your email address in enrollment forms</li>
                <li>Check spam/junk folders</li>
                <li>Ensure emails/ directory exists and is writable</li>
                <li>Contact your hosting provider about mail service configuration</li>
            </ul>
        </div>
    </div>
</body>
</html>
HTML;
?>
