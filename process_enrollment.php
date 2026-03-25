<?php
/**
 * Process Enrollment API Endpoint
 * Saves customer enrollment data to SQLite database
 */

// Enable error logging
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Set JSON response header IMMEDIATELY
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Load database configuration
require_once(__DIR__ . '/db_config.php');

// Handle CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON body
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No data received']);
    exit;
}

try {
    // Extract and validate required fields
    $fullName = trim($input['fullName'] ?? '');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $company = trim($input['company'] ?? '');
    $city = trim($input['city'] ?? '');
    $pincode = trim($input['pincode'] ?? '');
    $address = trim($input['address'] ?? '');
    $course = trim($input['course'] ?? '');
    $amount = floatval($input['amount'] ?? 0);
    $invoice = trim($input['invoice'] ?? '');
    $payment_method = trim($input['payment_method'] ?? '');
    $payment_status = trim($input['payment_status'] ?? 'pending');
    $payment_id = trim($input['payment_id'] ?? '');
    $comments = trim($input['comments'] ?? '');

    // Validation
    if (!$fullName || !$email || !$phone || !$city || !$course || !$invoice) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid email address']);
        exit;
    }

    // Save to database
    $db = getDB();
    $stmt = $db->prepare("
        INSERT INTO enrollments (
            invoice_id, full_name, email, phone, company, city, pincode, address,
            course, amount, payment_method, payment_status, payment_id, comments
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $invoice,
        $fullName,
        $email,
        $phone,
        $company,
        $city,
        $pincode,
        $address,
        $course,
        $amount,
        $payment_method,
        $payment_status,
        $payment_id,
        $comments
    ]);

    // Log payment
    logPayment($invoice, $payment_method, $payment_status, [
        'amount' => $amount,
        'email' => $email
    ]);

    // Send confirmation email (non-blocking)
    sendConfirmationEmail($fullName, $email, $course, $amount, $invoice);

    // Return success with enrollment ID
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Enrollment saved successfully',
        'enrollment_id' => $invoice,
        'email_sent' => true
    ]);

} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error processing enrollment']);
}

/**
 * Send confirmation email to customer
 */
function sendConfirmationEmail($fullName, $email, $course, $amount, $invoiceId) {
    try {
        $subject = "Course Enrollment Confirmation - $course";
        $message = "Dear $fullName,\n\n";
        $message .= "Thank you for enrolling in $course!\n\n";
        $message .= "Enrollment Details:\n";
        $message .= "- Invoice: $invoiceId\n";
        $message .= "- Course: $course\n";
        $message .= "- Amount: \$" . number_format($amount, 2) . "\n";
        $message .= "- Status: Payment Completed\n\n";
        $message .= "Our team will contact you shortly.\n\n";
        $message .= "Best regards,\nAdeptskil Team";

        // Set From header
        $from = "noreply@adeptskil.com";
        $headers = "From: " . $from . "\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        // Attempt to send (non-blocking if fails)
        @mail($email, $subject, $message, $headers);
        
        // Also log to file
        $log_file = dirname(__FILE__) . '/email_log.txt';
        $log_entry = date('Y-m-d H:i:s') . " | To: $email | Subject: $subject\n";
        @file_put_contents($log_file, $log_entry, FILE_APPEND);

    } catch (Exception $e) {
        error_log('Email sending failed: ' . $e->getMessage());
        // Don't fail the enrollment if email fails
    }
}

?>
