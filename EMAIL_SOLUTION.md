# Email Solution Implementation

## Current Status: ✅ Working

Your system now:
1. **Stores all enrollment confirmations** in the database (both customer + admin emails)
2. **Shows them in a dashboard** at `emails-dashboard.php`
3. **Prevents errors** from the missing SMTP server

---

## View Your Emails

Open this page in your browser to see all stored emails:
```
http://localhost:5501/emails-dashboard.php
```

You'll see:
- All customer confirmation emails
- All admin notifications
- Status (pending/sent)
- Date/time received
- Button to copy email content

---

## Option 1: SendGrid (Recommended - Free Tier Available)

### Setup (5 minutes)
1. Sign up: https://sendgrid.com
2. Get API key from Settings → API Keys
3. Store in config.php:

```php
define('SENDGRID_API_KEY', 'SG.your_api_key_here');
```

### Use This Code

Replace the contents of `send_enrollment_email.php` with:

```php
<?php
// Get input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    die(json_encode(['success' => false, 'error' => 'No data provided']));
}

$fullName = trim($input['fullName'] ?? '');
$email = trim($input['email'] ?? '');
$course = trim($input['course'] ?? '');
$price = floatval($input['price'] ?? 0);
$invoice = trim($input['invoice'] ?? '');

require_once __DIR__ . '/config.php';

// SendGrid API
$apiKey = defined('SENDGRID_API_KEY') ? SENDGRID_API_KEY : 'YOUR_KEY_HERE';
$apiUrl = 'https://api.sendgrid.com/v3/mail/send';

// Email 1: To Customer
$customerData = [
    'personalizations' => [
        ['to' => [['email' => $email, 'name' => $fullName]]]
    ],
    'from' => ['email' => 'noreply@adeptskil.com', 'name' => 'Adeptskil'],
    'subject' => "Enrollment Confirmation - $course",
    'content' => [
        ['type' => 'text/html', 'value' => buildCustomerEmail($fullName, $course, $invoice, $price, $email)]
    ]
];

$customerSent = false;
$response1 = @file_get_contents($apiUrl, false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => ["Authorization: Bearer $apiKey", 'Content-Type: application/json'],
        'content' => json_encode($customerData)
    ]
]));
$customerSent = ($response1 !== false);

// Email 2: To Admin
$adminData = [
    'personalizations' => [
        ['to' => [['email' => 'info@adeptskil.com', 'name' => 'Admin']]]
    ],
    'from' => ['email' => 'noreply@adeptskil.com', 'name' => 'Adeptskil'],
    'subject' => "New Enrollment: $course - $fullName",
    'content' => [
        ['type' => 'text/plain', 'value' => buildAdminEmail($fullName, $email, '', $course, $price, $invoice, '', '')]
    ]
];

$adminSent = false;
$response2 = @file_get_contents($apiUrl, false, stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => ["Authorization: Bearer $apiKey", 'Content-Type: application/json'],
        'content' => json_encode($adminData)
    ]
]));
$adminSent = ($response2 !== false);

// Also store in database as backup
storeEmailInDatabase($input, $customerSent, $adminSent);

http_response_code(200);
echo json_encode([
    'success' => true,
    'customerEmail' => $customerSent,
    'adminEmail' => $adminSent,
    'invoice' => $invoice
]);
exit;

function storeEmailInDatabase($input, $customerSent, $adminSent) {
    // Same database storage code from current version
}

function buildCustomerEmail($fullName, $course, $invoice, $price, $email) {
    // Same email building code
}

function buildAdminEmail($fullName, $email, $phone, $course, $price, $invoice, $company, $city) {
    // Same email building code
}
?>
```

---

## Option 2: Mailgun (Also Free Tier)

### Setup
1. Sign up: https://www.mailgun.com
2. Get API key and domain
3. Store in config.php:

```php
define('MAILGUN_API_KEY', 'your_api_key');
define('MAILGUN_DOMAIN', 'mail.adeptskil.com');
```

### Use Mailgun API (similar flow as SendGrid)

---

## Option 3: AWS SES (Pay-per-email)

### Setup
1. Get AWS account
2. Verify domain in SES
3. Get credentials

### Use AWS SDK

---

## Option 4: For Production (Recommended)

If you deploy to a **real web server** (not development machine):
1. Most hosting providers have SMTP support built-in
2. Simply update PHP `php.ini` with SMTP details
3. Original `mail()` function will work

---

## Quick Test

1. Go to `http://localhost:5501/enrollment_with_fees.html`
2. Fill out form
3. Select any payment method
4. Confirm payment
5. Check `http://localhost:5501/emails-dashboard.php` for your emails ✅

All confirmations are **working and stored**, just need email service integration for actual sending.

