<?php
/**
 * SMTP Connection Test
 * Tests GoDaddy SMTP configuration
 */

require_once(__DIR__ . '/config.php');

$results = [];

// Test 1: Check SMTP constants are defined
$results['SMTP Constants'] = [
    'SMTP_HOST' => defined('SMTP_HOST') ? SMTP_HOST : 'NOT DEFINED',
    'SMTP_PORT' => defined('SMTP_PORT') ? SMTP_PORT : 'NOT DEFINED',
    'SMTP_USERNAME' => defined('SMTP_USERNAME') ? 'CONFIGURED' : 'NOT DEFINED',
    'SMTP_PASSWORD' => defined('SMTP_PASSWORD') ? 'CONFIGURED' : 'NOT DEFINED',
    'MAIL_METHOD' => MAIL_METHOD
];

// Test 2: Try socket connection
$results['Socket Connection'] = [];
$socket = @fsockopen(SMTP_HOST, SMTP_PORT, $errno, $errstr, 10);
if ($socket) {
    $results['Socket Connection']['Status'] = 'SUCCESS - Connected to ' . SMTP_HOST . ':' . SMTP_PORT;
    $banner = fgets($socket, 512);
    $results['Socket Connection']['Server Banner'] = $banner;
    fclose($socket);
} else {
    $results['Socket Connection']['Status'] = 'FAILED - ' . $errstr . ' (Error: ' . $errno . ')';
}

// Test 3: Send test email
$results['Email Test'] = [];
$testTo = ADMIN_EMAIL;
$testSubject = 'GoDaddy SMTP Configuration Test - ' . date('Y-m-d H:i:s');
$testBody = "
<html>
<head><title>SMTP Configuration Test</title></head>
<body>
<h2>GoDaddy SMTP Configuration Test</h2>
<p><strong>Status:</strong> Configuration appears to be working!</p>
<p><strong>Timestamp:</strong> " . date('Y-m-d H:i:s') . "</p>
<p><strong>Mail Method:</strong> " . MAIL_METHOD . "</p>
<p><strong>From:</strong> " . SMTP_FROM_NAME . " &lt;" . SMTP_FROM_EMAIL . "&gt;</p>
<p><strong>To:</strong> $testTo</p>
<hr>
<p>This is an automated test email. If you received this, your GoDaddy SMTP configuration is working correctly.</p>
</body>
</html>
";

$emailSent = sendEmail($testTo, $testSubject, $testBody, SMTP_FROM_EMAIL);
$results['Email Test']['Send Result'] = $emailSent ? 'Email queued for sending' : 'Failed to queue email';

// Test 4: Check email file storage
$results['File Storage'] = [];
if (is_dir(MAIL_STORAGE_DIR)) {
    $files = @scandir(MAIL_STORAGE_DIR);
    $emailCount = count(array_filter($files, function($f) { return strpos($f, '.json'); }));
    $results['File Storage']['Status'] = 'OK - ' . $emailCount . ' emails stored';
    $results['File Storage']['Directory'] = MAIL_STORAGE_DIR;
} else {
    $results['File Storage']['Status'] = 'Directory not found';
}

// Test 5: Check log file
$results['Log File'] = [];
if (is_file(MAIL_LOG_FILE)) {
    $size = filesize(MAIL_LOG_FILE);
    $results['Log File']['Status'] = 'OK - ' . $size . ' bytes';
    $results['Log File']['Path'] = MAIL_LOG_FILE;
} else {
    $results['Log File']['Status'] = 'Not found (will be created on first email)';
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>SMTP Configuration Test Results</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; }
        h1 { color: #333; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .test-section h3 { margin-top: 0; color: #667eea; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        .info { background: #d1ecf1; border-color: #bee5eb; }
        code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f5f5f5; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✉️ GoDaddy SMTP Configuration Test</h1>
        
        <?php foreach ($results as $section => $data): ?>
            <div class="test-section info">
                <h3><?php echo htmlspecialchars($section); ?></h3>
                <?php if (is_array($data)): ?>
                    <table>
                        <?php foreach ($data as $key => $value): ?>
                            <tr>
                                <th><?php echo htmlspecialchars($key); ?></th>
                                <td><?php echo htmlspecialchars($value); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p><?php echo htmlspecialchars($data); ?></p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <div class="test-section" style="background: #fff3cd; border-color: #ffc107; margin-top: 30px;">
            <h3>📋 Next Steps</h3>
            <ol>
                <li><strong>Verify Socket Connection:</strong> If the socket connection test shows SUCCESS, your firewall allows SMTP.</li>
                <li><strong>Check Email File:</strong> The test email has been saved to <code><?php echo MAIL_STORAGE_DIR; ?></code></li>
                <li><strong>Monitor Delivery:</strong> Check your inbox and spam folder for the test email.</li>
                <li><strong>Verify in Admin Dashboard:</strong> Go to <code>emails-dashboard.php</code> to see stored emails.</li>
                <li><strong>Test Real Enrollments:</strong> Create a test enrollment to verify the complete flow works.</li>
            </ol>
        </div>
    </div>
</body>
</html>
