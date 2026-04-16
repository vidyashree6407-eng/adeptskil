<?php
/**
 * Simulate enrollment and test email sending
 */

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/db_config.php');

echo "=== Enrollment Email Test ===\n\n";

// Test data matching your enrollment
$testData = [
    'fullName' => 'Test Student',
    'email' => 'vidyashree6407@gmail.com',
    'phone' => '9876543210',
    'company' => 'Test Company',
    'city' => 'Bangalore',
    'pincode' => '560001',
    'address' => 'Test Address',
    'course' => 'Leadership Training',
    'amount' => 5000,
    'invoice' => 'TEST-' . date('YmdHis'),
    'payment_method' => 'credit_card',
    'payment_status' => 'completed',
    'payment_id' => 'test-payment-id'
];

echo "Test Data:\n";
echo "  Name: {$testData['fullName']}\n";
echo "  Email: {$testData['email']}\n";
echo "  Course: {$testData['course']}\n";
echo "  Invoice: {$testData['invoice']}\n\n";

// Include the email building functions from process_enrollment.php
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
                    <span class="value" style="color: #10b981; font-weight: bold;">₹$amount</span>
                </div>
            </div>
            
            <p>Your course materials will be available soon. You will receive further instructions via email.</p>
            
            <p>If you have any questions, feel free to contact us.</p>
            
            <p>Best regards,<br>
            <strong>Adeptskil Team</strong><br>
            info@adeptskil.com
            </p>
        </div>
        
        <div class="footer">
            <p>This is an automated email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>
HTML;
}

try {
    echo "Preparing to send confirmation email...\n";
    
    $customerSubject = "✓ Enrollment Confirmation - {$testData['course']}";
    $customerBody = buildCustomerConfirmationEmail(
        $testData['fullName'],
        $testData['course'],
        $testData['amount'],
        $testData['invoice']
    );
    
    echo "Subject: $customerSubject\n";
    echo "To: {$testData['email']}\n";
    echo "Body length: " . strlen($customerBody) . " bytes\n\n";
    
    echo "Calling sendEmail()...\n";
    $result = sendEmail(
        $testData['email'],
        $customerSubject,
        $customerBody,
        ADMIN_EMAIL,
        ADMIN_EMAIL
    );
    
    if ($result) {
        echo "✓ Email sent successfully!\n";
        echo "\nCheck your email inbox for the confirmation.\n";
    } else {
        echo "✗ Email sending returned false.\n";
        echo "Check mail_log.txt and error logs.\n";
    }
    
} catch (Exception $e) {
    echo "✗ Exception: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>
