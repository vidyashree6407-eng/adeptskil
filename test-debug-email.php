<?php
/**
 * Debug Email Sending - Shows SMTP errors
 */

require_once(__DIR__ . '/config.php');

echo "=== Email Debugging ===\n\n";

// Enable PHPMailer debug output
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "1. Testing SMTP connection...\n";

try {
    require_once(__DIR__ . '/PHPMailer/src/PHPMailer.php');
    require_once(__DIR__ . '/PHPMailer/src/SMTP.php');
    require_once(__DIR__ . '/PHPMailer/src/Exception.php');
    
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->Port = SMTP_PORT;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = 'ssl';
    $mail->Timeout = 10;
    $mail->SMTPDebug = 2; // Show all debug output
    
    echo "\n2. Setting up email...\n";
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress('vidyashree6407@gmail.com');
    $mail->Subject = 'Debug Test - ' . date('Y-m-d H:i:s');
    $mail->Body = '<h1>Test</h1><p>This is a debug test email.</p>';
    $mail->AltBody = 'This is a debug test email.';
    $mail->isHTML(true);
    
    echo "\n3. Sending...\n";
    echo "---\n";
    
    ob_start();
    $result = @$mail->send();
    $output = ob_get_clean();
    
    echo $output;
    echo "---\n";
    
    if ($result) {
        echo "\n✓ EMAIL SENT SUCCESSFULLY\n";
    } else {
        echo "\n✗ EMAIL SEND FAILED\n";
        echo "Error: " . $mail->ErrorInfo . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ EXCEPTION: " . $e->getMessage() . "\n";
}

echo "\n=== Check Gmail Spam Folder ===\n";
echo "If you don't see it in inbox, check:\n";
echo "1. Gmail Spam/Junk folder\n";
echo "2. Promotions tab\n";
echo "3. Other emails folder\n";
echo "4. Check that vidyashree6407@gmail.com is the right address\n";
?>
