<?php
/**
 * PayPal IPN (Instant Payment Notification) Handler
 * Listens for PayPal webhooks to verify payments
 */

require_once(__DIR__ . '/config.php');

// Log IPN messages
$ipn_log_file = dirname(__FILE__) . '/ipn_log.txt';

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
$payment_status = isset($ipn_data['payment_status']) ? trim($ipn_data['payment_status']) : '';
$receiver_email = isset($ipn_data['receiver_email']) ? trim($ipn_data['receiver_email']) : '';
$business = isset($ipn_data['business']) ? trim($ipn_data['business']) : '';
$custom = isset($ipn_data['custom']) ? trim($ipn_data['custom']) : '';

logIPN("Transaction: $txn_id, Status: $payment_status");

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
    
    // Update enrollment record with IPN data
    $enrollmentsFile = dirname(__FILE__) . '/enrollments.json';
    if (file_exists($enrollmentsFile)) {
        $enrollments = json_decode(file_get_contents($enrollmentsFile), true) ?: array();
        
        // Find and update the enrollment matching this transaction
        foreach ($enrollments as &$enrollment) {
            if ($enrollment['paypal_order_id'] === $txn_id) {
                $enrollment['ipn_verified'] = true;
                $enrollment['payment_verified'] = true;
                $enrollment['verification_time'] = date('Y-m-d H:i:s');
                logIPN("Updated enrollment: " . $enrollment['id']);
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
?>
