<?php
/**
 * SendGrid Email Sender Integration
 * Sends stored emails via SendGrid API
 * Sign up free at: https://sendgrid.com (100 emails/day free)
 */

// Get all stored emails
@mkdir('emails', 0755, true);
$emailsDir = __DIR__ . '/emails';
$storedEmails = [];

if (is_dir($emailsDir)) {
    $files = glob($emailsDir . '/email_*.json');
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data && $data['sent_status'] === 'pending') {
            $storedEmails[] = ['file' => $file, 'data' => $data];
        }
    }
}

echo "=== SENDGRID EMAIL SENDER ===\n\n";
echo "Pending emails to send: " . count($storedEmails) . "\n\n";

// SendGrid settings (ADD YOUR API KEY)
$sendgridApiKey = 'SG.YOUR_API_KEY_HERE'; // Replace this with your actual API key
$sendgridFromEmail = 'noreply@adeptskil.com';

if ($sendgridApiKey === 'SG.YOUR_API_KEY_HERE') {
    echo "❌ ERROR: SendGrid API key not configured!\n\n";
    echo "Setup instructions:\n";
    echo "1. Sign up at: https://sendgrid.com\n";
    echo "2. Get your API key from: Settings → API Keys\n";
    echo "3. Edit this file and replace 'SG.YOUR_API_KEY_HERE' with your actual key\n";
    echo "4. Run: php send_emails_sendgrid.php\n";
    exit;
}

// Send each pending email
if (count($storedEmails) > 0) {
    foreach ($storedEmails as $emailItem) {
        $email = $emailItem['data'];
        $file = $emailItem['file'];
        
        echo "Sending: {$email['subject']} to {$email['recipient_email']}... ";
        
        // Prepare SendGrid API request
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.sendgrid.com/v3/mail/send',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $sendgridApiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode([
                'personalizations' => [
                    [
                        'to' => [['email' => $email['recipient_email'], 'name' => $email['recipient_name']]]
                    ]
                ],
                'from' => ['email' => $sendgridFromEmail, 'name' => 'Adeptskil'],
                'subject' => $email['subject'],
                'content' => [
                    ['type' => 'text/plain', 'value' => $email['body']]
                ]
            ]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($status === 202) {
            echo "✅ SENT\n";
            
            // Mark as sent
            $email['sent_status'] = 'sent';
            $email['sent_at'] = date('Y-m-d H:i:s');
            file_put_contents($file, json_encode($email, JSON_PRETTY_PRINT));
        } else {
            echo "❌ FAILED (HTTP $status)\n";
            if ($response) {
                echo "  Error: " . substr($response, 0, 100) . "\n";
            }
        }
    }
} else {
    echo "No pending emails to send.\n";
}

echo "\n=== UPDATED EMAILS ===\n";
echo "View all emails at: http://localhost:5501/emails-dashboard.php\n";
?>
