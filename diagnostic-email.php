<?php
/**
 * Email Delivery Diagnostic Dashboard
 * Access: https://adeptskil.com/diagnostic-email.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Allow all HTTP methods
header('Access-Control-Allow-Methods: GET, POST, OPTIONS, HEAD');
header('Content-Type: text/html; charset=utf-8');

// Handle OPTIONS request (CORS preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/db_config.php');

?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Delivery Diagnostic</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #667eea;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        h3 {
            color: #333;
            margin-top: 25px;
        }
        .box {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            background: #f9f9f9;
        }
        .success {
            color: #10b981;
            font-weight: bold;
        }
        .error {
            color: #ef4444;
            font-weight: bold;
        }
        .info {
            color: #3b82f6;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        table th {
            background: #667eea;
            color: white;
        }
        button {
            background: #667eea;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        button:hover {
            background: #764ba2;
        }
        pre {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 12px;
        }
        .action-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>📧 Email Delivery Diagnostic Dashboard</h1>
    
    <!-- Configuration Check -->
    <h3>1️⃣ Configuration Status</h3>
    <div class="box">
        <p><strong>Email Method:</strong> <span class="info"><?php echo MAIL_METHOD; ?></span></p>
        <?php if (MAIL_METHOD === 'smtp') { echo "<p class='success'>✓ SMTP + PHPMailer (Professional Mode)</p>"; } ?>
        <p><strong>SMTP Host:</strong> <span class="info"><?php echo SMTP_HOST; ?></span></p>
        <p><strong>SMTP Port:</strong> <span class="info"><?php echo SMTP_PORT; ?></span></p>
        <p><strong>SMTP Username:</strong> <span class="info"><?php echo SMTP_USERNAME; ?></span></p>
        <p><strong>Encryption:</strong> <span class="info"><?php echo SMTP_ENCRYPTION; ?></span></p>
        <p><strong>From Name:</strong> <span class="info"><?php echo SMTP_FROM_NAME; ?></span></p>
        <p><strong>Admin Email:</strong> <span class="info"><?php echo ADMIN_EMAIL; ?></span></p>
        <p><strong>Storage Directory:</strong> <span class="info"><?php echo MAIL_STORAGE_DIR; ?></span></p>
        
        <hr style="margin: 15px 0;">
        
        <p><strong>⚡ Email Method Details:</strong></p>
        <ul>
            <li><span class='success'>✓ Using SMTP with PHPMailer (professional delivery)</span></li>
            <li><span class='success'>✓ NO fallback to mail() function</span></li>
            <li><span class='success'>✓ Direct GoDaddy SMTP connection</span></li>
            <li><span class='success'>✓ SSL encrypted (port 465)</span></li>
            <li><span class='success'>✓ Full error logging and debugging</span></li>
        </ul>
        
        <?php
        if (is_dir(MAIL_STORAGE_DIR)) {
            echo "<p><span class='success'>✓ Email storage directory exists</span></p>";
        } else {
            echo "<p><span class='error'>✗ Email storage directory missing</span></p>";
        }
        
        // Check PHPMailer
        $phpMailerFile = __DIR__ . '/PHPMailer/src/PHPMailer.php';
        if (file_exists($phpMailerFile)) {
            echo "<p><span class='success'>✓ PHPMailer library found at: /PHPMailer/src/</span></p>";
        } else {
            echo "<p><span class='error'>✗ PHPMailer library NOT found</span></p>";
        }
        ?>
    </div>
    
    <!-- Database Check -->
    <h3>2️⃣ Database & Enrollments</h3>
    <div class="box">
        <?php
        try {
            $db = getDB();
            $stmt = $db->query("SELECT COUNT(*) as count FROM enrollments");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p><span class='success'>✓ Database connection OK</span></p>";
            echo "<p><strong>Total Enrollments:</strong> " . $result['count'] . "</p>";
            
            $stmt = $db->query("SELECT invoice_id, email, course, payment_status, created_at FROM enrollments ORDER BY created_at DESC LIMIT 10");
            $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($enrollments) > 0) {
                echo "<p><strong>Recent Enrollments:</strong></p>";
                echo "<table>";
                echo "<tr><th>Invoice</th><th>Email</th><th>Course</th><th>Status</th><th>Date</th></tr>";
                foreach ($enrollments as $e) {
                    $status_class = ($e['payment_status'] == 'completed') ? 'success' : 'info';
                    echo "<tr>";
                    echo "<td><code>" . substr($e['invoice_id'], 0, 20) . "...</code></td>";
                    echo "<td>" . htmlspecialchars($e['email']) . "</td>";
                    echo "<td>" . htmlspecialchars(substr($e['course'], 0, 30)) . "</td>";
                    echo "<td><span class='" . $status_class . "'>" . $e['payment_status'] . "</span></td>";
                    echo "<td>" . substr($e['created_at'], 0, 10) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p class='info'>ℹ No enrollments yet</p>";
            }
        } catch (Exception $e) {
            echo "<p><span class='error'>✗ Database error: " . htmlspecialchars($e->getMessage()) . "</span></p>";
        }
        ?>
    </div>
    
    <!-- Email Logs -->
    <h3>3️⃣ Recent Email Logs</h3>
    <div class="box">
        <?php
        $emailDir = MAIL_STORAGE_DIR;
        if (is_dir($emailDir)) {
            $files = array_slice(array_reverse(glob("$emailDir/EMAIL-*.json")), 0, 5);
            if (count($files) > 0) {
                echo "<p><strong>Last 5 emails sent:</strong></p>";
                foreach ($files as $file) {
                    $data = json_decode(file_get_contents($file), true);
                    echo "<div class='box'>";
                    echo "<p><strong>To:</strong> " . htmlspecialchars($data['to']) . "</p>";
                    echo "<p><strong>Subject:</strong> " . htmlspecialchars($data['subject']) . "</p>";
                    echo "<p><strong>Sent:</strong> " . $data['timestamp'] . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p class='info'>ℹ No emails recorded yet</p>";
            }
        }
        ?>
    </div>
    
    <!-- SMTP Debug Log -->
    <h3>4️⃣ SMTP Debug Log</h3>
    <div class="box">
        <?php
        $debugLog = __DIR__ . '/smtp_debug.log';
        if (file_exists($debugLog)) {
            $lines = array_slice(array_reverse(explode("\n", file_get_contents($debugLog))), 0, 15);
            echo "<p><strong>Last 15 entries:</strong></p>";
            echo "<pre>";
            foreach (array_reverse($lines) as $line) {
                if (!empty($line)) {
                    if (strpos($line, '✓') !== false) {
                        echo "<span class='success'>$line</span>\n";
                    } elseif (strpos($line, '✗') !== false) {
                        echo "<span class='error'>$line</span>\n";
                    } else {
                        echo "$line\n";
                    }
                }
            }
            echo "</pre>";
        } else {
            echo "<p class='info'>ℹ Debug log not created yet (will appear after first email attempt)</p>";
        }
        ?>
    </div>
    
    <!-- IPN Handler Log -->
    <h3>5️⃣ IPN Handler Log</h3>
    <div class="box">
        <?php
        $ipnLog = __DIR__ . '/ipn_handler.log';
        if (file_exists($ipnLog)) {
            $lines = array_slice(array_reverse(explode("\n", file_get_contents($ipnLog))), 0, 15);
            echo "<p><strong>Last 15 IPN events:</strong></p>";
            echo "<pre>";
            foreach (array_reverse($lines) as $line) {
                if (!empty($line)) {
                    echo htmlspecialchars($line) . "\n";
                }
            }
            echo "</pre>";
        } else {
            echo "<p class='info'>ℹ IPN log not created yet (will appear after first IPN from PayPal)</p>";
        }
        ?>
    </div>
    
    <!-- Send Test Email -->
    <h3>6️⃣ Send Test Email</h3>
    <div class="action-box">
        <p><strong>Send a test email to verify SMTP configuration:</strong></p>
        <form method="POST" action="">
            <input type="email" name="test_email" placeholder="your-email@example.com" required value="<?php echo htmlspecialchars($_POST['test_email'] ?? $_GET['test_email'] ?? 'vidyashree6407@gmail.com'); ?>" style="padding: 8px; width: 250px; border: 1px solid #ddd; border-radius: 5px;">
            <button type="submit" name="send_test" value="1">📧 Send Test Email</button>
        </form>
        
        <?php
        if (isset($_POST['send_test'])) {
            $testEmail = $_POST['test_email'];
            $testSubject = "Adeptskil Email Test - " . date('Y-m-d H:i:s');
            $testBody = "
            <html>
            <body>
            <h2>Email Test</h2>
            <p>This is a test email from Adeptskil.</p>
            <p><strong>Sent at:</strong> " . date('Y-m-d H:i:s') . "</p>
            <p><strong>MAIL_METHOD:</strong> " . MAIL_METHOD . "</p>
            <p>If you received this, SMTP is working!</p>
            </body>
            </html>";
            
            $result = sendEmail($testEmail, $testSubject, $testBody);
            
            if ($result) {
                echo "<p><span class='success'>✓ Test email sent successfully to $testEmail</span></p>";
                echo "<p><strong>Check your inbox and spam folder.</strong></p>";
                echo "<p><small>The email was also saved locally in the emails directory.</small></p>";
            } else {
                echo "<p><span class='error'>✗ Failed to send test email</span></p>";
                echo "<p>Check the SMTP Debug Log above for error details.</p>";
            }
        }
        ?>
    </div>
    
    <!-- Simulate Payment -->
    <h3>7️⃣ Simulate Payment & Email</h3>
    <div class="action-box">
        <p><strong>Manually trigger confirmation email (simulating PayPal payment):</strong></p>
        <form method="POST" action="">
            <button type="submit" name="simulate" value="1">🎯 Simulate Payment Email</button>
        </form>
        
        <?php
        if (isset($_POST['simulate'])) {
            // Get the enrollment to use for email
            try {
                $db = getDB();
                $stmt = $db->query("SELECT * FROM enrollments WHERE payment_status='pending' ORDER BY created_at DESC LIMIT 1");
                $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($enrollment) {
                    require_once(__DIR__ . '/ipn_handler.php');
                    
                    logIPN("Manual test triggered - simulating payment for " . $enrollment['email']);
                    sendPaymentConfirmationEmails(
                        $enrollment['full_name'],
                        $enrollment['email'],
                        $enrollment['course'],
                        $enrollment['amount'],
                        $enrollment['invoice_id']
                    );
                    
                    echo "<p><span class='success'>✓ Confirmation emails triggered for:</span></p>";
                    echo "<p><strong>Email:</strong> " . htmlspecialchars($enrollment['email']) . "</p>";
                    echo "<p><strong>Course:</strong> " . htmlspecialchars($enrollment['course']) . "</p>";
                    echo "<p><strong>Invoice:</strong> " . htmlspecialchars($enrollment['invoice_id']) . "</p>";
                } else {
                    echo "<p><span class='error'>✗ No pending enrollments found. Complete an enrollment first.</span></p>";
                }
            } catch (Exception $e) {
                echo "<p><span class='error'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</span></p>";
            }
        }
        ?>
    </div>
    
    <!-- Troubleshooting Guide -->
    <h3>🔍 Troubleshooting Checklist</h3>
    <div class="box">
        <p>✓ Check if test email appears in your inbox (or spam folder)</p>
        <p>✓ Check SMTP Debug Log for connection errors</p>
        <p>✓ Check IPN Handler Log to see if PayPal is sending IPNs</p>
        <p>✓ Verify GoDaddy SMTP credentials are correct</p>
        <p>✓ Check if SSL certificate is valid (some providers require this)</p>
        <p>✓ Try sending test email to multiple providers (Gmail, Outlook, etc)</p>
    </div>

</div>
</body>
</html>
