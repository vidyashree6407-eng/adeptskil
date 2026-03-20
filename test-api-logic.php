<?php
// Test that the API logic works correctly

$fees_file = __DIR__ . '/course_fees.json';

if (!file_exists($fees_file)) {
    echo "ERROR: course_fees.json not found at: " . $fees_file . "\n";
    exit;
}

$fees_data = json_decode(file_get_contents($fees_file), true);

if (!$fees_data) {
    echo "ERROR: Failed to decode JSON\n";
    exit;
}

$courses = $fees_data['courses'] ?? [];
echo "Total courses in file: " . count($courses) . "\n\n";

// Test exact match for a few course names
$test_names = ['Account Management', 'Absence Management', '10 Soft Skills You Need'];

foreach ($test_names as $test_name) {
    echo "Testing: '$test_name'\n";
    $query = strtolower(trim($test_name));
    $course = null;
    
    foreach ($courses as $c) {
        if (strtolower($c['name']) === $query) {
            $course = $c;
            break;
        }
    }
    
    if ($course) {
        echo "  ✓ FOUND\n";
        echo "  Name: " . $course['name'] . "\n";
        $pricing = $course['pricing'] ?? [];
        echo "  Pricing options: " . implode(', ', array_keys($pricing)) . "\n";
        if (isset($pricing['standard'])) {
            echo "  Standard price: $" . $pricing['standard']['price'] . "\n";
        }
    } else {
        echo "  ✗ NOT FOUND\n";
        // Try case-insensitive partial match
        $partial = array_filter($courses, function($c) use($query) {
            return stripos($c['name'], $query) !== false;
        });
        if ($partial) {
            echo "  Partial matches found: " . count($partial) . "\n";
            foreach (array_slice($partial, 0, 2) as $match) {
                echo "    - " . $match['name'] . "\n";
            }
        }
    }
    echo "\n";
}
