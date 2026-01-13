<?php
header('Content-Type: application/json');
echo json_encode(['status' => 'PHP is working', 'timestamp' => date('Y-m-d H:i:s')]);
