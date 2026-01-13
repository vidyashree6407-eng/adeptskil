<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Server Diagnostic</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .section { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; }
        .ok { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .test { margin: 10px 0; padding: 10px; background: #f9f9f9; border-left: 3px solid #667eea; }
        h2 { color: #667eea; }
        code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🔍 Server Diagnostic Report</h1>
    
    <div class="section">
        <h2>PHP Environment</h2>
        <div class="test">
            <strong>PHP Version:</strong> <?php echo phpversion(); ?>
        </div>
        <div class="test">
            <strong>Server Software:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?>
        </div>
        <div class="test">
            <strong>Current Directory:</strong> <?php echo __DIR__; ?>
        </div>
    </div>

    <div class="section">
        <h2>File System</h2>
        <div class="test">
            <strong>Logs Directory Writable:</strong>
            <?php
            $testFile = __DIR__ . '/diagnostic_test.txt';
            if (@file_put_contents($testFile, 'test')) {
                @unlink($testFile);
                echo '<span class="ok">✓ YES</span>';
            } else {
                echo '<span class="error">✗ NO - PROBLEM!</span>';
            }
            ?>
        </div>
        <div class="test">
            <strong>enrollments.log exists:</strong>
            <?php echo file_exists(__DIR__ . '/enrollments.log') ? '✓ Yes' : '✗ No'; ?>
        </div>
        <div class="test">
            <strong>chatbot_errors.log writable:</strong>
            <?php
            $logFile = __DIR__ . '/chatbot_errors.log';
            if (@file_put_contents($logFile, "[Test] " . date('Y-m-d H:i:s') . "\n", FILE_APPEND)) {
                echo '<span class="ok">✓ YES</span>';
            } else {
                echo '<span class="error">✗ NO - PROBLEM!</span>';
            }
            ?>
        </div>
    </div>

    <div class="section">
        <h2>Email System</h2>
        <div class="test">
            <strong>mail() Function Available:</strong>
            <?php echo function_exists('mail') ? '<span class="ok">✓ YES</span>' : '<span class="error">✗ NO</span>'; ?>
        </div>
        <div class="test">
            <strong>Sendmail Path:</strong>
            <?php echo ini_get('sendmail_path') ?: 'Not configured'; ?>
        </div>
    </div>

    <div class="section">
        <h2>JSON Processing</h2>
        <div class="test">
            <?php
            $testData = ['success' => true, 'message' => 'Test'];
            $json = json_encode($testData);
            $decoded = json_decode($json, true);
            if ($decoded === $testData) {
                echo '<span class="ok">✓ JSON encoding/decoding works perfectly</span>';
            } else {
                echo '<span class="error">✗ JSON processing failed</span>';
            }
            ?>
        </div>
    </div>

    <div class="section">
        <h2>Test API Request</h2>
        <div class="test">
            <button onclick="testAPI()">Click to test process_chatbot.php</button>
            <pre id="result" style="background: #f0f0f0; padding: 10px; border-radius: 3px; margin-top: 10px; display:none;"></pre>
        </div>
    </div>

    <script>
        function testAPI() {
            const data = {
                fullName: 'Test User',
                email: 'test@example.com',
                phone: '1234567890',
                course: 'Test Course'
            };
            
            console.log('Sending test request...');
            
            fetch('process_chatbot.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => {
                console.log('Status:', response.status);
                return response.text();
            })
            .then(text => {
                console.log('Response:', text);
                document.getElementById('result').style.display = 'block';
                document.getElementById('result').textContent = text;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('result').style.display = 'block';
                document.getElementById('result').textContent = 'Error: ' + error.message;
            });
        }
    </script>

</body>
</html>
