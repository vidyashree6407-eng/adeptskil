<?php
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

$to = 'info@adeptskil.com';
$subject = "Enrollment: $course";
$message = "Name: $fullName\nEmail: $email\nPhone: $phone\nCourse: $course";
$headers = "Content-Type: text/plain\r\nFrom: noreply@adeptskil.com\r\n";

@mail($to, $subject, $message, $headers);
@mail($email, "Enrollment Confirmation", "Thank you for enrolling in $course", $headers);

http_response_code(200);
echo json_encode(['success' => true, 'message' => 'Enrollment successful']);

