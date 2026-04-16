<?php
/**
 * Sandbox Test Completion Handler
 * Handles test completion, sends confirmation email, and stores data in database
 */

require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/db_config.php');

ob_clean();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS, GET');
header('Access-Control-Allow-Headers: Content-Type, Accept');

// Handle OPTIONS request (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    die(json_encode(['success' => true]));
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'No data received']));
}

// Required fields
$invoice_id = trim($input['invoice_id'] ?? '');
$email = trim($input['email'] ?? '');
$full_name = trim($input['full_name'] ?? '');
$phone = trim($input['phone'] ?? '');
$test_type = trim($input['test_type'] ?? 'learning_module');
$score = isset($input['score']) ? floatval($input['score']) : 100;
$status = trim($input['status'] ?? 'completed');
$course = trim($input['course'] ?? '');
$duration = isset($input['duration']) ? intval($input['duration']) : 0; // in minutes

// Validation
if (!$invoice_id || !$email || !$full_name) {
    http_response_code(400);
    die(json_encode([
        'success' => false,
        'message' => 'Missing required fields: invoice_id, email, full_name'
    ]));
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    die(json_encode([
        'success' => false,
        'message' => 'Invalid email address'
    ]));
}

try {
    $db = getDB();
    
    // Add sandbox_tests table if it doesn't exist
    $db->exec("CREATE TABLE IF NOT EXISTS sandbox_tests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        invoice_id TEXT NOT NULL,
        test_type TEXT NOT NULL,
        full_name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL,
        course TEXT,
        score REAL DEFAULT 0,
        status TEXT DEFAULT 'pending',
        duration INTEGER DEFAULT 0,
        test_data TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        completed_at DATETIME,
        FOREIGN KEY(invoice_id) REFERENCES enrollments(invoice_id)
    )");
    
    // Insert test completion data
    $stmt = $db->prepare("
        INSERT INTO sandbox_tests 
        (invoice_id, test_type, full_name, email, phone, course, score, status, duration, completed_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $completed_at = ($status === 'completed') ? date('Y-m-d H:i:s') : null;
    
    $stmt->execute([
        $invoice_id,
        $test_type,
        $full_name,
        $email,
        $phone,
        $course,
        $score,
        $status,
        $duration,
        $completed_at
    ]);
    
    $test_id = $db->lastInsertId();
    
    // Log to payment_logs for reference
    logPayment($invoice_id, 'sandbox_test', $status, [
        'test_type' => $test_type,
        'score' => $score,
        'duration' => $duration
    ]);
    
    // Send confirmation email to customer
    $subject = "Test Completion Confirmation - $course";
    $body = buildConfirmationEmail($full_name, $course, $test_type, $score, $duration);
    
    $email_sent = sendEmail(
        $email,
        $subject,
        $body,
        ADMIN_EMAIL,
        $email
    );
    
    // Also notify admin
    $admin_subject = "Sandbox Test Completed - $full_name ($course)";
    $admin_body = buildAdminNotificationEmail($full_name, $email, $phone, $course, $test_type, $score, $duration, $invoice_id);
    
    $admin_notified = sendEmail(
        ADMIN_EMAIL,
        $admin_subject,
        $admin_body,
        ADMIN_EMAIL
    );
    
    // Log test completion
    $log_entry = "TEST_COMPLETED: " . date('Y-m-d H:i:s') . 
                 " | Invoice: $invoice_id | Email: $email | Test: $test_type | Score: $score\n";
    @file_put_contents(__DIR__ . '/sandbox_test_log.txt', $log_entry, FILE_APPEND);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Test completion recorded successfully',
        'test_id' => $test_id,
        'email_sent' => $email_sent,
        'admin_notified' => $admin_notified,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    
    // Log error
    $error_log = "ERROR: " . date('Y-m-d H:i:s') . " | " . $e->getMessage() . "\n";
    @file_put_contents(__DIR__ . '/sandbox_test_errors.log', $error_log, FILE_APPEND);
    
    die(json_encode([
        'success' => false,
        'message' => 'Database error occurred',
        'error' => $e->getMessage()
    ]));
} catch (Exception $e) {
    http_response_code(500);
    die(json_encode([
        'success' => false,
        'message' => 'Error processing test completion',
        'error' => $e->getMessage()
    ]));
}

/**
 * Build confirmation email for customer
 */
function buildConfirmationEmail($full_name, $course, $test_type, $score, $duration) {
    $score_text = ($score == 100) ? "Perfect Score!" : "Score: $score%";
    $duration_text = ($duration > 0) ? "\nDuration: " . floor($duration / 60) . " hours " . ($duration % 60) . " minutes" : "";
    
    $body = "Dear $full_name,\n\n";
    $body .= "Thank you for completing the sandbox test!\n\n";
    $body .= "=== TEST COMPLETION DETAILS ===\n";
    $body .= "Course: $course\n";
    $body .= "Test Type: $test_type\n";
    $body .= "Status: COMPLETED\n";
    $body .= "$score_text\n";
    $body .= $duration_text . "\n";
    $body .= "Date: " . date('F d, Y H:i:s') . "\n\n";
    
    $body .= "Your test has been successfully recorded in our system.\n";
    $body .= "Our team will review your results and contact you shortly with feedback.\n\n";
    
    $body .= "If you have any questions, please don't hesitate to reach out to us.\n\n";
    $body .= "Best regards,\n";
    $body .= SITE_NAME . " Team\n";
    $body .= SITE_URL . "\n";
    
    return $body;
}

/**
 * Build admin notification email
 */
function buildAdminNotificationEmail($full_name, $email, $phone, $course, $test_type, $score, $duration, $invoice_id) {
    $body = "New Sandbox Test Completed!\n\n";
    $body .= "=== CUSTOMER INFORMATION ===\n";
    $body .= "Name: $full_name\n";
    $body .= "Email: $email\n";
    $body .= "Phone: $phone\n";
    $body .= "Invoice ID: $invoice_id\n\n";
    
    $body .= "=== TEST DETAILS ===\n";
    $body .= "Course: $course\n";
    $body .= "Test Type: $test_type\n";
    $body .= "Score: $score%\n";
    $body .= "Duration: " . floor($duration / 60) . " hours " . ($duration % 60) . " minutes\n";
    $body .= "Completed At: " . date('Y-m-d H:i:s') . "\n\n";
    
    $body .= "Please review this test completion and contact the customer if needed.\n";
    $body .= "Dashboard: " . SITE_URL . "/admin_dashboard.php\n";
    
    return $body;
}

?>
