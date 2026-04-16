<?php
/**
 * Direct Enrollment Test - Bypass PayPal for testing confirmation emails
 * Access via: http://localhost:8000/test-direct-enrollment.php
 */

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/db_config.php');

// Handle test form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($input) {
        // Extract data
        $fullName = trim($input['fullName'] ?? '');
        $email = trim($input['email'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $company = trim($input['company'] ?? '');
        $city = trim($input['city'] ?? '');
        $course = trim($input['course'] ?? '');
        $amount = floatval($input['amount'] ?? 0);
        
        // Validation
        if (!$fullName || !$email || !$phone || !$city || !$course) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit;
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid email']);
            exit;
        }
        
        try {
            // Save to database
            $db = getDB();
            $invoice = 'TEST-' . date('YmdHis');
            
            $stmt = $db->prepare("
                INSERT INTO enrollments (
                    invoice_id, full_name, email, phone, company, city, pincode, address,
                    course, amount, payment_method, payment_status, payment_id, comments
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $invoice,
                $fullName,
                $email,
                $phone,
                $company,
                $city,
                '',
                '',
                $course,
                $amount,
                'test',
                'completed',
                'TEST-PAYMENT',
                'Test enrollment'
            ]);
            
            // Send confirmation email
            include_once(__DIR__ . '/process_enrollment.php');
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Test enrollment saved and email sent',
                'invoice_id' => $invoice
            ]);
            
        } catch (Exception $e) {
            error_log('Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        exit;
    }
}

// Show HTML form
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Enrollment (Direct)</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 40px 20px; }
        .container { max-width: 500px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; color: #555; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 1rem; }
        button { width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; margin-top: 20px; }
        button:hover { background: #764ba2; }
        .message { margin-top: 20px; padding: 15px; border-radius: 4px; text-align: center; font-weight: bold; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Test Enrollment (Direct)</h1>
        <p style="color: #666; margin-bottom: 20px;">This bypasses PayPal to test confirmation emails directly.</p>
        
        <form id="testForm">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="fullName" required value="Test Student">
            </div>
            
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required value="vidyashree6407@gmail.com">
            </div>
            
            <div class="form-group">
                <label>Phone *</label>
                <input type="tel" name="phone" required value="9876543210">
            </div>
            
            <div class="form-group">
                <label>Company</label>
                <input type="text" name="company" value="Test Company">
            </div>
            
            <div class="form-group">
                <label>City *</label>
                <input type="text" name="city" required value="Bangalore">
            </div>
            
            <div class="form-group">
                <label>Course *</label>
                <input type="text" name="course" required value="Leadership Training">
            </div>
            
            <div class="form-group">
                <label>Amount (₹)</label>
                <input type="number" name="amount" value="5000">
            </div>
            
            <button type="submit">📧 Submit & Send Email</button>
        </form>
        
        <div id="result"></div>
    </div>
    
    <script>
        document.getElementById('testForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<div class="message">Submitting...</div>';
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);
            
            try {
                const response = await fetch('/test-direct-enrollment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    resultDiv.innerHTML = `<div class="message success">✓ ${result.message}<br>Invoice: ${result.invoice_id}<br>Check your email!</div>`;
                } else {
                    resultDiv.innerHTML = `<div class="message error">✗ ${result.message}</div>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="message error">✗ Error: ${error.message}</div>`;
            }
        });
    </script>
</body>
</html>
