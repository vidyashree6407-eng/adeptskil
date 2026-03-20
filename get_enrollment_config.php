<?php
/**
 * Enrollment Configuration API
 * Returns PayPal Client ID and course pricing for the enrollment form
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Load payment configuration
require_once(__DIR__ . '/payment_config.php');

// Return configuration as JSON
echo json_encode([
    'paypal_client_id' => PAYPAL_CLIENT_ID,
    'currency' => PAYPAL_CURRENCY,
    'courses_pricing' => $coursesPricing,
    'payments_enabled' => PAYMENTS_ENABLED
]);
?>
