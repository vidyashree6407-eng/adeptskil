<?php
/**
 * SMTP Debug Test - Check GoDaddy connection and authentication
 */

// Set up error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load config
require_once(__DIR__ . '/config.php');

echo "=== SMTP Debug Test ===\n\n";

echo "Configuration:\n";
echo "  SMTP_HOST: " . SMTP_HOST . "\n";
echo "  SMTP_PORT: " . SMTP_PORT . "\n";
echo "  SMTP_USERNAME: " . SMTP_USERNAME . "\n";
echo "  SMTP_ENCRYPTION: " . SMTP_ENCRYPTION . "\n";
echo "  MAIL_METHOD: " . MAIL_METHOD . "\n\n";

// Test PHPMailer
try {
    echo "Loading PHPMailer...\n";
    require_once(__DIR__ . '/PHPMailer/src/PHPMailer.php');
    require_once(__DIR__ . '/PHPMailer/src/SMTP.php');
    require_once(__DIR__ . '/PHPMailer/src/Exception.php');
    
    echo "✓ PHPMailer files loaded\n\n";
    
    echo "Creating PHPMailer instance...\n";
    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
    
    echo "✓ PHPMailer instance created\n";
    echo "✓ Initializing SMTP configuration...\n\n";
    
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->Port = SMTP_PORT;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = 'ssl';
    $mail->Timeout = 10;
    $mail->SMTPDebug = 2;
    
    // Email content
    $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
    $mail->addAddress('manjunath.bgm@gmail.com'); // Change to your test email!
    $mail->Subject = 'Test Email - ' . date('Y-m-d H:i:s');
    $mail->Body = '<h1>SMTP Test</h1><p>This is a test email from Adeptskil using PHPMailer.</p>';
    $mail->AltBody = 'This is a test email from Adeptskil using PHPMailer.';
    $mail->isHTML(true);
    
    echo "Attempting to send email...\n";
    echo "---\n";
    
    if ($mail->send()) {
        echo "---\n";
        echo "✓ Email sent successfully!\n";
        echo "Check your test email inbox.\n";
    } else {
        echo "---\n";
        echo "✗ Email send failed!\n";
        echo "Error: " . $mail->ErrorInfo . "\n";
    }
    
} catch (\PHPMailer\PHPMailer\Exception $e) {
    echo "✗ PHPMailer Exception: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "✗ General Exception: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>
