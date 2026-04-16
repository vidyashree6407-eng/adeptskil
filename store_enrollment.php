<?php
/**
 * Store enrollment and save emails as JSON files
 * This runs on the server BEFORE redirect to payment provider
 * Uses file-based storage (no database needed)
 */

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['action']) || $input['action'] !== 'store_enrollment') {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Invalid request']));
}

$fullName = trim($input['fullName'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$course = trim($input['course'] ?? '');
$price = floatval($input['price'] ?? 0);
$invoice = trim($input['invoice'] ?? '');
$company = trim($input['company'] ?? '');
$city = trim($input['city'] ?? '');

// Validate
if (!$fullName || !$email || !$course || !$invoice) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Missing required fields']));
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Invalid email']));
}

<?php
/**
 * Store enrollment and save emails as JSON files
 * This runs on the server BEFORE redirect to payment provider
 * Uses file-based storage (no database needed)
 */

// Log all requests
$logDir = __DIR__ . '/logs';
@mkdir($logDir, 0755, true);

$requestLog = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'none',
    'body_size' => strlen(file_get_contents('php://input'))
];

// Read input fresh
$input = json_decode(file_get_contents('php://input'), true);

$requestLog['input_data'] = $input;
file_put_contents($logDir . '/request_' . time() . '.json', json_encode($requestLog, JSON_PRETTY_PRINT));

if (!$input || !isset($input['action']) || $input['action'] !== 'store_enrollment') {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Invalid request', 'input_received' => $input]));
}

$fullName = trim($input['fullName'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$course = trim($input['course'] ?? '');
$price = floatval($input['price'] ?? 0);
$invoice = trim($input['invoice'] ?? '');
$company = trim($input['company'] ?? '');
$city = trim($input['city'] ?? '');

// Validate
if (!$fullName || !$email || !$course || !$invoice) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Missing required fields', 'received' => compact('fullName', 'email', 'course', 'invoice')]));
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Invalid email: ' . $email]));
}

try {
    // Create emails directory
    @mkdir('emails', 0755, true);
    
    // Email 1: Customer
    $customerSubject = "Enrollment Confirmation - $course";
    $customerBody = buildCustomerEmail($fullName, $course, $invoice, $price, $email);
    
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
    file_put_contents($customerFile, json_encode($customerEmail, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    // Email 2: Admin
    $adminSubject = "New Enrollment: $course - $fullName";
    $adminBody = buildAdminEmail($fullName, $email, $phone, $course, $price, $invoice, $company, $city);
    
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
    
    // Save admin email
    $adminFile = 'emails/email_' . $adminEmail['id'] . '.json';
    file_put_contents($adminFile, json_encode($adminEmail, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    // Also save to a log file for easy viewing
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'invoice_id' => $invoice,
        'customer_name' => $fullName,
        'customer_email' => $email,
        'course' => $course,
        'price' => $price,
        'emails_created' => 2
    ];
    
    $logFile = 'emails/enrollment_log.json';
    $logData = [];
    if (file_exists($logFile)) {
        $logData = json_decode(file_get_contents($logFile), true) ?? [];
    }
    $logData[] = $logEntry;
    file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Enrollment stored successfully',
        'invoice' => $invoice,
        'emails_stored' => 2,
        'files' => [$customerFile, $adminFile],
        'view_url' => '/emails-dashboard.php'
    ]);
    exit;
    
} catch (Exception $e) {
    error_log("Store enrollment error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

function buildCustomerEmail($fullName, $course, $invoice, $price, $email) {
    $date = date('F d, Y H:i A');
    
    return <<<HTML
Hello $fullName,

Thank you for enrolling with Adeptskil! Your payment has been received and your enrollment is now active.

========================================
ENROLLMENT CONFIRMATION
========================================

Enrollment ID: $invoice
Course: $course
Amount Paid: \$$price
Date: $date
Email: $email

Your enrollment is now active. You have full access to course materials and resources.

If you have any questions, please contact us at info@adeptskil.com

Best regards,
Adeptskil Team

© 2024 Adeptskil. All rights reserved.
HTML;
}

function buildAdminEmail($fullName, $email, $phone, $course, $price, $invoice, $company, $city) {
    $date = date('Y-m-d H:i:s');
    
    return <<<TEXT
NEW ENROLLMENT RECEIVED

Date: $date
Invoice ID: $invoice

CUSTOMER DETAILS:
Name: $fullName
Email: $email
Phone: $phone
Company: $company
City: $city

ENROLLMENT DETAILS:
Course: $course
Amount Paid: \$$price

---
This is an automated notification.
TEXT;
}
?>

    
    // Email 1: Customer
    $customerSubject = "Enrollment Confirmation - $course";
    $customerBody = buildCustomerEmail($fullName, $course, $invoice, $price, $email);
    
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
    file_put_contents($customerFile, json_encode($customerEmail, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    // Email 2: Admin
    $adminSubject = "New Enrollment: $course - $fullName";
    $adminBody = buildAdminEmail($fullName, $email, $phone, $course, $price, $invoice, $company, $city);
    
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
    
    // Save admin email
    $adminFile = 'emails/email_' . $adminEmail['id'] . '.json';
    file_put_contents($adminFile, json_encode($adminEmail, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    // Also save to a log file for easy viewing
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'invoice_id' => $invoice,
        'customer_name' => $fullName,
        'customer_email' => $email,
        'course' => $course,
        'price' => $price,
        'emails_created' => 2
    ];
    
    $logFile = 'emails/enrollment_log.json';
    $logData = [];
    if (file_exists($logFile)) {
        $logData = json_decode(file_get_contents($logFile), true) ?? [];
    }
    $logData[] = $logEntry;
    file_put_contents($logFile, json_encode($logData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Enrollment stored successfully',
        'invoice' => $invoice,
        'emails_stored' => 2,
        'view_url' => '/emails-dashboard.php'
    ]);
    exit;
    
} catch (Exception $e) {
    error_log("Store enrollment error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    exit;
}

function buildCustomerEmail($fullName, $course, $invoice, $price, $email) {
    $date = date('F d, Y H:i A');
    
    return <<<HTML
Hello $fullName,

Thank you for enrolling with Adeptskil! Your payment has been received and your enrollment is now active.

========================================
ENROLLMENT CONFIRMATION
========================================

Enrollment ID: $invoice
Course: $course
Amount Paid: \$$price
Date: $date
Email: $email

Your enrollment is now active. You have full access to course materials and resources.

If you have any questions, please contact us at info@adeptskil.com

Best regards,
Adeptskil Team

© 2024 Adeptskil. All rights reserved.
HTML;
}

function buildAdminEmail($fullName, $email, $phone, $course, $price, $invoice, $company, $city) {
    $date = date('Y-m-d H:i:s');
    
    return <<<TEXT
NEW ENROLLMENT RECEIVED

Date: $date
Invoice ID: $invoice

CUSTOMER DETAILS:
Name: $fullName
Email: $email
Phone: $phone
Company: $company
City: $city

ENROLLMENT DETAILS:
Course: $course
Amount Paid: \$$price

---
This is an automated notification.
TEXT;
}
?>

