<?php
/**
 * Test exact PayPal return flow
 */

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/db_config.php');
require_once(__DIR__ . '/process_enrollment.php');

echo "=== Testing Exact PayPal Return Flow ===\n\n";

// Simulate what paypal-return.html sends to process_enrollment.php
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
    'payment_id' => 'PAYPAL-TEST-123',
    'comments' => 'Test enrollment'
];

echo "Simulating form data:\n";
echo json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

try {
    // Extract and validate required fields (same as process_enrollment.php)
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
    $payment_status = trim($payload['payment_status'] ?? '');
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
    echo "✓ Saving to database...\n";

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

    echo "✓ Enrolled in database (Invoice: $invoice)\n\n";
    
    echo "✓ Looking for sendConfirmationEmail function...\n";
    
    // Check if the functions exist
    if (function_exists('sendConfirmationEmail')) {
        echo "✓ Found sendConfirmationEmail function\n";
        echo "✓ Calling sendConfirmationEmail...\n";
        sendConfirmationEmail($fullName, $email, $course, $amount, $invoice);
        echo "✓ sendConfirmationEmail completed\n";
    } else {
        echo "✗ sendConfirmationEmail function NOT FOUND\n";
        echo "Available functions: " . implode(', ', get_defined_functions()['user']) . "\n";
    }
    
    echo "\n✓ EMAIL SENDING FLOW COMPLETED\n";
    echo "Check mail_log.txt for confirmation\n";

} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>
