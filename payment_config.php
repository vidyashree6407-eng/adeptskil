<?php
/**
 * Course and Payment Configuration
 * Edit this file to manage course prices and payment settings
 */

// ===========================================
// PAYPAL CONFIGURATION
// ===========================================

// Your PayPal Client ID (get from PayPal Developer Dashboard)
// For Sandbox (testing): Use your Sandbox Client ID
// For Live (production): Use your Live Client ID
define('PAYPAL_CLIENT_ID', 'AR5aMJDN1aJshbh5769VuR4uQH4p8ANvYcEmQSUmdTxEP2UUiM2MQf6iRzqrn4ct71J8GpAmaKCPwu9F');

// Supported currencies (make sure PayPal account supports this currency)
// Common options: USD, EUR, GBP, INR, AUD, CAD, etc.
define('PAYPAL_CURRENCY', 'USD');

// ===========================================
// COURSE PRICING CONFIGURATION
// ===========================================
// Add all your courses and their prices here
// Make sure course names match exactly with courses.html

$coursesPricing = array(
    // Format: 'Course Name' => price_in_numbers
    
    // Default price for courses not listed
    'default' => 99.00,
    
    // Adeptskil Leadership Courses:
    'Account Management' => 149.00,
    'Absence Management' => 1.00,
    'Conflict Management Training' => 149.00,
    'Crisis Management Training' => 149.00,
    'Effective Vendor Management' => 149.00,
    'Employee Engagement' => 149.00,
    'Essential Management Skills' => 149.00,
    'Human Resource Management Training' => 149.00,
    'Leading Effective Teams' => 149.00,
    'Manager Management Training' => 149.00,
    'Supply Chain Management' => 149.00,
    'Managing a Virtual Team' => 149.00,
    'Managing Performance' => 149.00,
    'Performance Management' => 149.00,
    'Problem Management Professional' => 149.00,
    'Risk Assessment and Management' => 149.00,
    'Risk Assessment Training' => 149.00,
    'Change Management - Change Matters' => 149.00,
    'Motivating People' => 149.00,
    'Building High Performing Teams' => 149.00,
    'Leadership Skills - Lead, Motivate & Inspire' => 199.00,
    
    // Add more courses as needed:
    // 'Course Name Here' => 99.00,
);

// Function to get course price
function getCoursePricing($courseName) {
    global $coursesPricing;
    if (isset($coursesPricing[$courseName])) {
        return $coursesPricing[$courseName];
    }
    return $coursesPricing['default'];
}

// ===========================================
// PAYMENT SETTINGS
// ===========================================

// Enable/disable payment feature
define('PAYMENTS_ENABLED', true);

// Payment method: 'paypal' (future: 'stripe', 'razorpay')
define('PAYMENT_METHOD', 'paypal');

// Email notification for completed payments
define('SEND_PAYMENT_CONFIRMATION', true);

// Payment receipt format
define('PAYMENT_RECEIPT_FORMAT', 'detailed'); // 'simple' or 'detailed'

// ===========================================
// ENROLLMENT SETTINGS
// ===========================================

// Required fields for enrollment
$requiredEnrollmentFields = array(
    'fullName',
    'email',
    'phone',
    'city'
);

// Optional fields
$optionalEnrollmentFields = array(
    'company',
    'message'
);

// Email template - stored enrollments
// If TRUE: enrollments saved to JSON file
// If FALSE: enrollments only in database (if configured)
define('SAVE_ENROLLMENTS_TO_FILE', true);
define('ENROLLMENTS_FILE', __DIR__ . '/enrollments.json');

// ===========================================
// DISCOUNT/COUPON CONFIGURATION (Future)
// ===========================================

// Discount codes - currently not active
// Format: 'CODE' => percentage_discount
$discountCodes = array(
    // 'WELCOME20' => 20,  // 20% discount
    // 'STUDENT15' => 15,  // 15% discount
);

// Enable coupon validation
define('COUPONS_ENABLED', false);

?>
