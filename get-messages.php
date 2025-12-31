<?php
/**
 * Messages API
 * Provides JSON data for admin dashboard
 * Reads from contact_submissions.log and messages_backup.txt
 */

header('Content-Type: application/json');

// Get all messages from log files
function getMessages() {
    $messages = [];
    $log_file = __DIR__ . '/contact_submissions.log';
    $backup_file = __DIR__ . '/messages_backup.txt';
    
    // Read from backup file for detailed messages
    if (file_exists($backup_file)) {
        $content = file_get_contents($backup_file);
        
        // Parse the detailed backup file
        $entries = explode("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━", $content);
        
        foreach ($entries as $entry) {
            $entry = trim($entry);
            if (empty($entry)) continue;
            
            $msg = parseMessageEntry($entry);
            if ($msg) {
                $messages[] = $msg;
            }
        }
    }
    
    // Sort by date (newest first)
    usort($messages, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    return $messages;
}

function parseMessageEntry($entry) {
    $msg = [];
    $lines = explode("\n", $entry);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        if (preg_match('/\[(.*?)\]/', $line, $matches)) {
            $msg['timestamp'] = $matches[1];
        }
        if (preg_match('/^ID:\s*(.+)/', $line, $matches)) {
            $msg['message_id'] = trim($matches[1]);
        }
        if (preg_match('/^Name:\s*(.+)/', $line, $matches)) {
            $msg['name'] = trim($matches[1]);
        }
        if (preg_match('/^Email:\s*(.+)/', $line, $matches)) {
            $msg['email'] = trim($matches[1]);
        }
        if (preg_match('/^Phone:\s*(.+)/', $line, $matches)) {
            $msg['phone'] = trim($matches[1]);
        }
        if (preg_match('/^Subject:\s*(.+)/', $line, $matches)) {
            $msg['subject'] = trim($matches[1]);
        }
        if (preg_match('/^Message:/', $line)) {
            // Get message content (everything after "Message:" line)
            $idx = array_search($line, $lines);
            $message_lines = array_slice($lines, $idx + 1);
            $msg['message'] = implode("\n", $message_lines);
            break;
        }
    }
    
    if (!empty($msg['email'])) {
        $msg['time_ago'] = getTimeAgo($msg['timestamp'] ?? date('Y-m-d H:i:s'));
        $msg['country'] = $msg['country'] ?? 'Not specified';
        return $msg;
    }
    
    return null;
}

function getTimeAgo($timestamp) {
    try {
        $date = new DateTime($timestamp);
        $now = new DateTime();
        $diff = $now->diff($date);
        
        if ($diff->days > 0) {
            return $diff->days . ' day' . ($diff->days > 1 ? 's' : '') . ' ago';
        }
        if ($diff->h > 0) {
            return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
        }
        if ($diff->i > 0) {
            return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
        }
        return 'Just now';
    } catch (Exception $e) {
        return 'Recently';
    }
}

$messages = getMessages();

// Calculate stats
$today_messages = 0;
foreach ($messages as $msg) {
    if (isset($msg['timestamp']) && date('Y-m-d', strtotime($msg['timestamp'])) === date('Y-m-d')) {
        $today_messages++;
    }
}

$unique_subjects = count(array_unique(array_column($messages, 'subject')));

echo json_encode([
    'messages' => array_slice($messages, 0, 50), // Return latest 50
    'total' => count($messages),
    'today' => $today_messages,
    'subjects' => $unique_subjects
]);
