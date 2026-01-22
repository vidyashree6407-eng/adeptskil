<?php
/**
 * Contact Form Processor
 * Securely handles contact form submissions from contact.html
 * Sends email notifications without exposing user data
 */

// Load configuration
require_once(__DIR__ . '/config.php');

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', LOGS_DIR . '/contact_errors.log');

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
$inquiry_type = isset($_POST['inquiry_type']) ? trim(strip_tags($_POST['inquiry_type'])) : '';
$message = isset($_POST['message']) ? trim(strip_tags($_POST['message'])) : '';

// Validate required fields
$errors = [];

if (empty($name) || strlen($name) < 2) {
    $errors[] = 'Name is required (minimum 2 characters)';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}

if (empty($inquiry_type)) {
    $errors[] = 'Type of inquiry is required';
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
$recipient_email = ADMIN_EMAIL;
$site_name = SITE_NAME;
$timestamp = date('Y-m-d H:i:s');

// Build email content
$email_subject = "New Contact Form Submission - $inquiry_type from $name";
$email_body = "
Dear Adeptskil Team,

You have received a new contact form submission:

Name: $name
Email: $email
Phone: " . (!empty($phone) ? $phone : 'Not provided') . "
Inquiry Type: $inquiry_type
Timestamp: $timestamp

Message:
---
$message
---

Please reply to: $email

---
This is an automated message from the Adeptskil contact form.
";

// Send email using centralized function
$mail_sent = sendEmail($recipient_email, $email_subject, $email_body, ADMIN_EMAIL, $email);

// Log the submission to local file for admin review
logContactSubmission($name, $email, $phone, $inquiry_type, $message, $mail_sent);

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

// Function to log contact submissions
function logContactSubmission($name, $email, $phone, $inquiry_type, $message, $mail_sent) {
    $log_entry = date('Y-m-d H:i:s') . " | Name: $name | Email: $email | Type: $inquiry_type | Sent: " . ($mail_sent ? 'Yes' : 'No') . "\n";
    $log_file = LOGS_DIR . '/contact_submissions.log';
    @file_put_contents($log_file, $log_entry, FILE_APPEND);
}

exit;

/**
 * Log contact form submissions
 */
function logContactSubmission($name, $email, $phone, $inquiry_type, $message, $mail_sent) {
    $log_file = __DIR__ . '/contact_submissions.log';
    $status = $mail_sent ? 'SUCCESS' : 'FAILED';
    $log_entry = sprintf(
        "[%s] %s - Name: %s | Email: %s | Phone: %s | Inquiry Type: %s | Message Length: %d\n",
        date('Y-m-d H:i:s'),
        $status,
        $name,
        $email,
        $phone ?: 'N/A',
        $inquiry_type,
        strlen($message)
    );
    
    error_log($log_entry, 3, $log_file);
}
?>
