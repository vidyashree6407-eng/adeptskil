<?php
/**
 * Email List API - Returns all stored emails as JSON
 */

require_once(__DIR__ . '/config.php');

header('Content-Type: application/json');

$emails = getAllEmails();
$count = count($emails);

echo json_encode([
    'success' => true,
    'count' => $count,
    'emails' => $emails,
    'storageDir' => MAIL_STORAGE_DIR,
    'lastUpdated' => date('Y-m-d H:i:s')
]);
