<?php
// Simple enrollment viewer
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Records - Adeptskil</title>
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
        
        .btn-secondary {
            background: #e2e8f0;
            color: #1e293b;
        }
        
        .btn-secondary:hover {
            background: #cbd5e1;
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
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-graduation-cap"></i> Course Enrollments</h1>
            <p>View all student enrollment records</p>
            
            <div class="stats">
                <div class="stat-card">
                    <div class="number"><?php echo count($enrollments); ?></div>
                    <div class="label">Total Enrollments</div>
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
                            <div class="label">Most Popular Course</div>
                            <div style="font-size: 0.8rem; margin-top: 5px;">' . substr($topCourse, 0, 20) . '...</div>
                        </div>';
                    }
                ?>
            </div>
        </div>
        
        <div class="enrollments-table">
            <?php if (empty($enrollments)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Enrollments Yet</h3>
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
                            <tr class="enrollment-row" onclick="showDetails(<?php echo $index; ?>)">
                                <td class="timestamp"><?php echo date('M d, Y', strtotime($enrollment['timestamp'])); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['fullName']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['email']); ?></td>
                                <td><?php echo htmlspecialchars($enrollment['phone']); ?></td>
                                <td><?php echo htmlspecialchars(substr($enrollment['course'], 0, 30)); ?></td>
                                <td><button class="btn btn-primary" style="padding: 6px 12px; font-size: 0.85rem;" onclick="event.stopPropagation(); showDetails(<?php echo $index; ?>)">View</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Detail Modal -->
    <div class="modal" id="detailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Enrollment Details</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div id="detailContent"></div>
            <div class="action-buttons">
                <button class="btn btn-secondary" onclick="closeModal()">Close</button>
                <button class="btn btn-primary" onclick="downloadEnrollments()">Download All</button>
            </div>
        </div>
    </div>
    
    <script>
        const enrollments = <?php echo json_encode($enrollments); ?>;
        
        function showDetails(index) {
            const enrollment = enrollments[index];
            let html = `
                <div class="detail-row">
                    <div class="detail-label">Date & Time</div>
                    <div class="detail-value">${enrollment.timestamp}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Full Name</div>
                    <div class="detail-value">${enrollment.fullName}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Email</div>
                    <div class="detail-value"><a href="mailto:${enrollment.email}" style="color: #667eea; text-decoration: none;">${enrollment.email}</a></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Phone</div>
                    <div class="detail-value"><a href="tel:${enrollment.phone}" style="color: #667eea; text-decoration: none;">${enrollment.phone}</a></div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Company</div>
                    <div class="detail-value">${enrollment.company || 'Not provided'}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Course</div>
                    <div class="detail-value">${enrollment.course}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-label">Message</div>
                    <div class="detail-value">${enrollment.message || 'No message'}</div>
                </div>
            `;
            
            document.getElementById('detailContent').innerHTML = html;
            document.getElementById('detailModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('detailModal').classList.remove('active');
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
            
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `enrollments-${new Date().toISOString().split('T')[0]}.csv`;
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
