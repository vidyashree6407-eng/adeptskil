<?php
/**
 * Enrollment Email Sender
 * Saves confirmation emails to database (local testing)
 * In production, change to use SendGrid, Mailgun, or your email service
 */

// Get input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'No data provided']));
}

// Extract data
$fullName = trim($input['fullName'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$course = trim($input['course'] ?? '');
$price = floatval($input['price'] ?? 0);
$invoice = trim($input['invoice'] ?? '');
$company = trim($input['company'] ?? '');
$city = trim($input['city'] ?? '');

// Validate
if (!$fullName || !$email || !$course) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Missing required fields']));
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'Invalid email']));
}

// Initialize database
@mkdir('emails', 0755, true);
$dbFile = __DIR__ . '/enrollments.db';
$db = new SQLite3($dbFile);

// Create emails table if doesn't exist
$db->exec("CREATE TABLE IF NOT EXISTS emails (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    invoice_id TEXT,
    recipient_email TEXT,
    recipient_name TEXT,
    recipient_type TEXT,
    subject TEXT,
    body TEXT,
    sent_status TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");


// PHPMailer for GoDaddy SMTP
require_once(__DIR__ . '/phpmailer_autoload.php');
$customerEmailSent = false;
$adminEmailSent = false;

try {
    // Email 1: Store CUSTOMER email
    $customerSubject = "Enrollment Confirmation - $course";
    $customerBody = buildCustomerEmail($fullName, $course, $invoice, $price, $email);
    $stmt = $db->prepare("INSERT INTO emails (invoice_id, recipient_email, recipient_name, recipient_type, subject, body, sent_status) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bindValue(1, $invoice, SQLITE3_TEXT);
    $stmt->bindValue(2, $email, SQLITE3_TEXT);
    $stmt->bindValue(3, $fullName, SQLITE3_TEXT);
    $stmt->bindValue(4, 'customer', SQLITE3_TEXT);
    $stmt->bindValue(5, $customerSubject, SQLITE3_TEXT);
    $stmt->bindValue(6, $customerBody, SQLITE3_TEXT);
    $stmt->bindValue(7, 'pending', SQLITE3_TEXT);
    $stmt->execute();

    // Send confirmation email to customer using GoDaddy SMTP
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email, $fullName);
        $mail->isHTML(true);
        $mail->Subject = $customerSubject;
        $mail->Body = $customerBody;
        $mail->AltBody = strip_tags($customerBody);
        $mail->send();
        $customerEmailSent = true;
        // Update sent_status in DB
        $db->exec("UPDATE emails SET sent_status='sent' WHERE recipient_email='" . SQLite3::escapeString($email) . "' AND invoice_id='" . SQLite3::escapeString($invoice) . "'");
    } catch (Exception $e) {
        error_log('PHPMailer error: ' . $mail->ErrorInfo);
    }
    
    // Email 2: Store ADMIN email
    $adminSubject = "New Enrollment: $course - $fullName";
    $adminBody = buildAdminEmail($fullName, $email, $phone, $course, $price, $invoice, $company, $city);
    $stmt = $db->prepare("INSERT INTO emails (invoice_id, recipient_email, recipient_name, recipient_type, subject, body, sent_status) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bindValue(1, $invoice, SQLITE3_TEXT);
    $stmt->bindValue(2, 'info@adeptskil.com', SQLITE3_TEXT);
    $stmt->bindValue(3, 'Admin', SQLITE3_TEXT);
    $stmt->bindValue(4, 'admin', SQLITE3_TEXT);
    $stmt->bindValue(5, $adminSubject, SQLITE3_TEXT);
    $stmt->bindValue(6, $adminBody, SQLITE3_TEXT);
    $stmt->bindValue(7, 'pending', SQLITE3_TEXT);
    $stmt->execute();

    // Send admin notification email using GoDaddy SMTP
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress('info@adeptskil.com', 'Admin');
        $mail->isHTML(false);
        $mail->Subject = $adminSubject;
        $mail->Body = $adminBody;
        $mail->send();
        $adminEmailSent = true;
        // Update sent_status in DB
        $db->exec("UPDATE emails SET sent_status='sent' WHERE recipient_email='info@adeptskil.com' AND invoice_id='" . SQLite3::escapeString($invoice) . "'");
    } catch (Exception $e) {
        error_log('PHPMailer admin email error: ' . $mail->ErrorInfo);
    }
    
} catch (Exception $e) {
    error_log("Email storage error: " . $e->getMessage());
}

$db->close();

// Response
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Emails stored successfully',
    'customerEmail' => $customerEmailSent,
    'adminEmail' => $adminEmailSent,
    'invoice' => $invoice,
    'note' => 'Emails stored in database. View in admin dashboard.'
]);
exit;

/**
 * Build customer email HTML
 */
function buildCustomerEmail($fullName, $course, $invoice, $price, $email) {
    $date = date('F d, Y H:i A');
    
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
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
            <p>Hello <strong>$fullName</strong>,</p>
            
            <p>Thank you for enrolling with <strong>Adeptskil</strong>! Your payment has been received and your enrollment is now active.</p>
            
            <div class="info-box">
                <div class="row">
                    <span class="label">Enrollment ID:</span>
                    <span class="value"><strong>$invoice</strong></span>
                </div>
                <div class="row">
                    <span class="label">Course:</span>
                    <span class="value">$course</span>
                </div>
                <div class="row">
                    <span class="label">Amount Paid:</span>
                    <span class="value" style="color: #10b981; font-weight: bold;">\$$price</span>
                </div>
                <div class="row">
                    <span class="label">Date:</span>
                    <span class="value">$date</span>
                </div>
            </div>
            
            <p>Your enrollment is now active. You have full access to course materials and resources.</p>
            
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
 * Build admin notification email (plain text)
 */
function buildAdminEmail($fullName, $email, $phone, $course, $price, $invoice, $company, $city) {
    $date = date('Y-m-d H:i:s');
    
    return <<<TEXT
NEW ENROLLMENT RECEIVED

Date: $date
Invoice ID: $invoice

CUSTOMER DETAILS:
Name: $fullName
Email: $email
Phone: $phone
Company: $company
City: $city

ENROLLMENT DETAILS:
Course: $course
Amount Paid: \$$price

---
This is an automated notification. Do not reply to this email.
TEXT;
}
?>
