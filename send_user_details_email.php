<?php
/**
 * Send enrollment/test details email to user
 * Called from view-enrollments.php
 */

// Load configuration
require_once(__DIR__ . '/config.php');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'error' => 'Method not allowed']));
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'No data provided']));
}

$type = $input['type'] ?? ''; // 'enrollment' or 'test'
$email = trim($input['email'] ?? '');
$data = $input['data'] ?? [];

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Invalid email address']));
}

if (empty($type) || empty($data)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Missing required fields']));
}

try {
    // Build email subject and body based on type
    if ($type === 'enrollment') {
        $subject = "Your Enrollment Details - Adeptskil";
        $body = buildEnrollmentDetailsEmail($data);
    } elseif ($type === 'test') {
        $subject = "Your Test Completion Details - Adeptskil";
        $body = buildTestDetailsEmail($data);
    } else {
        throw new Exception('Invalid type');
    }

    // Send email using the sendEmail() function from config.php
    $mailSent = sendEmail($email, $subject, $body, ADMIN_EMAIL, ADMIN_EMAIL);

    if ($mailSent) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Email sent successfully to ' . $email
        ]);
    } else {
        throw new Exception('Email sending failed');
    }

} catch (Exception $e) {
    error_log("Email send error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to send email: ' . $e->getMessage()
    ]);
}

exit;

/**
 * Build enrollment details email HTML
 */
function buildEnrollmentDetailsEmail($data) {
    $date = $data['timestamp'] ?? 'N/A';
    $fullName = htmlspecialchars($data['fullName'] ?? 'N/A');
    $email = htmlspecialchars($data['email'] ?? 'N/A');
    $phone = htmlspecialchars($data['phone'] ?? 'N/A');
    $company = htmlspecialchars($data['company'] ?? 'N/A');
    $course = htmlspecialchars($data['course'] ?? 'N/A');
    $message = htmlspecialchars($data['message'] ?? 'N/A');

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 25px 20px; background: white; }
        .info-section { background: #f5f9ff; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #667eea; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; }
        .info-label { font-weight: 600; color: #555; min-width: 120px; }
        .info-value { color: #333; word-break: break-word; }
        .footer { background: #f9f9f9; padding: 15px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #e0e0e0; }
        .timestamp { color: #999; font-size: 11px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📋 Your Enrollment Details</h1>
        </div>
        
        <div class="content">
            <p>Dear <strong>$fullName</strong>,</p>
            
            <p>Below are your enrollment details as recorded in our system:</p>
            
            <div class="info-section">
                <h3 style="margin-top: 0; color: #667eea;">Enrollment Information</h3>
                <div class="info-row">
                    <span class="info-label">Date & Time:</span>
                    <span class="info-value">$date</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Course:</span>
                    <span class="info-value">$course</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Full Name:</span>
                    <span class="info-value">$fullName</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">$email</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">$phone</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Company:</span>
                    <span class="info-value">$company</span>
                </div>
            </div>

            <div class="info-section">
                <h3 style="margin-top: 0; color: #667eea;">Message</h3>
                <p>$message</p>
            </div>
            
            <p>If you need to update any of these details or have questions, please don't hesitate to contact us at <strong>info@adeptskil.com</strong></p>
            
            <p>Thank you for choosing Adeptskil!</p>
            
            <p>Best regards,<br><strong>The Adeptskil Team</strong></p>
        </div>
        
        <div class="footer">
            <p>&copy; 2024 Adeptskil. All rights reserved.</p>
            <p class="timestamp">This email was automatically generated on $date</p>
        </div>
    </div>
</body>
</html>
HTML;
}

/**
 * Build test details email HTML
 */
function buildTestDetailsEmail($data) {
    $date = $data['completed_at'] ?? 'N/A';
    $fullName = htmlspecialchars($data['full_name'] ?? 'N/A');
    $email = htmlspecialchars($data['email'] ?? 'N/A');
    $phone = htmlspecialchars($data['phone'] ?? 'N/A');
    $invoiceId = htmlspecialchars($data['invoice_id'] ?? 'N/A');
    $course = htmlspecialchars($data['course'] ?? 'N/A');
    $testType = htmlspecialchars($data['test_type'] ?? 'N/A');
    $score = $data['score'] ?? 0;
    $duration = $data['duration'] ?? 0;
    $status = strtoupper($data['status'] ?? 'N/A');
    $statusColor = $score >= 70 ? '#10b981' : '#f59e0b';
    $statusBg = $score >= 70 ? '#d1fae5' : '#fef3c7';

    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px 20px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 25px 20px; background: white; }
        .info-section { background: #f5f9ff; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #667eea; }
        .info-row { display: flex; justify-content: space-between; padding: 8px 0; }
        .info-label { font-weight: 600; color: #555; min-width: 120px; }
        .info-value { color: #333; word-break: break-word; }
        .score-badge { display: inline-block; padding: 8px 16px; border-radius: 20px; font-weight: bold; background: $statusBg; color: $statusColor; }
        .footer { background: #f9f9f9; padding: 15px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #e0e0e0; }
        .timestamp { color: #999; font-size: 11px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✓ Your Test Completion Details</h1>
        </div>
        
        <div class="content">
            <p>Dear <strong>$fullName</strong>,</p>
            
            <p>Below are your test completion details:</p>
            
            <div class="info-section">
                <h3 style="margin-top: 0; color: #667eea;">Test Results</h3>
                <div class="info-row">
                    <span class="info-label">Test Type:</span>
                    <span class="info-value">$testType</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Course:</span>
                    <span class="info-value">$course</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Score:</span>
                    <span class="info-value"><span class="score-badge">$score%</span></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value"><strong style="color: $statusColor;">$status</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Duration:</span>
                    <span class="info-value">${duration} minutes</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Completed:</span>
                    <span class="info-value">$date</span>
                </div>
            </div>

            <div class="info-section">
                <h3 style="margin-top: 0; color: #667eea;">Your Details</h3>
                <div class="info-row">
                    <span class="info-label">Full Name:</span>
                    <span class="info-value">$fullName</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value">$email</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Phone:</span>
                    <span class="info-value">$phone</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Invoice ID:</span>
                    <span class="info-value">$invoiceId</span>
                </div>
            </div>
            
            <p>If you have any questions about your test results, please contact us at <strong>info@adeptskil.com</strong></p>
            
            <p>Thank you for using Adeptskil!</p>
            
            <p>Best regards,<br><strong>The Adeptskil Team</strong></p>
        </div>
        
        <div class="footer">
            <p>&copy; 2024 Adeptskil. All rights reserved.</p>
            <p class="timestamp">This email was automatically generated</p>
        </div>
    </div>
</body>
</html>
HTML;
}
?>
