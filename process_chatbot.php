<?php
// Load configuration
require_once(__DIR__ . '/config.php');

ob_clean();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS, GET');
header('Access-Control-Allow-Headers: Content-Type, Accept');

// Handle OPTIONS request (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    die(json_encode(['success' => true]));
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'No data']));
}

$fullName = trim($input['fullName'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$course = trim($input['course'] ?? '');

if (!$fullName || !$email || !$phone) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Missing fields']));
}

// Save enrollment data to file
$company = trim($input['company'] ?? '');
$message_text = trim($input['message'] ?? '');

$enrollmentRecord = [
    'timestamp' => date('Y-m-d H:i:s'),
    'fullName' => $fullName,
    'email' => $email,
    'phone' => $phone,
    'course' => $course,
    'company' => $company,
    'message' => $message_text
];

// Save to JSON file
$enrollmentsFile = 'enrollments.json';
$enrollments = [];

if (file_exists($enrollmentsFile)) {
    $content = file_get_contents($enrollmentsFile);
    if (!empty($content)) {
        $enrollments = json_decode($content, true) ?? [];
    }
}

$enrollments[] = $enrollmentRecord;
file_put_contents($enrollmentsFile, json_encode($enrollments, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

// Also try to send email if mail server is configured
$to = 'info@adeptskil.com';
$subject = "Enrollment: $course - $fullName";
$emailBody = "New Enrollment Received!\n\n" .
    "Name: $fullName\n" .
    "Email: $email\n" .
    "Phone: $phone\n" .
    "Course: $course\n" .
    "Company: " . ($company ?: 'Not provided') . "\n" .
    "Message: " . ($message_text ?: 'No message') . "\n\n" .
    "Enrolled at: " . date('Y-m-d H:i:s');

$emailSent = sendEmail(ADMIN_EMAIL, $subject, $emailBody, ADMIN_EMAIL);

// Send confirmation email to user
sendEmail($email, "Enrollment Confirmation - $course", 
    "Thank you for enrolling in $course!\n\n" .
    "We have received your enrollment request. Our team will contact you soon.\n\n" .
    "Best regards,\nAdeptskil Team",
    ADMIN_EMAIL,
    $email);

http_response_code(200);
echo json_encode([
    'success' => true, 
    'message' => 'Enrollment successful! Check your email for confirmation.',
    'enrollment_id' => count($enrollments),
    'email_sent' => $emailSent
]);

