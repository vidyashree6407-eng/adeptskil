<?php
/**
 * Admin Dashboard
 * View enrollments, payments, and manage refunds
 */

require_once(__DIR__ . '/admin_auth.php');
require_once(__DIR__ . '/config.php');

// Check if logged in
if (!isLoggedIn()) {
    header('Location: admin-login.html?error=unauthorized');
    exit;
}

$admin_user = getLoggedInUser();

// Get enrollments
$enrollments = array();
$enrollmentsFile = dirname(__FILE__) . '/enrollments.json';

if (file_exists($enrollmentsFile) && is_readable($enrollmentsFile)) {
    $content = file_get_contents($enrollmentsFile);
    if (!empty($content)) {
        $enrollments = json_decode($content, true) ?: array();
    }
}

// Get refunds
$refunds = array();
$refundsFile = dirname(__FILE__) . '/refunds.json';

if (file_exists($refundsFile) && is_readable($refundsFile)) {
    $content = file_get_contents($refundsFile);
    if (!empty($content)) {
        $refunds = json_decode($content, true) ?: array();
    }
}

// Calculate statistics
$total_enrollments = count($enrollments);
$total_revenue = array_sum(array_column($enrollments, 'price'));
$total_refunded = array_sum(array_column($refunds, 'amount'));
$current_revenue = $total_revenue - $total_refunded;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Adeptskil</title>
    <link rel="icon" type="image/jpeg" href="images/FAVICON.jpg">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #2d3748;
            line-height: 1.6;
        }
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        header .container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header h1 {
            font-size: 1.8rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        .stat-card.revenue {
            border-left-color: #10b981;
        }
        .stat-card.refunds {
            border-left-color: #f59e0b;
        }
        .stat-card h3 {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
        }
        .section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 30px;
        }
        .section h2 {
            font-size: 1.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th {
            background: #f8fafc;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e2e8f0;
            color: #64748b;
            font-size: 0.9rem;
        }
        table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        table tr:hover {
            background: #f8fafc;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-completed {
            background: #dcfce7;
            color: #166534;
        }
        .status-refunded {
            background: #fee2e2;
            color: #991b1b;
        }
        .action-btn {
            background: #667eea;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.85rem;
            cursor: pointer;
            border: none;
            transition: all 0.3s ease;
        }
        .action-btn:hover {
            background: #764ba2;
        }
        .action-btn.danger {
            background: #ef4444;
        }
        .action-btn.danger:hover {
            background: #dc2626;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .modal.show {
            display: flex;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-close {
            float: right;
            cursor: pointer;
            font-size: 1.5rem;
            color: #94a3b8;
        }
        .modal-close:hover {
            color: #2d3748;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2d3748;
        }
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 0.95rem;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1><i class="fas fa-chart-line"></i> Admin Dashboard</h1>
            <div class="user-menu">
                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($admin_user); ?></span>
                <a href="admin_logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><i class="fas fa-users"></i> Total Enrollments</h3>
                <div class="stat-value"><?php echo $total_enrollments; ?></div>
            </div>
            
            <div class="stat-card revenue">
                <h3><i class="fas fa-dollar-sign"></i> Total Revenue</h3>
                <div class="stat-value">$<?php echo number_format($total_revenue, 2); ?></div>
            </div>
            
            <div class="stat-card refunds">
                <h3><i class="fas fa-undo"></i> Total Refunded</h3>
                <div class="stat-value">$<?php echo number_format($total_refunded, 2); ?></div>
            </div>
            
            <div class="stat-card">
                <h3><i class="fas fa-wallet"></i> Net Revenue</h3>
                <div class="stat-value">$<?php echo number_format($current_revenue, 2); ?></div>
            </div>
        </div>

        <!-- Enrollments Section -->
        <div class="section">
            <h2><i class="fas fa-graduation-cap"></i> Recent Enrollments</h2>
            
            <?php if (empty($enrollments)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No enrollments yet</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Course</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Show latest enrollments first
                        $sorted = array_reverse($enrollments);
                        foreach ($sorted as $enrollment): 
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($enrollment['fullName']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['email']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['course']); ?></td>
                                <td>$<?php echo number_format($enrollment['price'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($enrollment['timestamp'])); ?></td>
                                <td><span class="status-badge status-completed"><?php echo htmlspecialchars($enrollment['payment_status']); ?></span></td>
                                <td>
                                    <button class="action-btn danger" onclick="initiateRefund('<?php echo htmlspecialchars(json_encode($enrollment)); ?>')">
                                        <i class="fas fa-undo"></i> Refund
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Refunds Section -->
        <div class="section">
            <h2><i class="fas fa-history"></i> Refund History</h2>
            
            <?php if (empty($refunds)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No refunds processed</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sorted = array_reverse($refunds);
                        foreach ($sorted as $refund): 
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($refund['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($refund['course']); ?></td>
                                <td>$<?php echo number_format($refund['amount'], 2); ?></td>
                                <td><?php echo date('M d, Y', strtotime($refund['refund_date'])); ?></td>
                                <td><?php echo htmlspecialchars($refund['reason']); ?></td>
                                <td><span class="status-badge status-refunded">Refunded</span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Refund Modal -->
    <div id="refundModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeRefundModal()">&times;</span>
            <h2><i class="fas fa-undo"></i> Process Refund</h2>
            
            <form id="refundForm" onsubmit="processRefund(event)">
                <div class="form-group">
                    <label>Student Name</label>
                    <input type="text" id="studentName" readonly>
                </div>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="studentEmail" readonly>
                </div>
                
                <div class="form-group">
                    <label>Course</label>
                    <input type="text" id="courseName" readonly>
                </div>
                
                <div class="form-group">
                    <label>Refund Amount</label>
                    <input type="number" id="refundAmount" readonly>
                </div>
                
                <div class="form-group">
                    <label>Reason for Refund *</label>
                    <textarea id="refundReason" required placeholder="Enter reason for refund..."></textarea>
                </div>
                
                <input type="hidden" id="enrollmentId">
                
                <button type="submit" class="action-btn danger" style="width: 100%; padding: 12px;">
                    <i class="fas fa-check"></i> Confirm Refund
                </button>
            </form>
        </div>
    </div>

    <script>
        let currentEnrollment = null;

        function initiateRefund(enrollmentJson) {
            try {
                currentEnrollment = JSON.parse(enrollmentJson);
                
                document.getElementById('studentName').value = currentEnrollment.fullName;
                document.getElementById('studentEmail').value = currentEnrollment.email;
                document.getElementById('courseName').value = currentEnrollment.course;
                document.getElementById('refundAmount').value = currentEnrollment.price;
                document.getElementById('enrollmentId').value = currentEnrollment.id;
                document.getElementById('refundReason').value = '';
                
                document.getElementById('refundModal').classList.add('show');
            } catch (error) {
                alert('Error loading enrollment data');
                console.error(error);
            }
        }

        function closeRefundModal() {
            document.getElementById('refundModal').classList.remove('show');
        }

        function processRefund(event) {
            event.preventDefault();
            
            const formData = {
                enrollment_id: document.getElementById('enrollmentId').value,
                student_name: document.getElementById('studentName').value,
                student_email: document.getElementById('studentEmail').value,
                course: document.getElementById('courseName').value,
                amount: parseFloat(document.getElementById('refundAmount').value),
                reason: document.getElementById('refundReason').value
            };
            
            fetch('./process_refund.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Refund processed successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                alert('Error processing refund');
                console.error(error);
            });
        }

        // Close modal when clicking outside
        document.getElementById('refundModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRefundModal();
            }
        });
    </script>
</body>
</html>