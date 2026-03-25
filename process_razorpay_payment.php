<?php
/**
 * Razorpay Payment Processing
 * Handles Razorpay payments for course enrollment
 * Works best with Indian customers / INR currency
 */

header('Content-Type: text/html; charset=utf-8');

// Get enrollment ID from URL
$enrollmentId = isset($_GET['enrollmentId']) ? sanitize($_GET['enrollmentId']) : '';

if (!$enrollmentId) {
    die("Error: Invalid enrollment ID");
}

// Razorpay Configuration (TEST MODE)
define('RAZORPAY_KEY_ID', 'rzp_test_1sCuWrHPqbUaea');  // Test Key ID
define('RAZORPAY_KEY_SECRET', 'b3P5vE9H4K8mL2Xq7Wz1Yj3');  // Test Key Secret

// Payment data (in production, retrieve from database)
$payment_data = array(
    'amount' => 149.00,
    'currency' => 'INR',
    'course' => 'Professional Training Course',
    'student_name' => 'Test User',
    'student_email' => 'test@example.com',
    'student_phone' => '+91-9999999999'
);

// Convert amount to paise (Razorpay uses smallest currency unit)
$amount_in_paise = (int)($payment_data['amount'] * 100);

// Generate invoice number
$invoice_number = 'ENR-' . $enrollmentId . '-' . date('YmdHis');

// Log payment initiation
error_log("Razorpay payment initiated - Enrollment ID: $enrollmentId");
error_log("Payment Amount: " . $payment_data['amount'] . " " . $payment_data['currency']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Razorpay Payment - Adeptskil</title>
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
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        .container h2 {
            color: #667eea;
            margin-bottom: 20px;
            font-size: 1.8rem;
        }
        .price-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin: 25px 0;
        }
        .price-display p {
            font-size: 0.9rem;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        .price-amount {
            font-size: 2.5rem;
            font-weight: 700;
        }
        .payment-info {
            background: #f0f4ff;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-size: 0.9rem;
            color: #2d3748;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 20px;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        .razorpay-logo {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Payment via Razorpay</h2>
        
        <div class="price-display">
            <p>Amount to Pay</p>
            <div class="price-amount">₹<?php echo number_format($payment_data['amount'], 2); ?></div>
            <p><?php echo htmlspecialchars($payment_data['currency']); ?></p>
        </div>

        <div class="payment-info">
            <p><strong>Course:</strong> <?php echo htmlspecialchars($payment_data['course']); ?></p>
            <p><strong>Invoice:</strong> <?php echo htmlspecialchars($invoice_number); ?></p>
            <p><strong>Student:</strong> <?php echo htmlspecialchars($payment_data['student_name']); ?></p>
        </div>

        <form id="razorpay-form">
            <button type="button" id="razorpay-button">
                <i class="fas fa-credit-card"></i> Pay with Razorpay
            </button>
        </form>

        <div class="razorpay-logo">
            Powered by Razorpay India Pvt. Ltd.
        </div>
    </div>

    <!-- Razorpay Checkout Script -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        // Razorpay Payment Options
        const razorpayOptions = {
            key: '<?php echo RAZORPAY_KEY_ID; ?>',
            amount: <?php echo $amount_in_paise; ?>,
            currency: '<?php echo $payment_data['currency']; ?>',
            name: 'Adeptskil',
            description: 'Course: <?php echo htmlspecialchars($payment_data['course']); ?>',
            image: 'https://adeptskil.com/images/FAVICON.jpg',
            order_id: '<?php echo $invoice_number; ?>',
            handler: function(response) {
                console.log('✓ Payment successful!');
                console.log('Payment ID:', response.razorpay_payment_id);
                
                // Log payment success
                fetch('log_payment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        method: 'razorpay',
                        payment_id: response.razorpay_payment_id,
                        enrollment_id: '<?php echo $enrollmentId; ?>',
                        amount: <?php echo $payment_data['amount']; ?>,
                        status: 'completed'
                    })
                });
                
                // Redirect to success
                window.location.href = 'success.html?invoice=<?php echo urlencode($invoice_number); ?>&payment_id=' + response.razorpay_payment_id;
            },
            prefill: {
                name: '<?php echo htmlspecialchars($payment_data['student_name']); ?>',
                email: '<?php echo htmlspecialchars($payment_data['student_email']); ?>',
                contact: '<?php echo htmlspecialchars($payment_data['student_phone']); ?>'
            },
            theme: {
                color: '#667eea'
            },
            modal: {
                ondismiss: function() {
                    console.log('Payment cancelled by user');
                    window.location.href = 'cancel.html?invoice=<?php echo urlencode($invoice_number); ?>';
                }
            }
        };

        // Trigger Razorpay checkout on button click
        document.getElementById('razorpay-button').addEventListener('click', function(e) {
            e.preventDefault();
            const rzp = new Razorpay(razorpayOptions);
            rzp.open();
        });

        // Auto-trigger on page load (optional)
        window.addEventListener('load', function() {
            // Uncomment to auto-open: document.getElementById('razorpay-button').click();
        });
    </script>
</body>
</html>

<?php
// Utility function to sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
}
?>
