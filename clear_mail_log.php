<?php
/**
 * Clear Mail Log
 */

require_once(__DIR__ . '/config.php');

header('Content-Type: application/json');

$logFile = MAIL_LOG_FILE;

if (file_exists($logFile)) {
    if (unlink($logFile)) {
        echo json_encode(['success' => true, 'message' => 'Mail log cleared']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Could not clear log file']);
    }
} else {
    echo json_encode(['success' => true, 'message' => 'Log file does not exist']);
}
