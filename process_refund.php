<?php
/**
 * Process Refund Handler
 * Handles refund requests and stores refund data
 */

require_once(__DIR__ . '/admin_auth.php');
require_once(__DIR__ . '/config.php');

// Check if logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Check if POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON data
$json_input = file_get_contents('php://input');
$data = json_decode($json_input, true);

// Validate required fields
if (empty($data['enrollment_id']) || empty($data['amount']) || empty($data['reason'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

// Create refund record
$refund = array(
    'id' => 'REF-' . date('YmdHis') . '-' . random_int(1000, 9999),
    'enrollment_id' => $data['enrollment_id'],
    'student_name' => $data['student_name'],
    'student_email' => $data['student_email'],
    'course' => $data['course'],
    'amount' => floatval($data['amount']),
    'reason' => $data['reason'],
    'refund_date' => date('Y-m-d H:i:s'),
    'processed_by' => getLoggedInUser(),
    'status' => 'Completed'
);

// Get refunds file
$refundsFile = dirname(__FILE__) . '/refunds.json';
$refunds = array();

if (file_exists($refundsFile) && is_readable($refundsFile)) {
    $content = file_get_contents($refundsFile);
    if (!empty($content)) {
        $refunds = json_decode($content, true) ?: array();
    }
}

// Add new refund
$refunds[] = $refund;

// Save refunds
$save_result = file_put_contents($refundsFile, json_encode($refunds, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

if ($save_result === false) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save refund']);
    exit;
}

// Send refund notification email
$student_email = $data['student_email'];
$refund_subject = "Refund Processed - " . $data['course'];
$refund_body = "Dear " . $data['student_name'] . ",

Your refund has been processed successfully!

==== REFUND DETAILS ====
Course: " . $data['course'] . "
Refund Amount: $" . number_format($data['amount'], 2) . "
Refund ID: " . $refund['id'] . "
Refund Date: " . date('Y-m-d H:i:s') . "
Reason: " . $data['reason'] . "

The refund will appear in your original payment method within 3-5 business days.

If you have any questions, please contact us at " . ADMIN_EMAIL . "

Best regards,
Adeptskil Team";

sendEmail($student_email, $refund_subject, $refund_body);

// Send notification to admin
$admin_subject = "Refund Processed - " . $data['course'];
$admin_body = "A refund has been processed.

Student: " . $data['student_name'] . "
Email: " . $data['student_email'] . "
Course: " . $data['course'] . "
Amount: $" . number_format($data['amount'], 2) . "
Refund ID: " . $refund['id'] . "
Reason: " . $data['reason'] . "
Processed By: " . getLoggedInUser();

sendEmail(ADMIN_EMAIL, $admin_subject, $admin_body);

// Return success
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'Refund processed successfully',
    'refund_id' => $refund['id']
]);
?>
