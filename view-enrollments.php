<?php
// Enrollment and Sandbox Test Viewer
require_once(__DIR__ . '/db_config.php');

$enrollmentsFile = 'enrollments.json';
$enrollments = [];

if (file_exists($enrollmentsFile)) {
    $content = file_get_contents($enrollmentsFile);
    if (!empty($content)) {
        $enrollments = json_decode($content, true) ?? [];
    }
}

// Sort by most recent first
usort($enrollments, function($a, $b) {
    return strtotime($b['timestamp'] ?? '0') - strtotime($a['timestamp'] ?? '0');
});

// Get sandbox tests from database
$sandbox_tests = [];
try {
    $db = getDB();
    
    // Add sandbox_tests table if it doesn't exist
    $db->exec("CREATE TABLE IF NOT EXISTS sandbox_tests (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        invoice_id TEXT NOT NULL,
        test_type TEXT NOT NULL,
        full_name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT NOT NULL,
        course TEXT,
        score REAL DEFAULT 0,
        status TEXT DEFAULT 'pending',
        duration INTEGER DEFAULT 0,
        test_data TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        completed_at DATETIME,
        FOREIGN KEY(invoice_id) REFERENCES enrollments(invoice_id)
    )");
    
    $stmt = $db->query("SELECT * FROM sandbox_tests ORDER BY completed_at DESC LIMIT 100");
    $sandbox_tests = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (Exception $e) {
    $sandbox_tests = [];
}

// Get current tab
$tab = $_GET['tab'] ?? 'enrollments';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment & Test Records - Adeptskil</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #2d3748;
            margin-bottom: 10px;
        }
        
        .header p {
            color: #64748b;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: bold;
        }
        
        .stat-card .label {
            font-size: 0.9rem;
            opacity: 0.9;
            margin-top: 5px;
        }
        
        .enrollments-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-header {
            padding: 20px;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th {
            background: #f8fafc;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #1e293b;
            border-bottom: 2px solid #e2e8f0;
        }
        
        td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            color: #475569;
        }
        
        tr:hover {
            background: #f8fafc;
        }
        
        .enrollment-row {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .enrollment-row:hover {
            background: #f1f5f9;
        }
        
        .timestamp {
            font-size: 0.85rem;
            color: #94a3b8;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #64748b;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 20px;
            opacity: 0.3;
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
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .modal-header h2 {
            color: #1e293b;
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #94a3b8;
        }
        
        .detail-row {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 20px;
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #1e293b;
        }
        
        .detail-value {
            color: #475569;
            word-break: break-all;
        }
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            flex: 1;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #1e293b;
        }
        
        .btn-secondary:hover {
            background: #cbd5e1;
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
            background: white;
            padding: 15px;
            border-radius: 12px 12px 0 0;
        }
        
        .tab-btn {
            padding: 10px 20px;
            border: none;
            background: none;
            color: #64748b;
            cursor: pointer;
            font-weight: 600;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }
        
        .tab-btn.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        
        .tab-btn:hover {
            color: #667eea;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .table-wrapper {
            padding: 20px;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .badge-completed {
            background: #dbeafe;
            color: #0c2d6b;
        }
        
        @media (max-width: 768px) {
            table {
                font-size: 0.9rem;
            }
            
            th, td {
                padding: 10px;
            }
            
            .stats {
                grid-template-columns: 1fr;
            }
            
            .tabs {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-graduation-cap"></i> Enrollments & Sandbox Tests</h1>
            <p>View enrollment records and sandbox test completions</p>
            
            <div class="stats">
                <div class="stat-card">
                    <div class="number"><?php echo count($enrollments); ?></div>
                    <div class="label">Total Enrollments</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo count($sandbox_tests); ?></div>
                    <div class="label">Tests Completed</div>
                </div>
                <?php
                    $courses = [];
                    foreach ($enrollments as $e) {
                        $courses[$e['course']] = ($courses[$e['course']] ?? 0) + 1;
                    }
                    arsort($courses);
                    if (!empty($courses)) {
                        $topCourse = array_key_first($courses);
                        echo '<div class="stat-card">
                            <div class="number">' . $courses[$topCourse] . '</div>
                            <div class="label">Most Popular</div>
                            <div style="font-size: 0.8rem; margin-top: 5px;">' . substr($topCourse, 0, 20) . '...</div>
                        </div>';
                    }
                ?>
            </div>
        </div>
        
        <div class="enrollments-table">
            <div class="tabs">
                <button class="tab-btn <?php echo ($tab === 'enrollments') ? 'active' : ''; ?>" onclick="switchTab('enrollments')">
                    <i class="fas fa-list"></i> Enrollments (<?php echo count($enrollments); ?>)
                </button>
                <button class="tab-btn <?php echo ($tab === 'tests') ? 'active' : ''; ?>" onclick="switchTab('tests')">
                    <i class="fas fa-check-circle"></i> Tests (<?php echo count($sandbox_tests); ?>)
                </button>
            </div>
            
            <!-- Enrollments Tab -->
            <div id="enrollments-tab" class="tab-content <?php echo ($tab === 'enrollments') ? 'active' : ''; ?>">
                <div class="table-wrapper">
                    <?php if (empty($enrollments)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No Enrollments</h3>
                            <p>Enrollments will appear here when students submit the enrollment form.</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Course</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($enrollments as $index => $enrollment): ?>
                                    <tr class="enrollment-row" onclick="showDetails(<?php echo $index; ?>, 'enrollment')">
                                        <td class="timestamp"><?php echo date('M d, Y', strtotime($enrollment['timestamp'])); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['fullName']); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['email']); ?></td>
                                        <td><?php echo htmlspecialchars($enrollment['phone']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($enrollment['course'], 0, 30)); ?></td>
                                        <td><button class="btn btn-primary" style="padding: 6px 12px; font-size: 0.85rem;" onclick="event.stopPropagation(); showDetails(<?php echo $index; ?>, 'enrollment')">View</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Tests Tab -->
            <div id="tests-tab" class="tab-content <?php echo ($tab === 'tests') ? 'active' : ''; ?>">
                <div class="table-wrapper">
                    <?php if (empty($sandbox_tests)): ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No Tests Completed</h3>
<p>Sandbox test completions will appear here.</p>
                        </div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Course</th>
                                    <th>Test Type</th>
                                    <th>Score</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sandbox_tests as $index => $test): ?>
                                    <tr class="enrollment-row" onclick="showDetails(<?php echo $index; ?>, 'test')">
                                        <td class="timestamp"><?php echo date('M d, Y', strtotime($test['completed_at'])); ?></td>
                                        <td><?php echo htmlspecialchars($test['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($test['email']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($test['course'], 0, 25)); ?></td>
                                        <td><?php echo htmlspecialchars($test['test_type']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo ($test['score'] >= 70) ? 'success' : 'pending'; ?>">
                                                <?php echo $test['score']; ?>%
                                            </span>
                                        </td>
                                        <td><button class="btn btn-primary" style="padding: 6px 12px; font-size: 0.85rem;" onclick="event.stopPropagation(); showDetails(<?php echo $index; ?>, 'test')">View</button></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Detail Modal -->
    <div class="modal" id="detailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="detailTitle">Details</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div id="detailContent"></div>
            <div class="action-buttons">
                <button class="btn btn-secondary" onclick="closeModal()">Close</button>
                <button class="btn btn-primary" id="sendEmailBtn" onclick="sendEmailToUser()">Send Email</button>
                <button class="btn btn-primary" onclick="downloadAll()">Download All</button>
            </div>
            <div id="emailStatus" style="margin-top: 15px; padding: 12px; border-radius: 8px; display: none; text-align: center; font-weight: 600;"></div>
        </div>
    </div>
    
    <script>
        const enrollments = <?php echo json_encode($enrollments); ?>;
        const sandboxTests = <?php echo json_encode($sandbox_tests); ?>;
        
        let currentDetailData = null;
        let currentDetailType = null;
        
        function switchTab(tab) {
            // Hide all tabs
            document.getElementById('enrollments-tab').classList.remove('active');
            document.getElementById('tests-tab').classList.remove('active');
            
            // Deactivate all buttons
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            
            // Show selected tab
            document.getElementById(tab + '-tab').classList.add('active');
            
            // Activate button
            event.target.closest('.tab-btn').classList.add('active');
            
            // Update URL
            window.history.replaceState({}, '', '?tab=' + tab);
        }
        
        function showDetails(index, type) {
            const data = type === 'enrollment' ? enrollments[index] : sandboxTests[index];
            let html = '';
            
            // Store current data and type for email sending
            currentDetailData = data;
            currentDetailType = type;
            
            // Reset email status
            document.getElementById('emailStatus').style.display = 'none';
            document.getElementById('sendEmailBtn').disabled = false;
            document.getElementById('sendEmailBtn').textContent = 'Send Email';
            
            if (type === 'enrollment') {
                document.getElementById('detailTitle').textContent = 'Enrollment Details';
                html = `
                    <div class="detail-row">
                        <div class="detail-label">Date & Time</div>
                        <div class="detail-value">${data.timestamp}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Full Name</div>
                        <div class="detail-value">${data.fullName}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><a href="mailto:${data.email}" style="color: #667eea; text-decoration: none;">${data.email}</a></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value"><a href="tel:${data.phone}" style="color: #667eea; text-decoration: none;">${data.phone}</a></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Company</div>
                        <div class="detail-value">${data.company || 'Not provided'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Course</div>
                        <div class="detail-value">${data.course}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Message</div>
                        <div class="detail-value">${data.message || 'No message'}</div>
                    </div>
                `;
            } else if (type === 'test') {
                document.getElementById('detailTitle').textContent = 'Test Completion Details';
                const statusBadge = data.score >= 70 ? '<span class="badge badge-success">PASSED</span>' : '<span class="badge badge-pending">PENDING</span>';
                html = `
                    <div class="detail-row">
                        <div class="detail-label">Date & Time</div>
                        <div class="detail-value">${data.completed_at}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Full Name</div>
                        <div class="detail-value">${data.full_name}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Email</div>
                        <div class="detail-value"><a href="mailto:${data.email}" style="color: #667eea; text-decoration: none;">${data.email}</a></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Phone</div>
                        <div class="detail-value"><a href="tel:${data.phone}" style="color: #667eea; text-decoration: none;">${data.phone}</a></div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Invoice ID</div>
                        <div class="detail-value">${data.invoice_id}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Course</div>
                        <div class="detail-value">${data.course || 'Not specified'}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Test Type</div>
                        <div class="detail-value">${data.test_type}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Score</div>
                        <div class="detail-value"><strong>${data.score}%</strong> ${statusBadge}</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Duration</div>
                        <div class="detail-value">${Math.floor(data.duration / 60)} h ${data.duration % 60} m</div>
                    </div>
                    <div class="detail-row">
                        <div class="detail-label">Status</div>
                        <div class="detail-value"><span class="badge badge-completed">${data.status.toUpperCase()}</span></div>
                    </div>
                `;
            }
            
            document.getElementById('detailContent').innerHTML = html;
            document.getElementById('detailModal').classList.add('active');
        }
        
        function sendEmailToUser() {
            if (!currentDetailData || !currentDetailType) {
                alert('No data selected. Please open a record first.');
                return;
            }
            
            const emailAddress = currentDetailType === 'enrollment' ? currentDetailData.email : currentDetailData.email;
            
            if (!emailAddress) {
                alert('No email address found for this record.');
                return;
            }
            
            // Disable button while sending
            const btn = document.getElementById('sendEmailBtn');
            btn.disabled = true;
            btn.textContent = 'Sending...';
            
            // Prepare data for sending
            const payload = {
                type: currentDetailType,
                email: emailAddress,
                data: currentDetailData
            };
            
            // Send email
            fetch('send_user_details_email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show success message
                    const statusDiv = document.getElementById('emailStatus');
                    statusDiv.style.display = 'block';
                    statusDiv.style.background = '#d1fae5';
                    statusDiv.style.color = '#065f46';
                    statusDiv.textContent = '✓ ' + data.message;
                    statusDiv.style.borderLeft = '4px solid #10b981';
                    
                    btn.textContent = 'Email Sent ✓';
                    btn.style.opacity = '0.7';
                    btn.disabled = true;
                } else {
                    throw new Error(data.error || 'Unknown error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const statusDiv = document.getElementById('emailStatus');
                statusDiv.style.display = 'block';
                statusDiv.style.background = '#fee2e2';
                statusDiv.style.color = '#991b1b';
                statusDiv.textContent = '✗ Error: ' + error.message;
                statusDiv.style.borderLeft = '4px solid #ef4444';
                
                btn.disabled = false;
                btn.textContent = 'Send Email';
            });
        }
        
        function closeModal() {
            document.getElementById('detailModal').classList.remove('active');
            currentDetailData = null;
            currentDetailType = null;
        }
        
        function downloadAll() {
            const currentTab = document.querySelector('.tab-btn.active');
            if (currentTab.textContent.includes('Enrollment')) {
                downloadEnrollments();
            } else {
                downloadTests();
            }
        }
        
        function downloadEnrollments() {
            const csv = [
                ['Date', 'Name', 'Email', 'Phone', 'Company', 'Course', 'Message'].join(','),
                ...enrollments.map(e => [
                    e.timestamp,
                    `"${e.fullName}"`,
                    e.email,
                    e.phone,
                    `"${e.company || ''}"`,
                    `"${e.course}"`,
                    `"${(e.message || '').replace(/"/g, '""')}"`
                ].join(','))
            ].join('\n');
            
            downloadCSV(csv, 'enrollments-' + new Date().toISOString().split('T')[0] + '.csv');
        }
        
        function downloadTests() {
            const csv = [
                ['Date', 'Name', 'Email', 'Phone', 'Invoice ID', 'Course', 'Test Type', 'Score', 'Duration (min)', 'Status'].join(','),
                ...sandboxTests.map(t => [
                    t.completed_at,
                    `"${t.full_name}"`,
                    t.email,
                    t.phone,
                    t.invoice_id,
                    `"${t.course || ''}"`,
                    t.test_type,
                    t.score,
                    t.duration,
                    t.status
                ].join(','))
            ].join('\n');
            
            downloadCSV(csv, 'sandbox-tests-' + new Date().toISOString().split('T')[0] + '.csv');
        }
        
        function downloadCSV(csv, filename) {
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.click();
            window.URL.revokeObjectURL(url);
        }
        
        // Close modal when clicking outside
        document.getElementById('detailModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>
