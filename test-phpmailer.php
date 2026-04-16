<?php
/**
 * PHPMailer Test Script
 * Tests email sending via GoDaddy SMTP with PHPMailer
 */

require_once(__DIR__ . '/config.php');

echo "=== PHPMailer Email Test ===\n\n";

// Test email parameters
$testEmail = 'manjunath.bgm@gmail.com'; // Change this to YOUR email for testing
$testSubject = 'Adeptskil - PHPMailer Test';
$testBody = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; }
        .container { max-width: 600px; margin: 20px auto; border: 1px solid #ddd; border-radius: 8px; padding: 20px; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ PHPMailer Test Email</h1>
        </div>
        
        <p>Hello,</p>
        
        <p>This is a test email from <strong>Adeptskil</strong> using PHPMailer with GoDaddy SMTP.</p>
        
        <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <p><strong>Configuration Details:</strong></p>
            <ul>
                <li>SMTP Host: smtpout.secureserver.net</li>
                <li>SMTP Port: 465</li>
                <li>Encryption: SSL</li>
                <li>Sent At: <?php echo date('Y-m-d H:i:s'); ?></li>
            </ul>
        </div>
        
        <p>If you received this email, PHPMailer is working correctly!</p>
        
        <p>Best regards,<br>
        <strong>Adeptskil Team</strong>
        </p>
    </div>
</body>
</html>
HTML;

// Send test email
echo "Sending test email to: $testEmail\n";
echo "Subject: $testSubject\n";
echo "---\n";

$result = sendEmail($testEmail, $testSubject, $testBody);

if ($result) {
    echo "✓ Email sent successfully!\n";
    echo "\nCheck your email inbox for the test message.\n";
} else {
    echo "✗ Email sending failed.\n";
    echo "Check error_log and mail_log.txt for details.\n";
}

echo "\n=== Test Complete ===\n";
?>
