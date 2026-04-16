<?php
/**
 * PayPal IPN (Instant Payment Notification) Handler
 * Listens for PayPal webhooks to verify payments
 */

// CRITICAL: Log immediately to detect if this file is even being called
$ipn_log_file = dirname(__FILE__) . '/ipn_handler.log';
$debug_msg = "[" . date('Y-m-d H:i:s') . "] IPN Handler started - Method: " . $_SERVER['REQUEST_METHOD'] . " - Data size: " . strlen(file_get_contents('php://input')) . " bytes";
file_put_contents($ipn_log_file, $debug_msg . "\n", FILE_APPEND);

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/db_config.php');

// Log IPN messages
function logIPN($message) {
    global $ipn_log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($ipn_log_file, "[$timestamp] $message\n", FILE_APPEND);
}

// Get the IPN data from PayPal
$ipn_data = $_POST;

// Log the received IPN
logIPN('IPN Received: ' . json_encode($ipn_data));

// Verify IPN with PayPal
$verify_url = strpos($_POST['test_ipn'] ?? '', '1') !== false 
    ? 'https://www.sandbox.paypal.com/cgi-bin/webscr'
    : 'https://www.paypal.com/cgi-bin/webscr';

$verify_data = array('cmd' => '_notify-validate');
$verify_data = array_merge($verify_data, $ipn_data);

$verify_string = http_build_query($verify_data);

$ch = curl_init($verify_url);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $verify_string);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$res = curl_exec($ch);

if (curl_errno($ch) != 0) {
    logIPN('cURL Error: ' . curl_error($ch));
    curl_close($ch);
    http_response_code(400);
    exit;
}

curl_close($ch);

// Check verification response
if ($res != 'VERIFIED') {
    logIPN('IPN Verification Failed: ' . $res);
    http_response_code(400);
    exit;
}

logIPN('IPN Verified Successfully');

// Process verified IPN data
$txn_id = isset($ipn_data['txn_id']) ? trim($ipn_data['txn_id']) : '';
$invoice_num = isset($ipn_data['invoice']) ? trim($ipn_data['invoice']) : '';
$payment_status = isset($ipn_data['payment_status']) ? trim($ipn_data['payment_status']) : '';
$receiver_email = isset($ipn_data['receiver_email']) ? trim($ipn_data['receiver_email']) : '';
$business = isset($ipn_data['business']) ? trim($ipn_data['business']) : '';
$custom = isset($ipn_data['custom']) ? trim($ipn_data['custom']) : '';
$payer_email = isset($ipn_data['payer_email']) ? trim($ipn_data['payer_email']) : '';

logIPN("Transaction: $txn_id, Invoice: $invoice_num, Status: $payment_status, Payer: $payer_email");

// Verify receiver email matches our PayPal account
$expected_receiver = ADMIN_EMAIL;
if ($receiver_email != $expected_receiver && $business != $expected_receiver) {
    logIPN("Receiver mismatch: $receiver_email vs $expected_receiver");
    http_response_code(400);
    exit;
}

// Handle payment completed
if ($payment_status == 'Completed') {
    logIPN("Payment completed for transaction: $txn_id");
    
    // Update enrollment record with IPN data in database
    try {
        $db = getDB();
        logIPN("Database connection successful");
        
        // PRIMARY LOOKUP: Find enrollment by invoice number (this is set by us in the PayPal form)
        logIPN("Attempting to find enrollment by invoice_num: '$invoice_num'");
        $stmt = $db->prepare("
            SELECT * FROM enrollments 
            WHERE invoice_id = ?
            LIMIT 1
        ");
        $stmt->execute([$invoice_num]);
        $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // FALLBACK: If not found, try by transaction ID (in case payment_id was set earlier)
        if (!$enrollment && !empty($txn_id)) {
            logIPN("Not found by invoice, trying transaction ID: '$txn_id'");
            $stmt = $db->prepare("
                SELECT * FROM enrollments 
                WHERE payment_id = ?
                LIMIT 1
            ");
            $stmt->execute([$txn_id]);
            $enrollment = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        if ($enrollment) {
            logIPN("✓ Enrollment FOUND: ID=" . $enrollment['id'] . ", Email=" . $enrollment['email'] . ", Course=" . $enrollment['course']);
            
            // Update payment status to completed
            $updateStmt = $db->prepare("
                UPDATE enrollments 
                SET payment_status = 'completed', payment_id = ?
                WHERE id = ?
            ");
            $updateStmt->execute([$txn_id, $enrollment['id']]);
            
            logIPN("✓ Updated enrollment payment_status to 'completed' and payment_id to: $txn_id");
            
            // Send confirmation emails
            logIPN("📧 Sending confirmation emails to: " . $enrollment['email']);
            sendPaymentConfirmationEmails(
                $enrollment['full_name'],
                $enrollment['email'],
                $enrollment['course'],
                $enrollment['amount'],
                $enrollment['invoice_id']
            );
            logIPN("✓ Confirmation email sending completed");
        } else {
            logIPN("✗ ENROLLMENT NOT FOUND - Could not find record for invoice='$invoice_num' or txn_id='$txn_id'");
            
            // Debug: List all recent enrollments to help diagnose
            $debug_stmt = $db->query("SELECT invoice_id, email, payment_id FROM enrollments ORDER BY created_at DESC LIMIT 5");
            $recent = $debug_stmt->fetchAll(PDO::FETCH_ASSOC);
            logIPN("Debug - Recent 5 enrollments: " . json_encode($recent));
        }
    } catch (Exception $e) {
        logIPN("✗ Database error: " . $e->getMessage());
    }
    
    // Also update enrollments.json for backwards compatibility
    $enrollmentsFile = dirname(__FILE__) . '/enrollments.json';
    if (file_exists($enrollmentsFile)) {
        $enrollments = json_decode(file_get_contents($enrollmentsFile), true) ?: array();
        
        // Find and update the enrollment matching this transaction
        foreach ($enrollments as &$enrollment) {
            if ($enrollment['paypal_order_id'] === $txn_id) {
                $enrollment['ipn_verified'] = true;
                $enrollment['payment_verified'] = true;
                $enrollment['verification_time'] = date('Y-m-d H:i:s');
                logIPN("Updated enrollment in JSON: " . $enrollment['id']);
                break;
            }
        }
        
        file_put_contents($enrollmentsFile, json_encode($enrollments, JSON_PRETTY_PRINT));
    }
}

// Handle payment refunded
elseif ($payment_status == 'Refunded') {
    logIPN("Refund notification received for transaction: $txn_id");
    
    // Log refund notification
    $refund_log = dirname(__FILE__) . '/refund_notifications.txt';
    file_put_contents($refund_log, date('Y-m-d H:i:s') . " - Refund: $txn_id\n", FILE_APPEND);
}

// Handle payment denied
elseif ($payment_status == 'Denied') {
    logIPN("Payment denied for transaction: $txn_id");
}

// Always return 200 to PayPal
http_response_code(200);
echo 'OK';

/**
 * Send confirmation emails after successful payment verification
 */
function sendPaymentConfirmationEmails($fullName, $email, $course, $amount, $invoiceId) {
    try {
        // Build professional HTML email for customer
        $customerSubject = "✓ Enrollment Confirmation - $course";
        $customerBody = buildPaymentConfirmationEmail($fullName, $course, $amount, $invoiceId);
        
        // Send to customer
        sendEmail($email, $customerSubject, $customerBody, ADMIN_EMAIL, ADMIN_EMAIL);
        
        // Build admin notification email
        $adminSubject = "NEW ENROLLMENT: $course - $fullName";
        $adminBody = buildAdminPaymentNotificationEmail($fullName, $email, $course, $amount, $invoiceId);
        
        // Send to admin
        sendEmail(ADMIN_EMAIL, $adminSubject, $adminBody, ADMIN_EMAIL, $email);
        
        global $ipn_log_file;
        logIPN("Confirmation emails sent for: $email");
        
    } catch (Exception $e) {
        global $ipn_log_file;
        logIPN("Email sending failed: " . $e->getMessage());
    }
}

/**
 * Build professional HTML customer confirmation email
 */
function buildPaymentConfirmationEmail($fullName, $course, $amount, $invoiceId) {
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
function buildAdminPaymentNotificationEmail($fullName, $email, $course, $amount, $invoiceId) {
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
            <p>A new student has completed enrollment and payment verified.</p>
            
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
            
            <p><strong style="color: #10b981;">✓ Payment Verified by PayPal</strong></p>
            
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
