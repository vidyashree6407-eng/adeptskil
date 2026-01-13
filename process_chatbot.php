<?php
/**
 * Enrollment Handler
 * Processes course enrollments and sends confirmation emails
 */

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/chatbot_errors.log');

// Get the incoming enrollment data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
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
$adminSent = sendEmail($ADMIN_EMAIL, $adminSubject, $adminBody);
$userSent = sendEmail($email, $userSubject, $userBody);

// Return success response
if ($adminSent) {
    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Enrollment processed successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error processing enrollment']);
}
exit;

// HELPER FUNCTIONS
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function sendEmail($to, $subject, $body) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@adeptskil.com" . "\r\n";
    
    return mail($to, $subject, $body, $headers);
}

function logEnrollment($name, $email, $course, $timestamp) {
    $logFile = __DIR__ . '/enrollments.log';
    $logEntry = "[$timestamp] Name: $name | Email: $email | Course: $course\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

?>
