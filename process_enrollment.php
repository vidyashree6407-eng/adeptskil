<?php
// Set proper headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS, GET');
header('Access-Control-Allow-Headers: Content-Type, Accept');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit(json_encode(['success' => true]));
}

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!$input) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'No data received']));
}

// Extract and validate fields
$fullName = isset($input['fullName']) ? trim($input['fullName']) : '';
$email = isset($input['email']) ? trim($input['email']) : '';
$phone = isset($input['phone']) ? trim($input['phone']) : '';
$course = isset($input['course']) ? trim($input['course']) : '';
$company = isset($input['company']) ? trim($input['company']) : '';
$message = isset($input['message']) ? trim($input['message']) : '';

// Validate required fields
if (empty($fullName) || empty($email) || empty($phone)) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Required fields missing: Name, Email, Phone']));
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit(json_encode(['success' => false, 'message' => 'Invalid email address']));
}

// Create enrollment record
$enrollment = array(
    'id' => uniqid('ENR-', true),
    'timestamp' => date('Y-m-d H:i:s'),
    'fullName' => $fullName,
    'email' => $email,
    'phone' => $phone,
    'course' => $course,
    'company' => $company,
    'message' => $message
);

// Save to JSON file
$enrollmentsFile = __DIR__ . '/enrollments.json';
$enrollments = array();

if (file_exists($enrollmentsFile)) {
    $content = file_get_contents($enrollmentsFile);
    if (!empty($content)) {
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $enrollments = $decoded;
        }
    }
}

$enrollments[] = $enrollment;

if (!file_put_contents($enrollmentsFile, json_encode($enrollments, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
    http_response_code(500);
    exit(json_encode(['success' => false, 'message' => 'Failed to save enrollment']));
}

// Try to send confirmation email to user
$userSubject = "Enrollment Confirmation - " . $course;
$userMessage = "Dear " . $fullName . ",\n\n";
$userMessage .= "Thank you for enrolling in: " . $course . "\n\n";
$userMessage .= "We have received your enrollment request and will contact you shortly.\n\n";
$userMessage .= "Best regards,\nAdeptskil Team\n";
$userMessage .= "Email: info@adeptskil.com\n";

$headers = "Content-Type: text/plain; charset=UTF-8\r\n";
$headers .= "From: noreply@adeptskil.com\r\n";

@mail($email, $userSubject, $userMessage, $headers);

// Try to send notification to admin
$adminSubject = "New Enrollment: " . $course . " - " . $fullName;
$adminMessage = "New enrollment received!\n\n";
$adminMessage .= "Name: " . $fullName . "\n";
$adminMessage .= "Email: " . $email . "\n";
$adminMessage .= "Phone: " . $phone . "\n";
$adminMessage .= "Company: " . ($company ?: 'Not provided') . "\n";
$adminMessage .= "Course: " . $course . "\n";
$adminMessage .= "Message: " . ($message ?: 'No message') . "\n";
$adminMessage .= "Date: " . date('Y-m-d H:i:s') . "\n";

@mail('info@adeptskil.com', $adminSubject, $adminMessage, $headers);

// Return success
http_response_code(200);
exit(json_encode([
    'success' => true,
    'message' => 'Enrollment successful! Check your email for confirmation.',
    'enrollment_id' => $enrollment['id'],
    'name' => $fullName
]));
?>
