<?php
/**
 * Database Configuration
 * SQLite database for storing customer enrollments
 */

// Database file path
define('DB_PATH', __DIR__ . '/enrollments.db');

// Create or connect to database
try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create tables if they don't exist
    createTables($pdo);
    
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

function createTables($pdo) {
    // Enrollments table
    $pdo->exec("CREATE TABLE IF NOT EXISTS enrollments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        invoice_id TEXT UNIQUE NOT NULL,
        full_name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL,
        company TEXT,
        city TEXT,
        pincode TEXT,
        address TEXT,
        course TEXT NOT NULL,
        amount REAL NOT NULL,
        payment_method TEXT NOT NULL,
        payment_status TEXT DEFAULT 'pending',
        payment_id TEXT,
        comments TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Payment logs table
    $pdo->exec("CREATE TABLE IF NOT EXISTS payment_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        invoice_id TEXT NOT NULL,
        payment_method TEXT NOT NULL,
        status TEXT NOT NULL,
        response_data TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY(invoice_id) REFERENCES enrollments(invoice_id)
    )");
    
    // Create index on email for quick lookup
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_email ON enrollments(email)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_invoice ON enrollments(invoice_id)");
}

// Helper function to get database connection
function getDB() {
    global $pdo;
    return $pdo;
}

// Helper function to log payment
function logPayment($invoice_id, $method, $status, $response_data = null) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            INSERT INTO payment_logs (invoice_id, payment_method, status, response_data)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $invoice_id,
            $method,
            $status,
            is_array($response_data) ? json_encode($response_data) : $response_data
        ]);
    } catch (Exception $e) {
        error_log('Payment logging failed: ' . $e->getMessage());
    }
}

// Helper function to update enrollment status
function updateEnrollmentStatus($invoice_id, $status, $payment_id = null) {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            UPDATE enrollments 
            SET payment_status = ?, payment_id = ?, updated_at = CURRENT_TIMESTAMP
            WHERE invoice_id = ?
        ");
        $stmt->execute([$status, $payment_id, $invoice_id]);
        return true;
    } catch (Exception $e) {
        error_log('Status update failed: ' . $e->getMessage());
        return false;
    }
}

// Helper function to get enrollment by invoice
function getEnrollmentByInvoice($invoice_id) {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM enrollments WHERE invoice_id = ?");
        $stmt->execute([$invoice_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log('Enrollment lookup failed: ' . $e->getMessage());
        return null;
    }
}
?>
