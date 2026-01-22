<?php
/**
 * Clear all emails
 */

require_once(__DIR__ . '/config.php');

header('Content-Type: application/json');

$result = clearAllEmails();

echo json_encode([
    'success' => $result,
    'message' => $result ? 'All emails cleared' : 'Failed to clear emails'
]);
