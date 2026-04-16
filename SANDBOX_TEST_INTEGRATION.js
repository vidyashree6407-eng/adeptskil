/**
 * Sandbox Test Completion Integration Guide
 * 
 * HOW TO USE IN YOUR APPLICATION
 * ================================
 */

// EXAMPLE 1: Basic JavaScript Function Call
// ==========================================
function submitTestCompletion(testData) {
    const payload = {
        invoice_id: testData.invoiceId,
        email: testData.email,
        full_name: testData.fullName,
        phone: testData.phone,
        course: testData.course,
        test_type: testData.testType,  // e.g., 'assessment', 'quiz', etc.
        score: parseFloat(testData.score),  // 0-100
        duration: parseInt(testData.duration),  // in minutes
        status: 'completed'
    };
    
    fetch('process_sandbox_test.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Test recorded successfully!', data);
            alert('Test completion confirmed! Confirmation email sent.');
            // Redirect to thank you page
            window.location.href = 'thank-you.html?test=true';
        } else {
            console.error('Error:', data.message);
            alert('Error: ' + data.message);
        }
    })
    .catch(error => console.error('Network error:', error));
}

// EXAMPLE 2: From an Exam/Test Form Submission
// ==============================================
async function handleTestSubmission(event) {
    event.preventDefault();
    
    const form = event.target;
    const data = {
        invoice_id: document.getElementById('invoice_id').value,
        email: document.getElementById('email').value,
        full_name: document.getElementById('full_name').value,
        phone: document.getElementById('phone').value,
        course: document.getElementById('course').value,
        test_type: 'assessment',
        score: calculateTestScore(),  // Your scoring function
        duration: getMinsElapsed(),    // Your timer function
        status: 'completed'
    };
    
    try {
        const response = await fetch('process_sandbox_test.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show success message
            showSuccessModal('Test submitted successfully!');
            
            // Optional: Store test_id for future reference
            localStorage.setItem('lastTestId', result.test_id);
            
            // Redirect after delay
            setTimeout(() => {
                window.location.href = 'thank-you.html';
            }, 2000);
        } else {
            showErrorModal(result.message);
        }
    } catch (error) {
        showErrorModal('An error occurred: ' + error.message);
    }
}

// EXAMPLE 3: Integration with PayPal Payment Receipt
// ====================================================
function completeTestAfterPayment(paypalOrderId, enrollmentData) {
    const testData = {
        invoice_id: paypalOrderId,  // or your invoice ID
        email: enrollmentData.email,
        full_name: enrollmentData.fullName,
        phone: enrollmentData.phone,
        course: enrollmentData.course,
        test_type: 'learning_module',
        score: 100,  // Auto pass after payment
        duration: 0,
        status: 'completed'
    };
    
    fetch('process_sandbox_test.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(testData)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            console.log('Course access granted!');
        }
    });
}

// EXAMPLE 4: From Learning Module Completion
// =============================================
class LearningModule {
    constructor(moduleId, courseId) {
        this.moduleId = moduleId;
        this.courseId = courseId;
        this.startTime = Date.now();
    }
    
    async completeModule(studentName, studentEmail, studentPhone) {
        const endTime = Date.now();
        const durationMins = Math.round((endTime - this.startTime) / 60000);
        
        const payload = {
            invoice_id: 'MOD-' + this.moduleId + '-' + Date.now(),
            email: studentEmail,
            full_name: studentName,
            phone: studentPhone,
            course: this.courseId,
            test_type: 'learning_module',
            score: 100,  // Module completed
            duration: durationMins,
            status: 'completed'
        };
        
        return fetch('process_sandbox_test.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
    }
}

// USAGE: 
// const module = new LearningModule('mod-001', 'Leadership Development');
// module.completeModule('John Doe', 'john@example.com', '+1234567890');

// EXAMPLE 5: Scheduled Test Completion
// ======================================
function scheduleTestCompletion(testDetails, delayMs = 0) {
    setTimeout(() => {
        submitTestCompletion(testDetails);
    }, delayMs);
}

// USAGE:
// scheduleTestCompletion({
//     invoiceId: 'INV-001',
//     email: 'user@example.com',
//     fullName: 'John Doe',
//     phone: '+1234567890',
//     course: 'Course Name',
//     testType: 'quiz',
//     score: 85,
//     duration: 30
// }, 3000);

// EXAMPLE 6: With Loading/UI Feedback
// =====================================
async function submitTestWithFeedback(testData) {
    const button = event.target;
    const originalText = button.textContent;
    
    try {
        // Show loading state
        button.disabled = true;
        button.textContent = 'Processing...';
        
        const response = await fetch('process_sandbox_test.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(testData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            button.textContent = '✓ Success!';
            button.style.background = '#10b981';
            
            setTimeout(() => {
                window.location.href = 'thank-you.html';
            }, 2000);
        } else {
            button.textContent = '✗ Error';
            button.style.background = '#ef4444';
            alert('Error: ' + result.message);
            
            setTimeout(() => {
                button.disabled = false;
                button.textContent = originalText;
                button.style.background = '';
            }, 2000);
        }
    } catch (error) {
        button.disabled = false;
        button.textContent = originalText;
        alert('Network error: ' + error.message);
    }
}

// REQUIRED FIELDS
// ===============
/**
 * invoice_id (Required): String
 *   - Unique identifier for the test/enrollment
 *   - Example: "INV-2024001", "MOD-123", "TEST-456"
 *   
 * email (Required): String
 *   - Customer's email address
 *   - Must be valid email format
 *   
 * full_name (Required): String
 *   - Customer's full name
 *   
 * phone (Required): String
 *   - Customer's phone number
 *   
 * course (Optional): String
 *   - Name of the course
 *   
 * test_type (Optional): String
 *   - Default: 'learning_module'
 *   - Options: 'learning_module', 'assessment', 'quiz', 'practical', 'certification'
 *   
 * score (Optional): Number 0-100
 *   - Default: 100
 *   - Test score in percentage
 *   
 * duration (Optional): Number (minutes)
 *   - Default: 0
 *   - How long the test took
 *   
 * status (Optional): String
 *   - Default: 'completed'
 *   - Options: 'completed', 'pending', 'in_progress'
 */

// ERROR HANDLING
// ==============
function handleTestError(error) {
    if (error.message.includes('Missing required fields')) {
        alert('Please fill in all required fields');
    } else if (error.message.includes('Invalid email')) {
        alert('Please enter a valid email address');
    } else if (error.code === 'NETWORK_ERROR') {
        alert('Network error. Please check your connection.');
    } else {
        alert('An error occurred: ' + error.message);
    }
}

// VIEW RESULTS
// ============
function viewTestResults() {
    // Open the enrollments viewer with tests tab
    window.open('view-enrollments.php?tab=tests', '_blank');
}

// TESTING
// =======
// 1. Use test-sandbox.html in browser for manual testing
// 2. Check process_sandbox_test.php response in browser console
// 3. View results in view-enrollments.php
// 4. Check email logs in /emails directory
