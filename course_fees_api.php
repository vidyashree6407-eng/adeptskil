<?php
/**
 * Course Fees API
 * Serves course pricing data from course_fees.json
 * Supports 4 pricing tiers per course: Standard, Early Bird, Virtual Standard, Virtual Early Bird
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
error_log('Course Fees API Request: ' . $_SERVER['REQUEST_METHOD'] . ' - ' . $_SERVER['QUERY_STRING']);

// Load course fees data
$fees_file = __DIR__ . '/course_fees.json';

if (!file_exists($fees_file)) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Course fees database not found'
    ]);
    exit;
}

$fees_data = json_decode(file_get_contents($fees_file), true);

if (!$fees_data) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to load course fees'
    ]);
    exit;
}

$action = $_GET['action'] ?? 'all';
$course_name = $_GET['course'] ?? '';

switch ($action) {
    case 'all':
        // Return all courses with pricing
        echo json_encode([
            'success' => true,
            'data' => $fees_data['courses'],
            'total' => count($fees_data['courses'])
        ]);
        break;

    case 'search':
        // Search for a specific course by name
        $query = strtolower(trim($course_name));
        $results = array_filter($fees_data['courses'], function($course) use ($query) {
            return strpos(strtolower($course['name']), $query) !== false;
        });

        echo json_encode([
            'success' => true,
            'data' => array_values($results),
            'total' => count($results)
        ]);
        break;

    case 'get_course':
        // Get pricing for a specific course
        if (!$course_name) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Course name required'
            ]);
            exit;
        }

        $query = strtolower(trim($course_name));
        $course = null;

        foreach ($fees_data['courses'] as $c) {
            if (strtolower($c['name']) === $query) {
                $course = $c;
                break;
            }
        }

        if (!$course) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Course not found: ' . htmlspecialchars($course_name)
            ]);
            exit;
        }

        echo json_encode([
            'success' => true,
            'data' => $course
        ]);
        break;

    case 'get_pricing_options':
        // Get all available pricing options
        echo json_encode([
            'success' => true,
            'options' => [
                [
                    'id' => 'standard',
                    'name' => 'Standard Fee',
                    'description' => 'IN-PERSON or LIVE-ONLINE training',
                    'badge' => 'MOST POPULAR',
                    'color' => '#667eea'
                ],
                [
                    'id' => 'early_bird',
                    'name' => 'Early Bird Fee',
                    'description' => 'Early enrollment discount - Limited time!',
                    'badge' => 'SAVE 22%',
                    'color' => '#10b981'
                ],
                [
                    'id' => 'virtual_standard',
                    'name' => 'Live Virtual Standard',
                    'description' => 'Virtual instructor-led training',
                    'badge' => 'ONLINE',
                    'color' => '#764ba2'
                ],
                [
                    'id' => 'virtual_early_bird',
                    'name' => 'Live Virtual Early Bird',
                    'description' => 'Virtual early enrollment - Save 36%',
                    'badge' => 'SAVE 36%',
                    'color' => '#f59e0b'
                ]
            ]
        ]);
        break;

    case 'calculate_discount':
        // Calculate savings for a course
        if (!$course_name) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Course name required'
            ]);
            exit;
        }

        $query = strtolower(trim($course_name));
        $course = null;

        foreach ($fees_data['courses'] as $c) {
            if (strtolower($c['name']) === $query) {
                $course = $c;
                break;
            }
        }

        if (!$course) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Course not found'
            ]);
            exit;
        }

        $pricing = $course['pricing'];
        $standard_price = $pricing['standard']['price'];

        $discounts = [
            'standard' => 0,
            'early_bird' => round((($standard_price - $pricing['early_bird']['price']) / $standard_price) * 100),
            'virtual_standard' => round((($standard_price - $pricing['virtual_standard']['price']) / $standard_price) * 100),
            'virtual_early_bird' => round((($standard_price - $pricing['virtual_early_bird']['price']) / $standard_price) * 100)
        ];

        echo json_encode([
            'success' => true,
            'data' => [
                'course_name' => $course['name'],
                'pricing' => $pricing,
                'discounts' => $discounts,
                'reference_price' => $standard_price
            ]
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action. Available actions: all, search, get_course, get_pricing_options, calculate_discount'
        ]);
}
?>
