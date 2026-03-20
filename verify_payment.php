<?php
/**
 * PayPal PDT (Payment Data Transfer) Verification Handler
 * Verifies payment data returned from PayPal
 */

require_once(__DIR__ . '/config.php');

// PDT Token from PayPal (must be set in config)
define('PDT_TOKEN', 'YOUR_PDT_TOKEN_HERE');

/**
 * Verify PDT transaction
 */
function verifyPDTTransaction($tx) {
    $pdt_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr'; // For production: https://www.paypal.com/cgi-bin/webscr
    
    $pdt_data = array(
        'cmd' => '_notify-synch',
        'tx' => $tx,
        'at' => PDT_TOKEN
    );
    
    $ch = curl_init($pdt_url);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($pdt_data));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    
    $res = curl_exec($ch);
    curl_close($ch);
    
    // Parse response
    $lines = explode("\n", $res);
    $key_map = array();
    
    for ($i = 1; $i < count($lines); $i++) {
        $line = $lines[$i];
        if (empty($line)) continue;
        
        $line = explode("=", $line);
        $key = urldecode($line[0]);
        $val = urldecode($line[1]);
        
        $key_map[$key] = $val;
    }
    
    // Check if verification was successful
    if (isset($lines[0]) && $lines[0] == 'SUCCESS') {
        return $key_map;
    } else {
        return false;
    }
}

// Handle PDT verification request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $json_input = file_get_contents('php://input');
    $data = json_decode($json_input, true);
    
    if (empty($data['tx'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing transaction ID']);
        exit;
    }
    
    $tx = sanitize_input($data['tx']);
    $pdt_result = verifyPDTTransaction($tx);
    
    if ($pdt_result === false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'PDT verification failed']);
        exit;
    }
    
    // Payment verified
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Payment verified',
        'payment_data' => $pdt_result
    ]);
    exit;
}

/**
 * Sanitize input
 */
function sanitize_input($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

// GET request - return verification endpoint info
header('Content-Type: application/json');
echo json_encode([
    'endpoint' => 'POST /verify_payment.php',
    'method' => 'POST',
    'params' => [
        'tx' => 'PayPal transaction ID'
    ],
    'description' => 'Verify PayPal PDT transaction'
]);
?>
