<?php
/**
 * Send Confirmation Email
 * Handles sending confirmation emails for enrollment
 */

// Clear output buffer before setting headers
@ob_clean();

// Set headers FIRST - JSON response
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Initialize response
$response = ['success' => false, 'message' => 'Error processing request'];

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);

    // Validate input
    if (!$input || !isset($input['email']) || !isset($input['fullName'])) {
        throw new Exception('Missing required fields: email and fullName');
    }

    // Extract and sanitize data
    $to = filter_var($input['email'], FILTER_SANITIZE_EMAIL);
    $fullName = htmlspecialchars($input['fullName']);
    $course = htmlspecialchars($input['course'] ?? 'Professional Training Course');
    $phone = htmlspecialchars($input['phone'] ?? '');
    $company = htmlspecialchars($input['company'] ?? '');
    $city = htmlspecialchars($input['city'] ?? '');
    $price = floatval($input['price'] ?? 0);
    $invoice = isset($input['invoice']) ? htmlspecialchars($input['invoice']) : ('ENR-' . time());

    // Validate email
    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email address provided');
    }

    // Create emails directory if it doesn't exist
    if (!is_dir('emails')) {
        @mkdir('emails', 0755, true);
    }

    // Email configuration
    $siteEmail = 'info@adeptskil.com';
    $subject = 'Your Enrollment Confirmation - Adeptskil';

    // Build HTML email content
    $currentDate = date('F d, Y');
    $emailBody = buildConfirmationEmail($fullName, $course, $invoice, $price, $phone, $city, $company, $to);

    // Email headers
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . $siteEmail . "\r\n";
    $headers .= "Reply-To: " . $siteEmail . "\r\n";

    // Try to send email
    $emailSent = @mail($to, $subject, $emailBody, $headers);

    // Save enrollment document as backup
    $backupPath = 'emails/' . date('YmdHis') . '_' . sanitize_filename($fullName) . '.html';
    @file_put_contents($backupPath, $emailBody);

    // Log the email
    $logEntry = "[" . date('Y-m-d H:i:s') . "] To: $to | Course: $course | Invoice: $invoice | Status: " . ($emailSent ? 'SENT' : 'FAILED') . "\n";
    @file_put_contents('emails/email_log.txt', $logEntry, FILE_APPEND);

    // Also send copy to admin
    $adminBody = "NEW ENROLLMENT\n\n";
    $adminBody .= "Name: $fullName\n";
    $adminBody .= "Email: $to\n";
    $adminBody .= "Phone: $phone\n";
    $adminBody .= "City: $city\n";
    $adminBody .= "Company: $company\n";
    $adminBody .= "Course: $course\n";
    $adminBody .= "Amount: \$$price\n";
    $adminBody .= "Invoice: $invoice\n";
    $adminBody .= "Date: " . date('Y-m-d H:i:s') . "\n";

    $adminHeaders = "From: noreply@adeptskil.com\r\n";
    @mail($siteEmail, "New Enrollment: $course", $adminBody, $adminHeaders);

    // Success response
    $response = [
        'success' => true,
        'message' => $emailSent ? 'Confirmation email sent to your inbox' : 'Enrollment confirmed (email pending)',
        'email' => $to,
        'invoice' => $invoice,
        'course' => $course,
        'emailSent' => $emailSent
    ];

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ];
}

// Always output JSON response (never exit before this)
http_response_code(200);
echo json_encode($response);
exit;

/**
 * Sanitize filename
 */
function sanitize_filename($filename) {
    return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
}

/**
 * Build confirmation email HTML
 */
function buildConfirmationEmail($fullName, $course, $enrollmentId, $price, $phone, $city, $company, $to) {
    $currentDate = date('F d, Y');
    $to = htmlspecialchars($to);

    return <<<EOT
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #f9f9f9; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; }
        .content { padding: 30px; background: white; }
        .success-msg { background: #d1fae5; border-left: 4px solid #10b981; padding: 15px; margin: 20px 0; color: #065f46; }
        .section { margin-bottom: 25px; }
        .section h2 { color: #2d3748; font-size: 16px; margin-bottom: 12px; border-bottom: 2px solid #667eea; padding-bottom: 8px; }
        .row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .row:last-child { border-bottom: none; }
        .label { font-weight: 600; color: #64748b; }
        .value { color: #2d3748; }
        .price { color: #10b981; font-weight: 700; font-size: 18px; }
        .footer { background: #f0f4ff; padding: 20px; text-align: center; font-size: 12px; color: #64748b; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Enrollment Confirmed!</h1>
            <p style="margin: 10px 0 0 0;">Welcome to Adeptskil Professional Training</p>
        </div>

        <div class="content">
            <p>Dear <strong>$fullName</strong>,</p>
            
            <p>Thank you for your enrollment with Adeptskil! We're excited to have you join our professional training program. Your payment has been received and processed successfully.</p>

            <div class="success-msg">
                <strong>✓ Your enrollment is active!</strong><br>
                You now have full access to your course materials and resources.
            </div>

            <div class="section">
                <h2>📋 Your Enrollment Details</h2>
                <div class="row">
                    <span class="label">Enrollment ID:</span>
                    <span class="value" style="font-weight: bold;">$enrollmentId</span>
                </div>
                <div class="row">
                    <span class="label">Course Name:</span>
                    <span class="value">$course</span>
                </div>
                <div class="row">
                    <span class="label">Course Fee:</span>
                    <span class="value price">\$$price</span>
                </div>
                <div class="row">
                    <span class="label">Enrollment Date:</span>
                    <span class="value">$currentDate</span>
                </div>
            </div>

            <div class="section">
                <h2>👤 Personal Information on Record</h2>
                <div class="row">
                    <span class="label">Full Name:</span>
                    <span class="value">$fullName</span>
                </div>
                <div class="row">
                    <span class="label">Email Address:</span>
                    <span class="value">$to</span>
                </div>
                <div class="row">
                    <span class="label">Phone Number:</span>
                    <span class="value">$phone</span>
                </div>
                <div class="row">
                    <span class="label">City:</span>
                    <span class="value">$city</span>
                </div>
                <div class="row">
                    <span class="label">Organization:</span>
                    <span class="value">$company</span>
                </div>
            </div>

            <div class="section">
                <h2>📚 Getting Started</h2>
                <ul style="margin: 10px 0; padding-left: 20px; color: #64748b;">
                    <li>Your course materials are now available on our learning portal</li>
                    <li>You will receive separate login credentials via a follow-up email</li>
                    <li>Course access will remain available for 12 months from enrollment</li>
                    <li>For technical support, contact us at info@adeptskil.com</li>
                </ul>
            </div>

            <div class="section">
                <h2>📞 Support & Questions</h2>
                <p>If you have any questions about your enrollment or need assistance accessing course materials, please don't hesitate to contact our support team:</p>
                <ul style="margin: 10px 0; padding-left: 20px; color: #64748b;">
                    <li><strong>Email:</strong> info@adeptskil.com</li>
                    <li><strong>Phone:</strong> Available during business hours</li>
                    <li><strong>Enrollment ID:</strong> $enrollmentId (Please include in all correspondence)</li>
                </ul>
            </div>

            <p style="color: #64748b; margin-top: 20px;">We're committed to your professional development and success. Welcome to the Adeptskil community!</p>

            <p style="color: #64748b;">Best regards,<br><strong>The Adeptskil Training Team</strong></p>
        </div>

        <div class="footer">
            <p>&copy; 2024-2025 Adeptskil Professional Training. All rights reserved.<br>
            This is an automated confirmation email. Please do not reply to this message.<br><br>
            <em>Enrollment ID: $enrollmentId | Date: $currentDate</em></p>
        </div>
    </div>
</body>
</html>
EOT;
}
?>
