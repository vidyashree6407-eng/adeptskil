<?php
/**
 * Get Enrollments API
 * Fetches all customer enrollments from the database
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once(__DIR__ . '/db_config.php');

try {
    $db = getDB();
    
    // Check if table exists
    $tableCheck = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='enrollments'")->fetch();
    
    if (!$tableCheck) {
        echo json_encode(['success' => false, 'message' => 'Database not created yet']);
        exit;
    }
    
    // Get all enrollments ordered by newest first
    $stmt = $db->query("
        SELECT * FROM enrollments 
        ORDER BY created_at DESC 
        LIMIT 1000
    ");
    
    $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'enrollments' => $enrollments,
        'count' => count($enrollments)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
