<?php
// ===== ENROLLMENT HANDLER =====
// Processes course enrollments and sends confirmation emails

// Set headers FIRST before any output
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Suppress all output except JSON
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/chatbot_errors.log');

// Helper function to send JSON response and exit
function sendJSON($data, $httpCode = 200) {
    ob_end_clean();
    http_response_code($httpCode);
    echo json_encode($data);
    exit;
}

// Handle OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    sendJSON(['status' => 'ok'], 200);
}

// Get raw input
$rawInput = file_get_contents('php://input');

// Log raw input for debugging
error_log("Raw input received: " . $rawInput);

// Decode JSON
$input = json_decode($rawInput, true);

// Check JSON decode errors
if (json_last_error() !== JSON_ERROR_NONE) {
    sendJSON([
        'success' => false,
        'error' => 'Invalid JSON input',
        'json_error' => json_last_error_msg()
    ], 400);
}

if (!$input) {
    sendJSON([
        'success' => false,
        'error' => 'No data received'
    ], 400);
}


// Configuration - UPDATE THESE WITH YOUR DETAILS
$ADMIN_EMAIL = 'info@adeptskil.com';      // WHERE YOU RECEIVE ENROLLMENT NOTIFICATIONS
$COMPANY_NAME = 'Adeptskil';
$COMPANY_PHONE = '+1-800-TRAINING';        // Your company contact number
$COMPANY_WEBSITE = 'https://adeptskil.com';

// Extract enrollment data
$course = sanitize($input['course'] ?? '');
$fullName = sanitize($input['fullName'] ?? '');
$email = sanitize($input['email'] ?? '');
$phone = sanitize($input['phone'] ?? '');
$company = sanitize($input['company'] ?? '');
$message = sanitize($input['message'] ?? '');
$timestamp = date('Y-m-d H:i:s');

// Validate required fields
if (empty($fullName) || empty($email) || empty($phone)) {
    sendJSON([
        'success' => false,
        'error' => 'Missing required fields: fullName, email, phone'
    ], 400);
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendJSON([
        'success' => false,
        'error' => 'Invalid email format'
    ], 400);
}

error_log("Enrollment request received - Course: $course, Email: $email");

// Log the enrollment
logEnrollment($fullName, $email, $course, $timestamp);

// Send email to ADMIN (you receive this)
$adminSubject = "New Course Enrollment: $course";
$adminBody = "
<html>
  <body style='font-family: Arial, sans-serif;'>
    <h2>New Course Enrollment Received</h2>
    <p><strong>Course:</strong> $course</p>
    <p><strong>Full Name:</strong> $fullName</p>
    <p><strong>Email:</strong> $email</p>
    <p><strong>Phone:</strong> $phone</p>
    <p><strong>Company:</strong> " . ($company ?: 'Not provided') . "</p>
    <p><strong>Message:</strong> " . ($message ?: 'No additional message') . "</p>
    <p><strong>Enrollment Date:</strong> $timestamp</p>
    <hr>
    <p><small>This is an automated message. Please do not reply to this email.</small></p>
  </body>
</html>";

// Send email to USER (confirmation)
$userSubject = "Enrollment Confirmation - $course";
$userBody = "
<html>
  <body style='font-family: Arial, sans-serif; color: #333;'>
    <h2 style='color: #667eea;'>Enrollment Confirmation</h2>
    <p>Hi <strong>$fullName</strong>,</p>
    <p>Thank you for enrolling in <strong>$course</strong>!</p>
    
    <div style='background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0;'>
      <h3>Your Enrollment Details:</h3>
      <p><strong>Course:</strong> $course</p>
      <p><strong>Email:</strong> $email</p>
      <p><strong>Enrollment Date:</strong> $timestamp</p>
    </div>
    
    <p>Our team will contact you shortly at <strong>$phone</strong> to discuss course details and schedule.</p>
    
    <p>If you have any questions, feel free to reach out:</p>
    <p>
      📧 Email: $ADMIN_EMAIL<br>
      📞 Phone: $COMPANY_PHONE<br>
      🌐 Website: $COMPANY_WEBSITE
    </p>
    
    <p>Best regards,<br><strong>$COMPANY_NAME Team</strong></p>
  </body>
</html>";

// Send emails
try {
    $adminSent = sendEmail($ADMIN_EMAIL, $adminSubject, $adminBody);
    $userSent = sendEmail($email, $userSubject, $userBody);
    
    error_log("Emails sent - Admin: " . ($adminSent ? 'yes' : 'no') . ", User: " . ($userSent ? 'yes' : 'no'));
    
    // Return success response
    sendJSON([
        'success' => true, 
        'message' => 'Enrollment processed successfully',
        'adminSent' => $adminSent,
        'userSent' => $userSent
    ]);
} catch (Exception $e) {
    error_log("Exception during email sending: " . $e->getMessage());
    sendJSON([
        'success' => false, 
        'message' => 'Error processing enrollment: ' . $e->getMessage()
    ], 500);
}

// HELPER FUNCTIONS
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function sendEmail($to, $subject, $body) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@adeptskil.com" . "\r\n";
    $headers .= "Reply-To: noreply@adeptskil.com" . "\r\n";
    
    $result = mail($to, $subject, $body, $headers);
    
    if (!$result) {
        error_log("Failed to send email to: $to, Subject: $subject");
    }
    
    return $result;
}

function logEnrollment($name, $email, $course, $timestamp) {
    $logFile = __DIR__ . '/enrollments.log';
    $logEntry = "[$timestamp] Name: $name | Email: $email | Course: $course | IP: " . $_SERVER['REMOTE_ADDR'] . "\n";
    @file_put_contents($logFile, $logEntry, FILE_APPEND);
}

?>

