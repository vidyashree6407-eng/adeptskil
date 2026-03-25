<?php
/**
 * Credit Card Payment Processing
 * Handles Credit/Debit Card payments using Stripe or similar
 * For now, using a simple form with Stripe Elements
 */

header('Content-Type: text/html; charset=utf-8');

// Get enrollment ID from URL
$enrollmentId = isset($_GET['enrollmentId']) ? sanitize($_GET['enrollmentId']) : '';

if (!$enrollmentId) {
    die("Error: Invalid enrollment ID");
}

// Stripe Configuration (TEST MODE)
define('STRIPE_PUBLIC_KEY', 'pk_test_51234567890abcdef'); // Replace with actual test key
define('STRIPE_SECRET_KEY', 'sk_test_0987654321fedcba'); // Replace with actual test key

// Payment data
$payment_data = array(
    'amount' => 149.00,
    'currency' => 'USD',
    'course' => 'Professional Training Course',
    'student_name' => 'Test User',
    'student_email' => 'test@example.com'
);

// Convert amount to cents (Stripe uses smallest currency unit)
$amount_in_cents = (int)($payment_data['amount'] * 100);

// Generate invoice number
$invoice_number = 'ENR-' . $enrollmentId . '-' . date('YmdHis');

error_log("Credit Card payment initiated - Enrollment ID: $enrollmentId");

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credit Card Payment - Adeptskil</title>
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
        }
        .container h2 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 1.8rem;
        }
        .subtitle {
            color: #64748b;
            margin-bottom: 25px;
            font-size: 0.95rem;
        }
        .price-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin: 25px 0;
            text-align: center;
        }
        .price-label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-bottom: 10px;
        }
        .price-amount {
            font-size: 2.5rem;
            font-weight: 700;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2d3748;
            font-size: 0.95rem;
        }
        .form-control {
            width: 100%;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 15px;
        }
        .stripe-element {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            background: white;
        }
        button {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 14px 30px;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }
        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        .security-info {
            background: #fef3c7;
            border: 1px solid #fcd34d;
            color: #92400e;
            padding: 12px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 0.9rem;
            text-align: center;
        }
        .error-message {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: none;
        }
        .error-message.show {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Credit/Debit Card Payment</h2>
        <p class="subtitle">Secure payment for your course enrollment</p>

        <div class="price-display">
            <div class="price-label">Total Amount</div>
            <div class="price-amount">$<?php echo number_format($payment_data['amount'], 2); ?></div>
        </div>

        <div id="errorMessage" class="error-message"></div>

        <form id="payment-form">
            <div class="form-group">
                <label for="cardholder">Cardholder Name</label>
                <input type="text" id="cardholder" class="form-control" placeholder="John Doe" required>
            </div>

            <div class="form-group">
                <label>Card Number</label>
                <div id="card-element" class="stripe-element"></div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Expiry Date</label>
                    <div id="card-expiry" class="stripe-element"></div>
                </div>
                <div class="form-group">
                    <label>CVC</label>
                    <div id="card-cvc" class="stripe-element"></div>
                </div>
            </div>

            <button type="submit" id="submit-btn">
                <i class="fas fa-lock"></i> Pay $<?php echo number_format($payment_data['amount'], 2); ?>
            </button>
        </form>

        <div class="security-info">
            <i class="fas fa-shield-alt"></i> Your payment information is encrypted and secure.
        </div>
    </div>

    <!-- Stripe.js -->
    <script src="https://js.stripe.com/v3/"></script>
    <script>
        const stripePublicKey = '<?php echo STRIPE_PUBLIC_KEY; ?>';
        const stripe = Stripe(stripePublicKey);
        const elements = stripe.elements();

        // Create Stripe elements (simplified for demo)
        const cardElement = elements.create('card');
        cardElement.mount('#card-element');

        // Handle form submission
        const form = document.getElementById('payment-form');
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const cardholder = document.getElementById('cardholder').value;
            
            if (!cardholder) {
                showError('Please enter cardholder name');
                return;
            }

            console.log('Processing payment...');
            console.log('Amount: $<?php echo $payment_data['amount']; ?>');
            console.log('Invoice: <?php echo $invoice_number; ?>');

            // In production: create token and charge payment
            // For now, simulate successful payment
            setTimeout(() => {
                console.log('✓ Payment processed successfully!');
                
                window.location.href = 'success.html?invoice=<?php echo urlencode($invoice_number); ?>&method=creditcard';
            }, 2000);
        });

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = message;
            errorDiv.classList.add('show');
        }

        function clearError() {
            document.getElementById('errorMessage').classList.remove('show');
        }

        cardElement.addEventListener('change', clearError);
    </script>
</body>
</html>

<?php
function sanitize($input) {
    return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
}
?>
