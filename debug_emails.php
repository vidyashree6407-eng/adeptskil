<?php
echo "=== Email Storage Debug ===\n\n";

$emailsDir = __DIR__ . '/emails';
echo "Emails directory: $emailsDir\n";
echo "Directory exists: " . (is_dir($emailsDir) ? 'YES' : 'NO') . "\n\n";

if (is_dir($emailsDir)) {
    $files = array_diff(scandir($emailsDir), ['.', '..']);
    echo "Files in directory: " . count($files) . "\n";
    
    if (count($files) > 0) {
        echo "\nFiles found:\n";
        foreach ($files as $file) {
            $path = $emailsDir . '/' . $file;
            $size = filesize($path);
            echo "  - $file ($size bytes)\n";
            
            if (strpos($file, 'email_') === 0) {
                $data = json_decode(file_get_contents($path), true);
                echo "    To: " . $data['recipient_email'] . "\n";
                echo "    Type: " . $data['recipient_type'] . "\n";
                echo "    Subject: " . $data['subject'] . "\n";
            }
        }
    } else {
        echo "No emails stored yet.\n";
        echo "After completing a payment, check this page again.\n";
    }
} else {
    echo "Creating emails directory...\n";
    @mkdir($emailsDir, 0755, true);
}

echo "\n\n=== How to view all emails ===\n";
echo "Open: http://localhost:5501/emails-dashboard.php\n";
?>
