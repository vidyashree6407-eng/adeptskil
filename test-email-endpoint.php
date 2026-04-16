<?php
/**
 * Quick test to verify send_confirmation_email.php is accessible
 * Visit: http://localhost:8000/test-email-endpoint.php?email=your@email.com
 */

header('Content-Type: application/json');

// Test 1: Check if this file loads
$response = ['test' => 'success'];

// Test 2: Check if send_confirmation_email exists
if (file_exists('send_confirmation_email.php')) {
    $response['send_confirmation_email_exists'] = true;
} else {
    $response['send_confirmation_email_exists'] = false;
}

// Test 3: Try a GET request test
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['email'])) {
    // Simulate what success.html does
    $testData = [
        'fullName' => 'Test User',
        'email' => $_GET['email'],
        'phone' => '1234567890',
        'course' => 'Test Course',
        'company' => 'Test Company',
        'city' => 'Test City',
        'price' => 99.99,
        'invoice' => 'TEST-' . time(),
        'type' => 'enrollment_confirmation'
    ];
    
    $response['test_data'] = $testData;
    $response['test_endpoint'] = 'Ready to receive POST data';
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>
