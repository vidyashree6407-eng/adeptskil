<?php
/**
 * Email Delivery Diagnostic Test
 * This script tests the complete email sending pipeline
 * Access: https://adeptskil.com/test-email-delivery.php
 */

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/db_config.php');

header('Content-Type: text/html; charset=utf-8');

echo "<html><head><title>Email Delivery Test</title>";
echo "<style>body { font-family: Arial; margin: 20px; } .success { color: green; } .error { color: red; } .info { color: blue; } .box { border: 1px solid #ccc; padding: 15px; margin: 10px 0; } pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }</style>";
echo "</head><body>";

echo "<h1>Email Delivery Diagnostic Test</h1>";

// Test 1: Check MAIL_METHOD
echo "<div class='box'>";
echo "<h3>Test 1: Mail Configuration</h3>";
echo "<p><strong>MAIL_METHOD:</strong> " . MAIL_METHOD . "</p>";
echo "<p><strong>SMTP_HOST:</strong> " . SMTP_HOST . "</p>";
echo "<p><strong>SMTP_PORT:</strong> " . SMTP_PORT . "</p>";
echo "<p><strong>SMTP_USERNAME:</strong> " . SMTP_USERNAME . "</p>";
echo "<p><strong>MAIL_STORAGE_DIR:</strong> " . MAIL_STORAGE_DIR . "</p>";

if (is_dir(MAIL_STORAGE_DIR)) {
    echo "<p class='success'>✓ Email storage directory exists</p>";
} else {
    echo "<p class='error'>✗ Email storage directory missing</p>";
}
echo "</div>";

// Test 2: Check PHPMailer
echo "<div class='box'>";
echo "<h3>Test 2: PHPMailer Library</h3>";
$phpmailer_path = __DIR__ . '/PHPMailer/src/PHPMailer.php';
if (file_exists($phpmailer_path)) {
    echo "<p class='success'>✓ PHPMailer library found</p>";
} else {
    echo "<p class='error'>✗ PHPMailer library NOT found at: $phpmailer_path</p>";
}
echo "</div>";

// Test 3: Database check
echo "<div class='box'>";
echo "<h3>Test 3: Database Enrollments</h3>";
try {
    $db = getDB();
    $stmt = $db->query("SELECT COUNT(*) as count FROM enrollments");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p class='success'>✓ Database connection OK</p>";
    echo "<p><strong>Total Enrollments:</strong> " . $result['count'] . "</p>";
    
    // Show recent enrollments
    $stmt = $db->query("SELECT invoice_id, email, course, payment_status FROM enrollments ORDER BY created_at DESC LIMIT 5");
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($enrollments) > 0) {
        echo "<p><strong>Recent Enrollments:</strong></p>";
        echo "<table border='1' cellpadding='10' style='width: 100%;'>";
        echo "<tr><th>Invoice</th><th>Email</th><th>Course</th><th>Status</th></tr>";
        foreach ($enrollments as $e) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($e['invoice_id']) . "</td>";
            echo "<td>" . htmlspecialchars($e['email']) . "</td>";
            echo "<td>" . htmlspecialchars($e['course']) . "</td>";
            echo "<td>" . htmlspecialchars($e['payment_status']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='info'>ℹ No enrollments in database yet</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
echo "</div>";

// Test 4: Send test email
echo "<div class='box'>";
echo "<h3>Test 4: Send Test Email</h3>";

$testEmail = $_GET['email'] ?? 'test@example.com';
$testSubject = "Adeptskil Email Delivery Test - " . date('Y-m-d H:i:s');
$testBody = <<<HTML
<!DOCTYPE html>
<html>
<head><title>Test Email</title></head>
<body>
<h2>Email Delivery Test</h2>
<p>This is a test email sent at: <strong>{datetime}</strong></p>
<p>If you received this, the email delivery is working!</p>
<p><strong>MAIL_METHOD:</strong> HTML;
echo str_replace('{datetime}', date('Y-m-d H:i:s'), $testBody) . "</p>\n";
echo "<p><strong>From Email:</strong> " . ADMIN_EMAIL . "</p>\n";
echo "</body>\n</html>";

$testBody = ob_get_clean();

echo "<p>Sending test email to: <strong>" . htmlspecialchars($testEmail) . "</strong></p>";
echo "<p>Subject: <strong>" . htmlspecialchars($testSubject) . "</strong></p>";

try {
    $result = sendEmail($testEmail, $testSubject, $testBody);
    
    if ($result) {
        echo "<p class='success'>✓ Test email sent successfully!</p>";
        
        // Check if email file was created
        $emailFiles = glob(MAIL_STORAGE_DIR . '/EMAIL-*.json');
        if (count($emailFiles) > 0) {
            $latestEmail = end($emailFiles);
            echo "<p class='success'>✓ Email stored in: " . basename($latestEmail) . "</p>";
        }
    } else {
        echo "<p class='error'>✗ Email sending returned false</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error sending email: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<p><em>Tip: Check your spam/junk folder for the test email</em></p>";
echo "</div>";

// Test 5: Email logs
echo "<div class='box'>";
echo "<h3>Test 5: Email Logs</h3>";

if (file_exists(MAIL_LOG_FILE)) {
    $logContent = file_get_contents(MAIL_LOG_FILE);
    $lines = array_slice(explode("\n", $logContent), -20); // Last 20 lines
    echo "<p><strong>Last log entries:</strong></p>";
    echo "<pre>" . htmlspecialchars(implode("\n", $lines)) . "</pre>";
} else {
    echo "<p class='info'>ℹ Mail log file not yet created</p>";
}

echo "</div>";

// Test 6: Simulate IPN payment
echo "<div class='box'>";
echo "<h3>Test 6: Simulate PayPal IPN Payment</h3>";
echo "<p>This will simulate a payment and trigger confirmation email:</p>";

if (isset($_GET['simulate'])) {
    // Create test enrollment first
    $testInvoice = 'TEST-' . time();
    $testEmail = 'test@gmail.com';
    $testName = 'Test Customer';
    $testCourse = 'Leadership Training';
    $testAmount = 100;
    
    try {
        $db = getDB();
        
        // Check if already exists
        $stmt = $db->prepare("SELECT * FROM enrollments WHERE invoice_id = ?");
        $stmt->execute([$testInvoice]);
        $existing = $stmt->fetch();
        
        if (!$existing) {
            $stmt = $db->prepare("
                INSERT INTO enrollments (
                    invoice_id, full_name, email, phone, company, city, course, amount, 
                    payment_method, payment_status, payment_id
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $testInvoice,
                $testName,
                $testEmail,
                '9999999999',
                'Test Company',
                'Test City',
                $testCourse,
                $testAmount,
                'paypal',
                'pending',
                'TEST-TXN-' . time()
            ]);
            
            echo "<p class='success'>✓ Test enrollment created: $testInvoice</p>";
        }
        
        // Now trigger the confirmation email
        require_once(__DIR__ . '/ipn_handler.php');
        
        // This would need the functions from ipn_handler, so let's call them directly
        if (function_exists('sendPaymentConfirmationEmails')) {
            sendPaymentConfirmationEmails($testName, $testEmail, $testCourse, $testAmount, $testInvoice);
            echo "<p class='success'>✓ Confirmation emails triggered for test enrollment</p>";
        } else {
            echo "<p class='error'>✗ sendPaymentConfirmationEmails function not found</p>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p><form method='GET'>";
    echo "<input type='hidden' name='simulate' value='1'>";
    echo "<button type='submit'>Simulate Payment & Send Test Email</button>";
    echo "</form></p>";
}

echo "</div>";

echo "<hr>";
echo "<p><strong>Manual Test:</strong> Send test email to custom address:</p>";
echo "<form method='GET'>";
echo "<input type='email' name='email' placeholder='email@example.com' value='vidyashree6407@gmail.com'>";
echo "<button type='submit'>Send Test Email</button>";
echo "</form>";

echo "</body></html>";
?>
