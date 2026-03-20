<?php
// enrollment_with_pricing.php - Updated enrollment processing with pricing

header('Content-Type: application/json');

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);

$errors = [];
$success = false;

// Validate request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $course_name = $_POST['course_name'] ?? '';
    $participant_names = $_POST['participant_names'] ?? '';
    $company_name = $_POST['company_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $offer_code = $_POST['offer_code'] ?? '';
    
    // Validate required fields
    if (empty($course_name)) $errors[] = 'Course name is required';
    if (empty($participant_names)) $errors[] = 'Participant names are required';
    if (empty($email)) $errors[] = 'Email is required';
    if (empty($phone)) $errors[] = 'Phone is required';
    
    if (empty($errors)) {
        // Load pricing
        $pricing_data = json_decode(file_get_contents('course_pricing.json'), true);
        
        // Find course
        $course_found = false;
        $base_price = 0;
        $course_category = '';
        $course_duration = 0;
        
        foreach ($pricing_data['categories'] as $category => $category_data) {
            foreach ($category_data['courses'] as $course) {
                if (strtolower($course['name']) === strtolower($course_name)) {
                    $course_found = true;
                    $base_price = $course['base_price'];
                    $course_category = $course['category'];
                    $course_duration = $course['duration'];
                    break;
                }
            }
            if ($course_found) break;
        }
        
        if (!$course_found) {
            $errors[] = 'Course not found in database';
        } else {
            // Count participants
            $participants = array_filter(array_map('trim', explode("\n", $participant_names)));
            $participant_count = count($participants);
            
            // Load offers mapping
            $offers = [
                'WELCOME15' => ['percent' => 15, 'min_participants' => 1, 'min_duration' => 0],
                'TEAM20' => ['percent' => 20, 'min_participants' => 5, 'min_duration' => 0],
                'EXTENDED25' => ['percent' => 25, 'min_participants' => 1, 'min_duration' => 3],
                'CORPORATE30' => ['percent' => 30, 'min_participants' => 1, 'min_duration' => 0, 'is_package' => true, 'package_price' => 9999]
            ];
            
            $discount_percent = 0;
            $final_price_per_person = $base_price;
            $total_price = $base_price * $participant_count;
            $discount_reason = '';
            
            // Apply offer if provided
            if (!empty($offer_code) && isset($offers[$offer_code])) {
                $offer = $offers[$offer_code];
                
                // Check eligibility
                $eligible = true;
                if ($participant_count < $offer['min_participants']) {
                    $eligible = false;
                    $discount_reason = "Minimum {$offer['min_participants']} participants required";
                }
                if ($course_duration < $offer['min_duration']) {
                    $eligible = false;
                    $discount_reason = "Minimum {$offer['min_duration']} days course required";
                }
                
                if ($eligible) {
                    $discount_percent = $offer['percent'];
                    
                    if (isset($offer['is_package'])) {
                        $total_price = $offer['package_price'];
                        $final_price_per_person = $offer['package_price'] / $participant_count;
                    } else {
                        $discount_amount = ($base_price * $discount_percent) / 100;
                        $final_price_per_person = $base_price - $discount_amount;
                        $total_price = $final_price_per_person * $participant_count;
                    }
                }
            } else if (!empty($offer_code)) {
                $errors[] = 'Invalid offer code: ' . htmlspecialchars($offer_code);
            }
            
            if (empty($errors)) {
                // Prepare enrollment data
                $enrollment_date = date('Y-m-d H:i:s');
                $enrollment_id = 'ENR-' . date('YmdHis') . '-' . mt_rand(1000, 9999);
                
                $enrollment_data = [
                    'enrollment_id' => $enrollment_id,
                    'enrollment_date' => $enrollment_date,
                    'course_name' => $course_name,
                    'course_category' => $course_category,
                    'course_duration_days' => $course_duration,
                    'participant_count' => $participant_count,
                    'participant_names' => $participants,
                    'company_name' => $company_name,
                    'email' => $email,
                    'phone' => $phone,
                    'pricing' => [
                        'base_price_per_person' => $base_price,
                        'discount_code' => $offer_code ?: 'NONE',
                        'discount_percent' => $discount_percent,
                        'discount_amount_total' => round(($base_price * $participant_count) - $total_price, 2),
                        'final_price_per_person' => round($final_price_per_person, 2),
                        'total_price' => round($total_price, 2),
                        'currency' => 'USD'
                    ],
                    'status' => 'pending_payment'
                ];
                
                // Log enrollment
                $log_dir = 'enrollment_logs';
                if (!is_dir($log_dir)) mkdir($log_dir, 0755, true);
                
                $log_file = $log_dir . '/enrollment_' . $enrollment_id . '.json';
                file_put_contents($log_file, json_encode($enrollment_data, JSON_PRETTY_PRINT));
                
                // Send confirmation email
                $subject = "Enrollment Confirmation - " . $enrollment_id;
                $email_body = "
                <h2>Enrollment Confirmation</h2>
                <p><strong>Enrollment ID:</strong> {$enrollment_id}</p>
                <p><strong>Course:</strong> {$course_name}</p>
                <p><strong>Category:</strong> {$course_category}</p>
                <p><strong>Duration:</strong> {$course_duration} days</p>
                <p><strong>Participants:</strong> {$participant_count}</p>
                <p><strong>Base Price (per person):</strong> \${$base_price}</p>
                " . ($discount_percent > 0 ? "<p><strong>Discount:</strong> {$discount_percent}% (Code: {$offer_code})</p>" : "") . "
                <p><strong>Total Price:</strong> \${$total_price}</p>
                <p>Next Step: Complete payment to activate your enrollment.</p>
                ";
                
                // You would send email here using your email service
                
                $success = true;
            }
        }
    }
}

// Return response
http_response_code(!empty($errors) ? 400 : 200);
echo json_encode([
    'success' => $success,
    'errors' => $errors,
    'data' => $enrollment_data ?? null
]);
?>
