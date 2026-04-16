<?php
// Direct API test
header('Content-Type: application/json');

$data = [
    'action' => 'store_enrollment',
    'fullName' => 'Direct Test User',
    'email' => 'test@adeptskil.com',
    'phone' => '9999999999',
    'course' => 'Leadership Training',
    'price' => '500',
    'invoice' => 'DIRECT-TEST-' . time(),
    'company' => 'Test',
    'city' => 'Test City'
];

echo "Testing store_enrollment.php directly...\n\n";
echo "Input data:\n";
echo json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

// Simulate the POST request
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['CONTENT_TYPE'] = 'application/json';

// Replace php://input with our test data
$input = $data;

// Now run store_enrollment logic
$logDir = __DIR__ . '/logs';
@mkdir($logDir, 0755, true);

$fullName = trim($input['fullName'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$course = trim($input['course'] ?? '');
$price = floatval($input['price'] ?? 0);
$invoice = trim($input['invoice'] ?? '');
$company = trim($input['company'] ?? '');
$city = trim($input['city'] ?? '');

echo "Parsed values:\n";
echo "  fullName: $fullName\n";
echo "  email: $email\n";
echo "  course: $course\n";
echo "  invoice: $invoice\n\n";

// Validate
if (!$fullName || !$email || !$course || !$invoice) {
    echo "❌ Validation failed\n";
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "❌ Invalid email\n";
    exit;
}

echo "✓ Validation passed\n\n";

try {
    // Create emails directory
    @mkdir('emails', 0755, true);
    echo "✓ Emails directory created\n";
    
    // Email 1: Customer
    $customerSubject = "Enrollment Confirmation - $course";
    $customerBody = "Test body";
    
    $customerEmail = [
        'id' => uniqid(),
        'invoice_id' => $invoice,
        'recipient_email' => $email,
        'recipient_name' => $fullName,
        'recipient_type' => 'customer',
        'subject' => $customerSubject,
        'body' => $customerBody,
        'sent_status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Save customer email
    $customerFile = 'emails/email_' . $customerEmail['id'] . '.json';
    file_put_contents($customerFile, json_encode($customerEmail, JSON_PRETTY_PRINT));
    echo "✓ Customer email saved: $customerFile\n";
    
    // Email 2: Admin
    $adminSubject = "New Enrollment: $course - $fullName";
    $adminBody = "Admin test body";
    
    $adminEmail = [
        'id' => uniqid(),
        'invoice_id' => $invoice,
        'recipient_email' => 'info@adeptskil.com',
        'recipient_name' => 'Admin',
        'recipient_type' => 'admin',
        'subject' => $adminSubject,
        'body' => $adminBody,
        'sent_status' => 'pending',
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    $adminFile = 'emails/email_' . $adminEmail['id'] . '.json';
    file_put_contents($adminFile, json_encode($adminEmail, JSON_PRETTY_PRINT));
    echo "✓ Admin email saved: $adminFile\n\n";
    
    echo "✅ SUCCESS! Emails were stored.\n";
    echo "Now run: php debug_emails.php\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
