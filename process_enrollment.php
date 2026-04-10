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

// Load configuration files
require_once(__DIR__ . '/config.php');
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
    error_log("ENROLLMENT API: Calling sendConfirmationEmail for $email");
    sendConfirmationEmail($fullName, $email, $course, $amount, $invoice);
    error_log("ENROLLMENT API: sendConfirmationEmail completed for $email");

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
        $log = "[" . date('Y-m-d H:i:s') . "] ENROLLMENT EMAIL START: $email | Invoice: $invoiceId\n";
        @file_put_contents(MAIL_LOG_FILE, $log, FILE_APPEND);
        
        // Build professional HTML email for customer
        $customerSubject = "✓ Enrollment Confirmation - $course";
        $customerBody = buildCustomerConfirmationEmail($fullName, $course, $amount, $invoiceId);
        
        $log = "[" . date('Y-m-d H:i:s') . "] ENROLLMENT EMAIL BUILT: Subject=$customerSubject, Body length=" . strlen($customerBody) . "\n";
        @file_put_contents(MAIL_LOG_FILE, $log, FILE_APPEND);
        
        // Send to customer
        $result1 = sendEmail($email, $customerSubject, $customerBody, ADMIN_EMAIL, ADMIN_EMAIL);
        $log = "[" . date('Y-m-d H:i:s') . "] ENROLLMENT EMAIL TO CUSTOMER: " . ($result1 ? 'SENT' : 'FAILED') . "\n";
        @file_put_contents(MAIL_LOG_FILE, $log, FILE_APPEND);
        
        // Build admin notification email
        $adminSubject = "NEW ENROLLMENT: $course - $fullName";
        $adminBody = buildAdminNotificationEmail($fullName, $email, $course, $amount, $invoiceId);
        
        // Send to admin
        $result2 = sendEmail(ADMIN_EMAIL, $adminSubject, $adminBody, ADMIN_EMAIL, $email);
        $log = "[" . date('Y-m-d H:i:s') . "] ENROLLMENT EMAIL TO ADMIN: " . ($result2 ? 'SENT' : 'FAILED') . "\n";
        @file_put_contents(MAIL_LOG_FILE, $log, FILE_APPEND);
        
        error_log("CONFIRM_EMAIL: Completed for $email");
        
    } catch (Exception $e) {
        $log = "[" . date('Y-m-d H:i:s') . "] ENROLLMENT EMAIL ERROR: " . $e->getMessage() . "\n";
        @file_put_contents(MAIL_LOG_FILE, $log, FILE_APPEND);
        error_log('CONFIRM_EMAIL ERROR: ' . $e->getMessage());
        // Don't fail the enrollment if email fails
    }
}

/**
 * Build professional HTML customer confirmation email
 */
function buildCustomerConfirmationEmail($fullName, $course, $amount, $invoiceId) {
    $date = date('F d, Y H:i A');
    
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 20px auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 25px 20px; background: white; }
        .info-box { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .row:last-child { border-bottom: none; }
        .label { font-weight: bold; color: #555; }
        .value { color: #333; }
        .footer { background: #f9f9f9; padding: 15px; text-align: center; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Enrollment Confirmed</h1>
        </div>
        
        <div class="content">
            <p>Dear <strong>$fullName</strong>,</p>
            
            <p>Thank you for enrolling with <strong>Adeptskil</strong>! Your payment has been received and your enrollment is now active.</p>
            
            <div class="info-box">
                <div class="row">
                    <span class="label">Enrollment ID:</span>
                    <span class="value"><strong>$invoiceId</strong></span>
                </div>
                <div class="row">
                    <span class="label">Course:</span>
                    <span class="value">$course</span>
                </div>
                <div class="row">
                    <span class="label">Amount Paid:</span>
                    <span class="value" style="color: #10b981; font-weight: bold;">\$$amount</span>
                </div>
                <div class="row">
                    <span class="label">Date:</span>
                    <span class="value">$date</span>
                </div>
            </div>
            
            <p><strong>What's Next?</strong></p>
            <ul>
                <li>Your enrollment is now active</li>
                <li>You have full access to course materials and resources</li>
                <li>Our team will contact you within 24 hours with access details</li>
            </ul>
            
            <p>If you have any questions, please contact us at <strong>info@adeptskil.com</strong></p>
            
            <p>Best regards,<br><strong>Adeptskil Team</strong></p>
        </div>
        
        <div class="footer">
            <p>&copy; 2024 Adeptskil. All rights reserved.</p>
            <p>This is an automated confirmation email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Build admin notification email
 */
function buildAdminNotificationEmail($fullName, $email, $course, $amount, $invoiceId) {
    $date = date('Y-m-d H:i:s');
    
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 20px auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 25px 20px; background: white; }
        .info-box { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 15px 0; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .row:last-child { border-bottom: none; }
        .label { font-weight: bold; color: #555; }
        .value { color: #333; }
        .footer { background: #f9f9f9; padding: 15px; text-align: center; font-size: 12px; color: #777; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 New Enrollment Received</h1>
        </div>
        
        <div class="content">
            <p>A new student has completed enrollment.</p>
            
            <div class="info-box">
                <h3 style="margin-top: 0; color: #667eea;">Student Details</h3>
                <div class="row">
                    <span class="label">Name:</span>
                    <span class="value">$fullName</span>
                </div>
                <div class="row">
                    <span class="label">Email:</span>
                    <span class="value"><a href="mailto:$email" style="color: #667eea; text-decoration: none;">$email</a></span>
                </div>
            </div>
            
            <div class="info-box">
                <h3 style="margin-top: 0; color: #667eea;">Enrollment Details</h3>
                <div class="row">
                    <span class="label">Course:</span>
                    <span class="value">$course</span>
                </div>
                <div class="row">
                    <span class="label">Amount Paid:</span>
                    <span class="value" style="color: #10b981; font-weight: bold;">\$$amount</span>
                </div>
                <div class="row">
                    <span class="label">Invoice ID:</span>
                    <span class="value"><strong>$invoiceId</strong></span>
                </div>
                <div class="row">
                    <span class="label">Date:</span>
                    <span class="value">$date</span>
                </div>
            </div>
            
            <p>Action Required: Please follow up with the student and provide course access.</p>
            
            <p>—<br><strong>Adeptskil Admin System</strong></p>
        </div>
        
        <div class="footer">
            <p>&copy; 2024 Adeptskil. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
HTML;
}

?>
