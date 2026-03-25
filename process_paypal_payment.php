<?php
/**
 * PayPal Payment Processing
 * Handles PayPal payments for course enrollment
 */

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get enrollment ID from URL
$enrollmentId = isset($_GET['enrollmentId']) ? sanitize($_GET['enrollmentId']) : '';

if (!$enrollmentId) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Invalid enrollment ID']));
}

// PayPal Configuration (SANDBOX - for testing)
define('PAYPAL_SANDBOX_URL', 'https://www.sandbox.paypal.com/cgi-bin/webscr');
define('PAYPAL_LIVE_URL', 'https://www.paypal.com/cgi-bin/webscr');

// Using SANDBOX for testing
$paypal_url = PAYPAL_SANDBOX_URL;

// Your PayPal Business Email (SANDBOX TEST ACCOUNT)
$paypal_business_email = 'sb-besoe49191096@business.example.com';

// Get payment data from session
$payment_data = array(
    'method' => 'paypal',
    'amount' => 149.00,
    'currency' => 'USD',
    'enrollment' => array(
        'course' => 'Professional Training Course',
        'fullName' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+1-555-1234',
        'company' => 'Test Company',
        'city' => 'New York',
        'pincode' => '10001',
        'address' => '123 Main St',
        'timestamp' => date('Y-m-d H:i:s')
    )
);

// Log payment initiation
error_log("PayPal payment initiated - Enrollment ID: $enrollmentId");
error_log("Payment Amount: " . $payment_data['amount'] . " " . $payment_data['currency']);
error_log("Student: " . $payment_data['enrollment']['fullName']);

// Generate invoice number
$invoice_number = 'ENR-' . $enrollmentId . '-' . date('YmdHis');

// Prepare PayPal redirect parameters
$paypal_params = array(
    'cmd' => '_xclick',
    'business' => $paypal_business_email,
    'item_name' => 'Adeptskil Course Enrollment - ' . $payment_data['enrollment']['course'],
    'item_number' => $enrollmentId,
    'amount' => number_format($payment_data['amount'], 2),
    'currency_code' => $payment_data['currency'],
    'invoice' => $invoice_number,
    'custom' => json_encode($payment_data['enrollment']),
    'return' => 'https://' . $_SERVER['HTTP_HOST'] . '/success.html?invoice=' . urlencode($invoice_number),
    'cancel_return' => 'https://' . $_SERVER['HTTP_HOST'] . '/cancel.html?invoice=' . urlencode($invoice_number),
    'notify_url' => 'https://' . $_SERVER['HTTP_HOST'] . '/ipn_handler.php',
    'rm' => '2',
    'no_shipping' => '1',
    'no_note' => '1',
    'charset' => 'utf-8',
    'email' => $payment_data['enrollment']['email'],
    'first_name' => explode(' ', $payment_data['enrollment']['fullName'])[0],
    'last_name' => end(explode(' ', $payment_data['enrollment']['fullName'])),
    'lc' => 'US'
);

// Build redirect URL
$redirect_url = $paypal_url . '?' . http_build_query($paypal_params);

// Log the redirect
error_log("Redirecting to PayPal: $redirect_url");

// Store payment info for verification later
$payment_log = array(
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => 'paypal',
    'enrollment_id' => $enrollmentId,
    'invoice' => $invoice_number,
    'amount' => $payment_data['amount'],
    'student_email' => $payment_data['enrollment']['email'],
    'status' => 'initiated'
);

// Save to file (could be database in production)
$log_file = __DIR__ . '/payment_log.txt';
file_put_contents($log_file, json_encode($payment_log) . PHP_EOL, FILE_APPEND);

// Redirect to PayPal
header('Location: ' . $redirect_url);
exit;

// Utility function to sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
}
?>
