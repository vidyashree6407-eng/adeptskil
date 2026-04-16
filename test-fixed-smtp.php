<?php
require_once(__DIR__ . '/config.php');

echo "=== Testing Fixed SMTP Configuration ===\n\n";

// Test email
$testTo = ADMIN_EMAIL;
$testSubject = "Fixed SMTP Test - " . date('Y-m-d H:i:s');
$testBody = "<html><body>";
$testBody .= "<h2>Fixed SMTP Test</h2>";
$testBody .= "<p>This email tests the improved SMTP connection handler.</p>";
$testBody .= "<p>If you see this, the SMTP fix is working!</p>";
$testBody .= "<p>Sent at: " . date('Y-m-d H:i:s') . "</p>";
$testBody .= "</body></html>";

echo "Sending test email to: $testTo\n";
echo "Subject: $testSubject\n\n";

// Send the email
$result = sendEmail($testTo, $testSubject, $testBody, SMTP_FROM_EMAIL);

if ($result) {
    echo "✓ Email queued successfully!\n";
} else {
    echo "✗ Email queueing failed\n";
}

echo "\nCheck the email in your inbox within 5-30 seconds.\n";
echo "Check /mail_log.txt for any errors.\n";
echo "Check /emails/ directory for the stored email JSON file.\n";

?>
