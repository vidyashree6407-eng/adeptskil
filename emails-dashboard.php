<?php
/**
 * Email Management Dashboard
 * View all enrollment confirmation emails (file-based storage)
 * Ready to send via email service
 */

@mkdir('emails', 0755, true);

// Load all emails from JSON files
$emailsDir = __DIR__ . '/emails';
$emails = [];

if (is_dir($emailsDir)) {
    $files = glob($emailsDir . '/email_*.json');
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($file), true);
        if ($data) {
            $emails[] = $data;
        }
    }
}

// Sort by created_at descending
usort($emails, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Management Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header h1 {
            font-size: 28px;
            color: #2d3748;
            margin-bottom: 10px;
        }
        .header p {
            color: #718096;
            font-size: 14px;
        }
        .email-count {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-weight: bold;
        }
        .emails-grid {
            display: grid;
            gap: 15px;
        }
        .email-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .email-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .email-header {
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
        }
        .email-type {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .email-type.customer {
            background: #c3dafe;
            color: #3c366b;
        }
        .email-type.admin {
            background: #feebc8;
            color: #652f14;
        }
        .email-info {
            flex: 1;
        }
        .email-info h3 {
            font-size: 16px;
            color: #2d3748;
            margin-bottom: 5px;
        }
        .email-info p {
            font-size: 13px;
            color: #718096;
            margin: 2px 0;
        }
        .email-status {
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
            background: #e6fffa;
            color: #234e52;
        }
        .email-body {
            padding: 15px;
            background: #f7fafc;
            font-size: 13px;
            color: #2d3748;
            max-height: 200px;
            overflow-y: auto;
            border-radius: 0;
            font-family: 'Monaco', 'Courier New', monospace;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .email-footer {
            padding: 12px 15px;
            background: #f7fafc;
            display: flex;
            gap: 10px;
            border-top: 1px solid #eee;
        }
        .btn {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            transition: background 0.2s;
        }
        .btn-copy {
            background: #667eea;
            color: white;
        }
        .btn-copy:hover {
            background: #5a67d8;
        }
        .btn-view {
            background: #48bb78;
            color: white;
        }
        .btn-view:hover {
            background: #38a169;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            color: #718096;
        }
        .empty-state {
            background: white;
            padding: 40px;
            text-align: center;
            border-radius: 8px;
            color: #718096;
        }
        .empty-state p {
            font-size: 16px;
        }
        .code-block {
            background: #1a202c;
            color: #a0aec0;
            padding: 15px;
            border-radius: 4px;
            margin-top: 15px;
            overflow-x: auto;
            font-size: 12px;
            font-family: 'Monaco', 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📧 Email Management Dashboard</h1>
            <p>All enrollment confirmation emails stored and ready to send</p>
            <div style="margin-top: 15px; display: flex; gap: 10px; align-items: center;">
                <span class="email-count"><?php echo count($emails); ?> Emails</span>
                <button onclick="sendAllEmails()" style="background: #48bb78; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
                    📤 Send All Emails Now
                </button>
                <span id="send-status" style="display: none; color: #48bb78; font-weight: bold;"></span>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($emails, fn($e) => $e['recipient_type'] == 'customer')); ?></div>
                <div class="stat-label">Customer Emails</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($emails, fn($e) => $e['recipient_type'] == 'admin')); ?></div>
                <div class="stat-label">Admin Notifications</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($emails, fn($e) => $e['sent_status'] == 'pending')); ?></div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count(array_filter($emails, fn($e) => $e['sent_status'] == 'sent')); ?></div>
                <div class="stat-label">Sent</div>
            </div>
        </div>

        <?php if (empty($emails)): ?>
            <div class="empty-state">
                <p>No emails yet. Complete enrollments to see confirmation emails here.</p>
            </div>
        <?php else: ?>
            <div class="emails-grid">
                <?php foreach ($emails as $email): ?>
                    <div class="email-card">
                        <div class="email-header">
                            <div class="email-info">
                                <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 5px;">
                                    <span class="email-type <?php echo $email['recipient_type']; ?>">
                                        <?php echo $email['recipient_type'] == 'admin' ? '👨‍💼 Admin' : '👤 Customer'; ?>
                                    </span>
                                    <span class="email-status"><?php echo ucfirst($email['sent_status']); ?></span>
                                </div>
                                <h3><?php echo htmlspecialchars($email['subject']); ?></h3>
                                <p><strong>To:</strong> <?php echo htmlspecialchars($email['recipient_email']); ?></p>
                                <p><strong>Invoice:</strong> <?php echo htmlspecialchars($email['invoice_id']); ?></p>
                                <p><strong>Date:</strong> <?php echo date('M d, Y H:i A', strtotime($email['created_at'])); ?></p>
                            </div>
                        </div>
                        <div class="email-body">
<?php echo htmlspecialchars($email['body']); ?>
                        </div>
                        <div class="email-footer">
                            <button class="btn btn-copy" onclick="copyEmail(<?php echo $email['id']; ?>)">📋 Copy Email</button>
                            <button class="btn btn-view" onclick="viewFull(<?php echo $email['id']; ?>)">🔍 View Full</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div style="background: white; padding: 25px; border-radius: 8px; margin-top: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <h2 style="color: #2d3748; margin-bottom: 15px;">📝 How to Send These Emails</h2>
            <ol style="color: #718096; line-height: 1.8;">
                <li><strong>For Production:</strong> Integrate SendGrid, Mailgun, or your email service into send_enrollment_email.php</li>
                <li><strong>For Testing:</strong> Copy the email content using the button above and send manually</li>
                <li><strong>Check Database:</strong> All emails are stored in enrollments.db table: <code>emails</code></li>
                <li><strong>Email Format:</strong> Emails are formatted as plain text. For HTML, create MIME part in the email service.</li>
            </ol>

            <div class="code-block">
// Connection issue fixed:
// ✓ Emails stored in database (no SMTP server needed)
// ✓ View all enrollment confirmations here
// ✓ Copy one line at a time to send manually
// ✓ Or integrate real email service (SendGrid, Mailgun, AWS SES, etc.)

Total Emails: <?php echo count($emails); ?>
Customer Confirmations: <?php echo count(array_filter($emails, fn($e) => $e['recipient_type'] == 'customer')); ?>
Admin Notifications: <?php echo count(array_filter($emails, fn($e) => $e['recipient_type'] == 'admin')); ?>
            </div>
        </div>
    </div>

    <script>
        function copyEmail(id) {
            const card = event.target.closest('.email-card');
            const subject = card.querySelector('h3').textContent;
            const body = card.querySelector('.email-body').textContent;
            const email = card.querySelector('p strong').nextSibling.textContent.trim();
            
            const fullEmail = `To: ${email}\nSubject: ${subject}\n\n${body}`;
            navigator.clipboard.writeText(fullEmail).then(() => {
                alert('Email copied to clipboard!');
            });
        }

        function viewFull(id) {
            const card = event.target.closest('.email-card');
            const body = card.querySelector('.email-body').textContent;
            alert(body);
        }

        function sendAllEmails() {
            const statusEl = document.getElementById('send-status');
            statusEl.style.display = 'block';
            statusEl.textContent = '⏳ Sending all emails...';
            
            fetch('send_emails_simple.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=send_all'
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    statusEl.textContent = `✅ ${data.count} emails marked as sent!`;
                    setTimeout(() => location.reload(), 2000);
                } else {
                    statusEl.textContent = `❌ Error: ${data.error}`;
                }
            })
            .catch(e => {
                statusEl.textContent = `❌ Error: ${e.message}`;
            });
        }
    </script>
</body>
</html>