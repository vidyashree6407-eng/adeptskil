<?php
/**
 * Chatbot Message Handler
 * Receives messages from the chatbot and sends notifications to your phone
 */

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/chatbot_errors.log');

// Get the incoming message
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['message'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$message = $input['message'];
$timestamp = $input['timestamp'] ?? date('Y-m-d H:i:s');

// Configuration - UPDATE THESE WITH YOUR DETAILS
$YOUR_EMAIL = 'info@adeptskil.com';           // Your email to receive notifications
$YOUR_PHONE = '+1234567890';                  // Your phone number (with country code)
$ADMIN_EMAIL = 'admin@adeptskil.com';         // Admin email

// 1. OPTION A: Send Email Notification (Always works)
sendEmailNotification($message, $timestamp, $YOUR_EMAIL);

// 2. OPTION B: Send SMS via Twilio (if configured)
// Uncomment and configure if using Twilio
// sendSMSViatwilio($message, $timestamp, $YOUR_PHONE);

// 3. OPTION C: Send WhatsApp Message via Twilio
// Uncomment and configure if using Twilio WhatsApp
// sendWhatsAppViaTwilio($message, $timestamp, $YOUR_PHONE);

// 4. OPTION D: Store message for later retrieval
storeMessageLocally($message, $timestamp);

// Return success response
http_response_code(200);
// File removed as part of chatbot decommissioning.
exit;
