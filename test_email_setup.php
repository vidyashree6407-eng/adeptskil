<?php
echo "=== PHP EMAIL DIAGNOSTIC ===\n";
echo "PHP version: " . phpversion() . "\n";
echo "PHP mail() function exists: " . (function_exists('mail') ? 'YES' : 'NO') . "\n";
echo "Sendmail path: " . ini_get('sendmail_path') . "\n";
echo "SMTP: " . ini_get('SMTP') . "\n";
echo "SMTP port: " . ini_get('smtp_port') . "\n";

// Try to send a test email
echo "\n=== ATTEMPTING TEST EMAIL ===\n";
$test = @mail('test@example.com', 'Test', 'Test body');
echo "mail() returned: " . ($test ? 'true' : 'false') . "\n";

// Try without @ suppression to see errors
echo "\n=== ATTEMPTING TEST EMAIL WITHOUT ERROR SUPPRESSION ===\n";
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo "ERROR: $errstr\n";
});
$test2 = mail('test@example.com', 'Test 2', 'Test body 2');
echo "mail() returned: " . ($test2 ? 'true' : 'false') . "\n";
restore_error_handler();

echo "\n=== END DIAGNOSTIC ===\n";
?>
