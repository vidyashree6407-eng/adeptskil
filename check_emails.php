<?php
try {
    $db = new SQLite3(__DIR__ . '/enrollments.db');
    
    // Check emails table
    $result = $db->querySingle('SELECT COUNT(*) as count FROM emails', true);
    echo "=== EMAIL DATABASE CHECK ===\n";
    echo "Total emails stored: " . $result['count'] . "\n\n";
    
    if ($result['count'] > 0) {
        echo "Recent Emails:\n";
        $emails = $db->query('SELECT id, invoice_id, recipient_email, recipient_type, subject, created_at FROM emails ORDER BY created_at DESC LIMIT 10');
        
        while($row = $emails->fetchArray(SQLITE3_ASSOC)) {
            echo "ID: {$row['id']}\n";
            echo "  Invoice: {$row['invoice_id']}\n";
            echo "  To: {$row['recipient_email']} ({$row['recipient_type']})\n";
            echo "  Subject: {$row['subject']}\n";
            echo "  Stored: {$row['created_at']}\n\n";
        }
    } else {
        echo "❌ NO EMAILS STORED YET\n";
        echo "This means store_enrollment.php is not being called.\n";
        echo "Check console for errors or POST request failures.\n";
    }
    
} catch(Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
