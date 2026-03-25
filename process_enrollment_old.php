<?php
// Load configuration
require_once(__DIR__ . '/config.php');

// Start output buffering to prevent any accidental output
ob_start();

// Clear any previous output
ob_clean();

// Disable error reporting to console/output
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set JSON response header IMMEDIATELY
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Accept, Origin');

// Log request method for debugging
error_log('Request method: ' . $_SERVER['REQUEST_METHOD']);

// Handle CORS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'CORS preflight OK']);
    ob_end_flush();
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log('Invalid method: ' . $_SERVER['REQUEST_METHOD']);
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method ' . $_SERVER['REQUEST_METHOD'] . ' not allowed. Use POST.']);
    ob_end_flush();
    exit;
}

// Get JSON input
$json_input = file_get_contents('php://input');
$input = json_decode($json_input, true);

// Validate input
if (empty($input)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No data received']);
    ob_end_flush();
    exit;
}

// Extract fields
$fullName = isset($input['fullName']) ? trim($input['fullName']) : '';
$email = isset($input['email']) ? trim($input['email']) : '';
$phone = isset($input['phone']) ? trim($input['phone']) : '';
$course = isset($input['course']) ? trim($input['course']) : '';
$city = isset($input['city']) ? trim($input['city']) : '';
$company = isset($input['company']) ? trim($input['company']) : '';
$message_text = isset($input['message']) ? trim($input['message']) : '';
$paypal_order_id = isset($input['paypal_order_id']) ? trim($input['paypal_order_id']) : '';
$payment_status = isset($input['payment_status']) ? trim($input['payment_status']) : '';
$price = isset($input['price']) ? floatval($input['price']) : 0;

// Validate required fields
if (empty($fullName) || empty($email) || empty($phone) || empty($city)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    ob_end_flush();
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    ob_end_flush();
    exit;
}

// Create enrollment record
$enrollment_id = 'ENR-' . date('YmdHis') . '-' . random_int(1000, 9999);
$enrollment = array(
    'id' => $enrollment_id,
    'timestamp' => date('Y-m-d H:i:s'),
    'fullName' => $fullName,
    'email' => $email,
    'phone' => $phone,
    'city' => $city,
    'course' => $course,
    'company' => $company,
    'message' => $message_text,
    'paypal_order_id' => $paypal_order_id,
    'payment_status' => $payment_status,
    'price' => $price
);

// Get enrollments file path
$enrollmentsFile = dirname(__FILE__) . '/enrollments.json';
$enrollments = array();

// Read existing enrollments
if (file_exists($enrollmentsFile) && is_readable($enrollmentsFile)) {
    $content = file_get_contents($enrollmentsFile);
    if (!empty($content)) {
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $enrollments = $decoded;
        }
    }
}

// Add new enrollment
$enrollments[] = $enrollment;

// Save to file
$save_result = file_put_contents($enrollmentsFile, json_encode($enrollments, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

if ($save_result === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save enrollment data']);
    ob_end_flush();
    exit;
}

// Send emails (non-blocking, don't fail if email fails)

// ===== USER CONFIRMATION EMAIL =====
$userEmailSubject = "Course Enrollment Confirmation - " . $course;
$userEmailBody = "Dear " . $fullName . ",

Thank you for enrolling in our course and completing your payment!

==== ENROLLMENT DETAILS ====
Name: " . $fullName . "
Email: " . $email . "
Phone: " . $phone . "
City: " . $city . "
Course: " . $course . "
Course Fee: \$" . number_format($price, 2) . "
Company: " . ($company ?: 'N/A') . "
Payment Status: Completed
Order ID: " . $paypal_order_id . "
Enrollment ID: " . $enrollment_id . "

We have successfully received your enrollment for: " . $course . "

Your payment of \$" . number_format($price, 2) . " has been processed successfully.

Our team will review your application and contact you shortly at " . $phone . " with the next steps and course details.

If you have any questions, feel free to reach out to us.

Best regards,
Adeptskil Training Team
Email: " . ADMIN_EMAIL . "";

sendEmail($email, $userEmailSubject, $userEmailBody);

// ===== ADMIN NOTIFICATION EMAIL =====
$adminEmailSubject = "New Course Enrollment - " . $course . " [" . $fullName . "]";
$adminEmailBody = "New course enrollment received!

==== STUDENT DETAILS ====
Name: " . $fullName . "
Email: " . $email . "
Phone: " . $phone . "
City: " . $city . "
Company: " . ($company ?: 'N/A') . "

==== ENROLLMENT DETAILS ====
Course: " . $course . "
Enrollment ID: " . $enrollment_id . "
Enrollment Time: " . date('Y-m-d H:i:s') . "
Payment Status: " . $payment_status . "
Course Fee: \$" . number_format($price, 2) . "
PayPal Order ID: " . $paypal_order_id . "

==== ADDITIONAL MESSAGE ====
" . ($message_text ?: 'No additional message provided') . "

---
Please follow up with the student to confirm enrollment and provide course materials.
";

sendEmail(ADMIN_EMAIL, $adminEmailSubject, $adminEmailBody);

// Return success
http_response_code(200);
$response = [
    'success' => true,
    'message' => 'Enrollment successful! Check your email for confirmation.',
    'enrollment_id' => $enrollment_id,
    'name' => $fullName
];
echo json_encode($response);
ob_end_flush();
exit;
