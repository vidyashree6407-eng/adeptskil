#!/usr/bin/env php
<?php
/**
 * CLI Test that simulates PayPal return flow
 * Run from command line: php test-simulate-paypal.php
 */

echo "=== Simulating PayPal Return Flow ===\n\n";

// Simulate the POST request that paypal-return.html would send
$payload = [
    'fullName' => 'PayPal Test User',
    'email' => 'vidyashree6407@gmail.com',
    'phone' => '9876543210',
    'company' => 'PayPal Test Co',
    'city' => 'Bangalore',
    'pincode' => '560001',
    'address' => 'Test Address',
    'course' => 'Advanced Leadership',
    'amount' => 7999,
    'invoice' => 'PAYPAL-TEST-' . time(),
    'payment_method' => 'paypal',
    'payment_status' => 'completed',
    'payment_id' => 'PAYPAL-' . time(),
    'comments' => 'CLI test enrollment'
];

// Simulate the API call locally (change to 'http://localhost:8000/' if testing on web)
echo "Test Payload:\n";
echo json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

// Include the config WITHOUT the process_enrollment logic
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/db_config.php');

echo "Running enrollment process...\n";
echo "---\n";

try {
    // Extract and validate required fields
    $fullName = trim($payload['fullName'] ?? '');
    $email = trim($payload['email'] ?? '');
    $phone = trim($payload['phone'] ?? '');
    $company = trim($payload['company'] ?? '');
    $city = trim($payload['city'] ?? '');
    $pincode = trim($payload['pincode'] ?? '');
    $address = trim($payload['address'] ?? '');
    $course = trim($payload['course'] ?? '');
    $amount = floatval($payload['amount'] ?? 0);
    $invoice = trim($payload['invoice'] ?? '');
    $payment_method = trim($payload['payment_method'] ?? '');
    $payment_status = trim($payload['payment_status'] ?? 'pending');
    $payment_id = trim($payload['payment_id'] ?? '');
    $comments = trim($payload['comments'] ?? '');

    // Validation
    if (!$fullName || !$email || !$phone || !$city || !$course || !$invoice) {
        throw new Exception('Missing required fields');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address');
    }

    echo "✓ Validation passed\n";

    // Save to database
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO enrollments (
            invoice_id, full_name, email, phone, company, city, pincode, address,
            course, amount, payment_method, payment_status, payment_id, comments
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $invoice,
        $fullName,
        $email,
        $phone,
        $company,
        $city,
        $pincode,
        $address,
        $course,
        $amount,
        $payment_method,
        $payment_status,
        $payment_id,
        $comments
    ]);

    echo "✓ Saved to database (Invoice: $invoice)\n";

    // Now manually reproduce what sendConfirmationEmail does
    echo "✓ Sending confirmation emails...\n\n";
    
    // Simulate sendConfirmationEmail call
    $log = "[" . date('Y-m-d H:i:s') . "] CLI TEST: ENROLLMENT EMAIL START: $email | Invoice: $invoice\n";
    @file_put_contents(MAIL_LOG_FILE, $log, FILE_APPEND);
    
    // Send customer email
    $customerSubject = "✓ Enrollment Confirmation - $course";
    $result1 = sendEmail($email, $customerSubject, "<p>Test email to customer</p>", ADMIN_EMAIL, ADMIN_EMAIL);
    $log = "[" . date('Y-m-d H:i:s') . "] CLI TEST: ENROLLMENT EMAIL TO CUSTOMER: " . ($result1 ? 'SENT' : 'FAILED') . "\n";
    @file_put_contents(MAIL_LOG_FILE, $log, FILE_APPEND);
    
    // Send admin email
    $adminSubject = "NEW ENROLLMENT: $course - $fullName";
    $result2 = sendEmail(ADMIN_EMAIL, $adminSubject, "<p>Test email to admin</p>", ADMIN_EMAIL, $email);
    $log = "[" . date('Y-m-d H:i:s') . "] CLI TEST: ENROLLMENT EMAIL TO ADMIN: " . ($result2 ? 'SENT' : 'FAILED') . "\n";
    @file_put_contents(MAIL_LOG_FILE, $log, FILE_APPEND);
    
    echo "✓ Emails sent\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "---\n\n";
echo "✓ Test Complete\n";
echo "\nCheck:\n";
echo "1. mail_log.txt for email logs\n";
echo "2. Your email inbox (vidyashree6407@gmail.com)\n";
echo "3. Gmail spam/junk folder\n";
?>
