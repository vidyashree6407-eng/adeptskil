<?php
error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/chatbot_errors.log');

ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

function respond($success, $message, $code = 200) {
    ob_end_clean();
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    respond(true, 'ok', 200);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    respond(false, 'No data received', 400);
}

$fullName = trim($input['fullName'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$course = trim($input['course'] ?? '');
$company = trim($input['company'] ?? '');

if (!$fullName || !$email || !$phone) {
    respond(false, 'Missing required fields', 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respond(false, 'Invalid email', 400);
}

$adminEmail = 'info@adeptskil.com';
$subject = "New Enrollment: $course";
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=UTF-8\r\n";
$headers .= "From: noreply@adeptskil.com\r\n";

$body = "<h2>New Enrollment</h2>";
$body .= "<p><b>Course:</b> $course</p>";
$body .= "<p><b>Name:</b> $fullName</p>";
$body .= "<p><b>Email:</b> $email</p>";
$body .= "<p><b>Phone:</b> $phone</p>";
if ($company) {
    $body .= "<p><b>Company:</b> $company</p>";
}

@mail($adminEmail, $subject, $body, $headers);
@mail($email, "Enrollment Confirmation", "<p>Thank you for enrolling in <b>$course</b>!</p>", $headers);

$logFile = __DIR__ . '/enrollments.log';
$logLine = "[" . date('Y-m-d H:i:s') . "] $fullName | $email | $course | " . $_SERVER['REMOTE_ADDR'] . "\n";
@file_put_contents($logFile, $logLine, FILE_APPEND);

respond(true, 'Enrollment successful', 200);

