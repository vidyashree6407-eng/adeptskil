<?php
// course_pricing_manager.php - Pricing and Offers Management System

header('Content-Type: application/json');

// Load pricing data
$pricing_file = 'course_pricing.json';
$pricing_data = json_decode(file_get_contents($pricing_file), true);

// ==========================================
// PRICING STRATEGY
// ==========================================
// Base Formula: $50 + ($99 × Duration in days)
//
// Examples:
// 1-day course: $50 + ($99 × 1) = $149
// 2-day course: $50 + ($99 × 2) = $248
// 3-day course: $50 + ($99 × 3) = $347
// ==========================================

// 4 CUSTOMER OFFERS / DISCOUNTS
$offers = [
    'welcome_discount' => [
        'name' => 'Welcome Offer',
        'description' => '15% off for new customers',
        'discount_percent' => 15,
        'code' => 'WELCOME15',
        'min_price' => 0,
        'applicable_to' => 'all'
    ],
    'bulk_discount' => [
        'name' => 'Team Training Offer',
        'description' => '20% off when enrolling 5+ people',
        'discount_percent' => 20,
        'code' => 'TEAM20',
        'min_participants' => 5,
        'applicable_to' => 'all'
    ],
    'duration_discount' => [
        'name' => 'Extended Training Offer',
        'description' => '25% off for courses 3+ days',
        'discount_percent' => 25,
        'code' => 'EXTENDED25',
        'min_duration_days' => 3,
        'applicable_to' => 'all'
    ],
    'corporate_package' => [
        'name' => 'Corporate Annual Package',
        'description' => '30% off - Unlimited access to all courses for 1 year',
        'discount_percent' => 30,
        'code' => 'CORPORATE30',
        'package_price' => 9999,
        'description_detail' => 'Perfect for organizations with multiple learners',
        'applicable_to' => 'all'
    ]
];

// Get all courses with pricing
function getAllCourses() {
    $pricing_file = 'course_pricing.json';
    $data = json_decode(file_get_contents($pricing_file), true);
    $all_courses = [];
    
    foreach ($data['categories'] as $category => $category_data) {
        foreach ($category_data['courses'] as $course) {
            $all_courses[] = $course;
        }
    }
    
    return $all_courses;
}

// Calculate final price after discount
function calculatePriceAfterDiscount($base_price, $offer) {
    $discount_amount = ($base_price * $offer['discount_percent']) / 100;
    return round($base_price - $discount_amount, 2);
}

// Get course pricing
function getCoursePrice($course_name) {
    $courses = getAllCourses();
    
    foreach ($courses as $course) {
        if (strtolower($course['name']) === strtolower($course_name)) {
            return [
                'course_name' => $course['name'],
                'category' => $course['category'],
                'duration_days' => $course['duration'],
                'base_price' => $course['base_price'],
                'currency' => 'USD'
            ];
        }
    }
    
    return null;
}

// Apply offer to a course
function applyOfferToCourse($course_name, $offer_code) {
    global $offers;
    
    $course = getCoursePrice($course_name);
    if (!$course) return ['error' => 'Course not found'];
    
    // Find matching offer
    $selected_offer = null;
    foreach ($offers as $offer_key => $offer) {
        if ($offer['code'] === $offer_code) {
            $selected_offer = $offer;
            break;
        }
    }
    
    if (!$selected_offer) {
        return ['error' => 'Invalid offer code'];
    }
    
    // Check offer eligibility
    if (isset($selected_offer['min_duration_days']) && 
        $course['duration_days'] < $selected_offer['min_duration_days']) {
        return ['error' => 'This offer is only valid for courses of ' . $selected_offer['min_duration_days'] . '+ days'];
    }
    
    $final_price = calculatePriceAfterDiscount($course['base_price'], $selected_offer);
    $savings = $course['base_price'] - $final_price;
    
    return [
        'course_name' => $course['course_name'],
        'category' => $course['category'],
        'duration_days' => $course['duration_days'],
        'base_price' => $course['base_price'],
        'offer_applied' => $selected_offer['name'],
        'offer_code' => $selected_offer['code'],
        'discount_percent' => $selected_offer['discount_percent'],
        'discount_amount' => round($savings, 2),
        'final_price' => $final_price,
        'currency' => 'USD'
    ];
}

// API Endpoints
$action = $_GET['action'] ?? $_POST['action'] ?? null;

switch($action) {
    case 'get_all_courses':
        echo json_encode([
            'success' => true,
            'total_courses' => count(getAllCourses()),
            'courses' => getAllCourses()
        ]);
        break;
    
    case 'get_course_price':
        $course_name = $_GET['course'] ?? $_POST['course'] ?? null;
        $result = getCoursePrice($course_name);
        echo json_encode($result ? ['success' => true, 'data' => $result] : ['error' => 'Course not found']);
        break;
    
    case 'get_offers':
        global $offers;
        echo json_encode([
            'success' => true,
            'total_offers' => count($offers),
            'offers' => array_map(function($o) {
                return [
                    'name' => $o['name'],
                    'code' => $o['code'],
                    'description' => $o['description'],
                    'discount_percent' => $o['discount_percent']
                ];
            }, $offers)
        ]);
        break;
    
    case 'apply_offer':
        $course_name = $_GET['course'] ?? $_POST['course'] ?? null;
        $offer_code = $_GET['code'] ?? $_POST['code'] ?? null;
        $result = applyOfferToCourse($course_name, $offer_code);
        echo json_encode($result);
        break;
    
    case 'calculate_prices':
        // Calculate prices for all courses
        $courses = getAllCourses();
        $summary = [
            'total_courses' => count($courses),
            'price_range' => [
                'min' => min(array_column($courses, 'base_price')),
                'max' => max(array_column($courses, 'base_price')),
                'average' => round(array_sum(array_column($courses, 'base_price')) / count($courses), 2)
            ],
            'courses' => []
        ];
        
        foreach ($courses as $course) {
            $summary['courses'][] = [
                'name' => $course['name'],
                'category' => $course['category'],
                'duration' => $course['duration'] . ' days',
                'price' => '$' . $course['base_price']
            ];
        }
        
        echo json_encode($summary);
        break;
    
    default:
        echo json_encode([
            'error' => 'Invalid action',
            'available_actions' => [
                'get_all_courses',
                'get_course_price?course=CourseNameHere',
                'get_offers',
                'apply_offer?course=CourseName&code=WELCOME15',
                'calculate_prices'
            ]
        ]);
}
?>
