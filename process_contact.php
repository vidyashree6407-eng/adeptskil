<?php
/**
 * Contact Form Processor
 * Securely handles contact form submissions from contact.html
 * Sends email notifications without exposing user data
 */

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/contact_errors.log');

// Set JSON response header
header('Content-Type: application/json');

// CSRF Protection - check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Sanitize and validate input
$name = isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : '';
$email = isset($_POST['email']) ? trim(strtolower($_POST['email'])) : '';
$phone = isset($_POST['phone']) ? trim(preg_replace('/[^\d]/', '', $_POST['phone'])) : '';
$message = isset($_POST['message']) ? trim(strip_tags($_POST['message'])) : '';

// Validate required fields
$errors = [];

if (empty($name) || strlen($name) < 2) {
    $errors[] = 'Name is required (minimum 2 characters)';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}

if (empty($message) || strlen($message) < 10) {
    $errors[] = 'Message is required (minimum 10 characters)';
}

// If there are validation errors
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

// Configuration
$recipient_email = 'info@adeptskil.com';
$site_name = 'Adeptskil';
$timestamp = date('Y-m-d H:i:s');

// Build email content
$email_subject = "New Contact Form Submission from $name";
$email_body = "
Dear Adeptskil Team,

You have received a new contact form submission:

Name: $name
Email: $email
Phone: " . (!empty($phone) ? $phone : 'Not provided') . "
Timestamp: $timestamp

Message:
---
$message
---

Please reply to: $email

---
This is an automated message from the Adeptskil contact form.
";

// Prepare email headers
$headers = array(
    'From' => 'noreply@adeptskil.com',
    'Reply-To' => $email,
    'X-Mailer' => 'Adeptskil Contact Form',
    'X-Priority' => '3',
    'Return-Path' => 'noreply@adeptskil.com'
);

$headers_str = '';
foreach ($headers as $key => $value) {
    $headers_str .= $key . ": " . $value . "\r\n";
}

// Send email
$mail_sent = mail($recipient_email, $email_subject, $email_body, $headers_str);

// Log the submission
logContactSubmission($name, $email, $phone, $message, $mail_sent);

// Return appropriate response
if ($mail_sent) {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Thank you! Your message has been received. We will contact you soon.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Sorry, there was an error sending your message. Please try again later.'
    ]);
}

exit;

/**
 * Log contact form submissions
 */
function logContactSubmission($name, $email, $phone, $message, $mail_sent) {
    $log_file = __DIR__ . '/contact_submissions.log';
    $status = $mail_sent ? 'SUCCESS' : 'FAILED';
    $log_entry = sprintf(
        "[%s] %s - Name: %s | Email: %s | Phone: %s | Message Length: %d\n",
        date('Y-m-d H:i:s'),
        $status,
        $name,
        $email,
        $phone ?: 'N/A',
        strlen($message)
    );
    
    error_log($log_entry, 3, $log_file);
}
?>
