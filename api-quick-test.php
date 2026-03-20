<?php
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'test' => 'API is working',
    'file_exists' => file_exists(__DIR__ . '/course_fees.json'),
    'json_file_size' => filesize(__DIR__ . '/course_fees.json')
]);
