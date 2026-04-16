<?php
/**
 * SMTP Connection Debug Test
 */

echo "=== GoDaddy SMTP Connection Debug ===\n\n";

$host = 'smtpout.secureserver.net';
$port = 465;
$username = 'info@adeptskil.com';
$password = 'zS2nRu.7Mfk8*Xk';

echo "1. Testing Socket Connection...\n";
$socket = @fsockopen($host, $port, $errno, $errstr, 30);

if (!$socket) {
    echo "   FAILED: Cannot connect to $host:$port\n";
    echo "   Error Code: $errno\n";
    echo "   Error: $errstr\n\n";
} else {
    echo "   SUCCESS: Connected to $host:$port\n\n";
    
    // Read server response
    echo "2. Reading Server Banner...\n";
    $response = fgets($socket, 512);
    var_dump($response);
    
    echo "\n3. Sending EHLO...\n";
    fputs($socket, "EHLO localhost\r\n");
    $response = fgets($socket, 512);
    var_dump($response);
    
    echo "\n4. Checking Server Capabilities...\n";
    // Read all capability lines
    while (true) {
        $line = fgets($socket, 512);
        var_dump($line);
        if (substr($line, 3, 1) == ' ') break;
    }
    
    echo "\n5. Closing Connection...\n";
    fputs($socket, "QUIT\r\n");
    fclose($socket);
    echo "   Connection closed.\n";
}

?>
