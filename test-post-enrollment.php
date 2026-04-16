<?php
/**
 * Direct test of process_enrollment.php
 * Sends POST request like the form would
 */

$url = 'http://localhost:8000/process_enrollment.php';

$payload = [
    'fullName' => 'Test User',
    'email' => 'vidyashree6407@gmail.com',
    'phone' => '9876543210',
    'company' => 'Test Corp',
    'city' => 'Bangalore',
    'pincode' => '560001',
    'address' => '123 Test St',
    'course' => 'Leadership Training',
    'amount' => 5000,
    'invoice' => 'TEST-' . time(),
    'payment_method' => 'paypal',
    'payment_status' => 'completed',
    'payment_id' => 'PAYPAL-TEST-' . time(),
    'comments' => 'Test enrollment'
];

echo "=== Testing process_enrollment.php ===\n\n";
echo "URL: $url\n";
echo "Payload:\n";
echo json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

echo "Sending POST request...\n";
echo "---\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo $response;
echo "---\n";
echo "HTTP Code: $httpCode\n";
if ($error) {
    echo "cURL Error: $error\n";
}

echo "\n✓ Test complete\n";
echo "\nCheck:\n";
echo "1. Your email inbox/spam for confirmation\n";
echo "2. mail_log.txt for email logs\n";
echo "3. enrollments.db for the enrollment record\n";
?>
