<?php
/**
 * Mail Log Viewer - Backend
 */

require_once(__DIR__ . '/config.php');

header('Content-Type: application/json');

$logFile = MAIL_LOG_FILE;
$content = '';
$count = 0;
$lastUpdated = 'Never';

if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    
    // Count emails (count "[" which starts each log entry)
    $count = substr_count($content, '[');
    
    // Get last modified time
    $lastModified = filemtime($logFile);
    $lastUpdated = date('Y-m-d H:i:s', $lastModified);
}

echo json_encode([
    'success' => true,
    'content' => $content,
    'count' => $count,
    'lastUpdated' => $lastUpdated
]);
