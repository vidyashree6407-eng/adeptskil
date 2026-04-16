<?php
require_once(__DIR__ . '/db_config.php');

$db = getDB();

// Check enrollments
$count = $db->querySingle('SELECT COUNT(*) as count FROM enrollments', true);
echo "=== Enrollment Status ===\n";
echo "Total enrollments: " . $count['count'] . "\n\n";

if ($count['count'] > 0) {
    echo "Last 5 enrollments:\n";
    $enrollments = $db->query('SELECT invoice_id, full_name, email, created_at FROM enrollments ORDER BY created_at DESC LIMIT 5');
    
    while ($row = $enrollments->fetchArray(SQLITE3_ASSOC)) {
        echo "  - {$row['invoice_id']}: {$row['full_name']} ({$row['email']}) - {$row['created_at']}\n";
    }
}
?>
