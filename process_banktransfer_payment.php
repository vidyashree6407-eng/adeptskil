<?php
/**
 * Bank Transfer Payment Processing
 * Shows bank details and payment instructions
 */

header('Content-Type: text/html; charset=utf-8');

// Get enrollment ID from URL
$enrollmentId = isset($_GET['enrollmentId']) ? sanitize($_GET['enrollmentId']) : '';

if (!$enrollmentId) {
    die("Error: Invalid enrollment ID");
}

// Generate invoice number
$invoice_number = 'ENR-' . $enrollmentId . '-' . date('YmdHis');

// Payment details
$amount = 149.00;
$course = 'Professional Training Course';
$student_email = 'test@example.com';

// Bank Details (Replace with actual bank details)
$bank_details = array(
    'bank_name' => 'Adeptskil Business Bank',
    'account_name' => 'Adeptskil Training Institute',
    'account_number' => '1234567890',
    'routing_number' => '021000021',
    'swift_code' => 'ADSBUSXXXXX',
    'iban' => 'US12ADSB0012345678',
    'reference' => $invoice_number
);

error_log("Bank Transfer payment initiated - Enrollment ID: $enrollmentId");
error_log("Invoice: $invoice_number");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Transfer Payment - Adeptskil</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 600px;
            width: 100%;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h2 {
            color: #667eea;
            font-size: 1.8rem;
            margin-bottom: 10px;
        }
        .header p {
            color: #64748b;
            font-size: 0.95rem;
        }
        .amount-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 30px;
        }
        .amount-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        .amount-value {
            font-size: 2.5rem;
            font-weight: 700;
        }
        .invoice-ref {
            background: #f0f4ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .invoice-ref strong {
            color: #667eea;
        }
        .bank-details {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 25px;
        }
        .bank-details h3 {
            color: #2d3748;
            font-size: 1.1rem;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .detail-row {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 15px;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .detail-label {
            font-weight: 600;
            color: #64748b;
            font-size: 0.9rem;
        }
        .detail-value {
            color: #2d3748;
            font-family: 'Courier New', monospace;
            word-break: break-all;
        }
        .copy-btn {
            background: #e0e7ff;
            color: #667eea;
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.3s ease;
            margin-left: 10px;
        }
        .copy-btn:hover {
            background: #c7d2fe;
        }
        .instructions {
            background: #fef3c7;
            border: 2px solid #fcd34d;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .instructions h4 {
            color: #92400e;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .instructions ol {
            color: #92400e;
            margin-left: 20px;
            line-height: 1.8;
        }
        .instructions li {
            margin-bottom: 8px;
        }
        .confirmation-box {
            background: #dcfce7;
            border: 2px solid #86efac;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .confirmation-box h4 {
            color: #166534;
            margin-bottom: 10px;
        }
        .confirmation-box p {
            color: #166534;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }
        .button-group {
            display: flex;
            gap: 10px;
        }
        button {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.95rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        .btn-secondary {
            background: #e2e8f0;
            color: #2d3748;
        }
        .btn-secondary:hover {
            background: #cbd5e1;
        }
        .note {
            background: #ecfdf5;
            border-left: 4px solid #10b981;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            color: #047857;
            font-size: 0.9rem;
        }
        @media (max-width: 768px) {
            .detail-row {
                grid-template-columns: 1fr;
            }
            .button-group {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-university"></i> Bank Transfer</h2>
            <p>Complete your payment via direct bank transfer</p>
        </div>

        <div class="amount-box">
            <div class="amount-label">Amount to Transfer</div>
            <div class="amount-value">$<?php echo number_format($amount, 2); ?></div>
            <p style="margin-top: 10px; font-size: 0.9rem;">USD (United States Dollar)</p>
        </div>

        <div class="invoice-ref">
            <strong>Reference/Invoice Number:</strong> 
            <?php echo htmlspecialchars($invoice_number); ?>
            <button class="copy-btn" onclick="copyToClipboard('<?php echo htmlspecialchars($invoice_number); ?>')">
                <i class="fas fa-copy"></i> Copy
            </button>
        </div>

        <div class="bank-details">
            <h3><i class="fas fa-building"></i> Bank Details</h3>
            
            <div class="detail-row">
                <div class="detail-label">Bank Name</div>
                <div class="detail-value"><?php echo htmlspecialchars($bank_details['bank_name']); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Account Name</div>
                <div class="detail-value"><?php echo htmlspecialchars($bank_details['account_name']); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Account Number</div>
                <div class="detail-value">
                    <?php echo htmlspecialchars($bank_details['account_number']); ?>
                    <button class="copy-btn" onclick="copyToClipboard('<?php echo htmlspecialchars($bank_details['account_number']); ?>')">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Routing Number</div>
                <div class="detail-value">
                    <?php echo htmlspecialchars($bank_details['routing_number']); ?>
                    <button class="copy-btn" onclick="copyToClipboard('<?php echo htmlspecialchars($bank_details['routing_number']); ?>')">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">SWIFT Code</div>
                <div class="detail-value">
                    <?php echo htmlspecialchars($bank_details['swift_code']); ?>
                    <button class="copy-btn" onclick="copyToClipboard('<?php echo htmlspecialchars($bank_details['swift_code']); ?>')">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">IBAN</div>
                <div class="detail-value">
                    <?php echo htmlspecialchars($bank_details['iban']); ?>
                    <button class="copy-btn" onclick="copyToClipboard('<?php echo htmlspecialchars($bank_details['iban']); ?>')">
                        <i class="fas fa-copy"></i> Copy
                    </button>
                </div>
            </div>
        </div>

        <div class="instructions">
            <h4><i class="fas fa-list-ol"></i> Transfer Instructions</h4>
            <ol>
                <li>Use your bank's online banking or visit a branch</li>
                <li>Initiate a new transfer to the account details above</li>
                <li>Enter the amount: <strong>$<?php echo number_format($amount, 2); ?></strong></li>
                <li><strong>IMPORTANT:</strong> Include the reference number in the payment memo/description: <strong><?php echo htmlspecialchars($invoice_number); ?></strong></li>
                <li>Complete the transfer</li>
                <li>Allow 2-3 business days for the payment to be processed</li>
            </ol>
        </div>

        <div class="confirmation-box">
            <h4><i class="fas fa-check-circle"></i> Payment Initiation Complete</h4>
            <p>We have recorded your payment intent. Once we receive the transfer, your enrollment will be confirmed.</p>
            <div class="button-group">
                <button class="btn-secondary" onclick="window.location.href='enrollment_with_fees.html'">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button class="btn-primary" onclick="markPaymentInitiated()">
                    <i class="fas fa-check"></i> Payment Initiated
                </button>
            </div>
        </div>

        <div class="note">
            <i class="fas fa-info-circle"></i> <strong>Note:</strong> A confirmation email will be sent to <?php echo htmlspecialchars($student_email); ?> once the payment is received and verified.
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Copied to clipboard!');
            }).catch(() => {
                alert('Copy failed. Please try again.');
            });
        }

        function markPaymentInitiated() {
            console.log('Payment initiated - Bank transfer details recorded');
            
            // Store payment data
            const paymentData = {
                method: 'banktransfer',
                invoice: '<?php echo htmlspecialchars($invoice_number); ?>',
                amount: <?php echo $amount; ?>,
                timestamp: new Date().toISOString(),
                status: 'pending_transfer'
            };

            // Log to backend (optional)
            fetch('log_payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(paymentData)
            }).then(() => {
                // Redirect to success/pending page
                window.location.href = 'success.html?invoice=<?php echo urlencode($invoice_number); ?>&method=banktransfer&status=pending';
            }).catch(() => {
                // Still redirect even if logging fails
                window.location.href = 'success.html?invoice=<?php echo urlencode($invoice_number); ?>&method=banktransfer';
            });
        }
    </script>
</body>
</html>

<?php
function sanitize($input) {
    return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
}
?>
