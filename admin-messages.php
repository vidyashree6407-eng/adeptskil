<?php
/**
 * Admin Messages Dashboard
 * Real-time view of all customer messages and inquiries
 * SECURITY: Requires password protection in production
 */

session_start();

// Simple password protection (change this in production!)
$admin_password = 'adeptskil123'; // WARNING: Change this to a secure password!

// Check if password is provided and correct
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $auth_error = 'Incorrect password';
    }
}

// If not logged in, show login form
if (!isset($_SESSION['admin_logged_in'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - Adeptskil</title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .login-container {
                background: white;
                padding: 2rem;
                border-radius: 10px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.2);
                max-width: 350px;
                width: 100%;
            }
            h1 {
                font-size: 1.5rem;
                margin-bottom: 0.5rem;
                color: #1f2937;
            }
            .subtitle {
                color: #6b7280;
                font-size: 0.9rem;
                margin-bottom: 1.5rem;
            }
            input[type="password"] {
                width: 100%;
                padding: 0.75rem;
                border: 2px solid #e5e7eb;
                border-radius: 6px;
                font-size: 1rem;
                margin-bottom: 1rem;
            }
            input[type="password"]:focus {
                outline: none;
                border-color: #667eea;
            }
            button {
                width: 100%;
                padding: 0.75rem;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 6px;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                transition: transform 0.2s;
            }
            button:hover { transform: translateY(-2px); }
            .error {
                background: #fee2e2;
                color: #991b1b;
                padding: 0.75rem;
                border-radius: 6px;
                margin-bottom: 1rem;
                border-left: 4px solid #ef4444;
            }
        </style>
    </head>
    <body>
        <div class="login-container">
            <h1>🔐 Admin Dashboard</h1>
            <p class="subtitle">Enter password to access messages</p>
            <?php if (isset($auth_error)): ?>
                <div class="error"><?php echo htmlspecialchars($auth_error); ?></div>
            <?php endif; ?>
            <form method="post">
                <input type="password" name="password" placeholder="Enter admin password" required autofocus>
                <button type="submit">Login</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// User is logged in - show dashboard
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Messages Dashboard - Adeptskil</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            padding: 20px;
        }
        .dashboard {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        h1 {
            font-size: 1.8rem;
            color: #1f2937;
        }
        .logout-btn {
            background: #ef4444;
            color: white;
            padding: 0.6rem 1.2rem;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border-left: 4px solid #667eea;
        }
        .stat-card h3 {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #1f2937;
        }
        .messages-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .messages-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .messages-header h2 {
            font-size: 1.3rem;
        }
        .refresh-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 0.6rem 1.2rem;
            border: 1px solid white;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
        }
        .refresh-btn:hover {
            background: rgba(255,255,255,0.3);
        }
        .message-item {
            padding: 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            transition: background 0.2s;
        }
        .message-item:hover {
            background: #f8fafc;
        }
        .message-item:last-child {
            border-bottom: none;
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 0.75rem;
        }
        .message-from {
            font-weight: 600;
            color: #1f2937;
            font-size: 1rem;
        }
        .message-subject {
            background: #dbeafe;
            color: #1e40af;
            padding: 0.25rem 0.75rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            margin-left: 0.5rem;
        }
        .message-meta {
            font-size: 0.85rem;
            color: #6b7280;
            margin-bottom: 1rem;
        }
        .message-content {
            color: #374151;
            line-height: 1.6;
            margin-bottom: 0.75rem;
            padding: 0.75rem;
            background: #f9fafb;
            border-radius: 6px;
            border-left: 3px solid #667eea;
        }
        .message-footer {
            display: flex;
            gap: 1rem;
            font-size: 0.85rem;
            flex-wrap: wrap;
        }
        .message-footer span {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            color: #6b7280;
        }
        .copy-btn {
            background: #e5e7eb;
            color: #374151;
            padding: 0.4rem 0.8rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.8rem;
        }
        .copy-btn:hover {
            background: #d1d5db;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }
        .empty-state i {
            font-size: 3rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }
        .filter-bar {
            padding: 1.5rem;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .filter-bar select {
            padding: 0.5rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: white;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-envelope"></i> Message Dashboard</h1>
            <a href="?logout=1" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>

        <!-- Stats -->
        <div class="stats">
            <div class="stat-card">
                <h3>📊 Total Messages</h3>
                <div class="stat-number" id="totalMessages">-</div>
            </div>
            <div class="stat-card">
                <h3>📧 Today's Messages</h3>
                <div class="stat-number" id="todayMessages">-</div>
            </div>
            <div class="stat-card">
                <h3>📍 Subjects</h3>
                <div class="stat-number" id="uniqueSubjects">-</div>
            </div>
        </div>

        <!-- Messages -->
        <div class="messages-container">
            <div class="messages-header">
                <h2><i class="fas fa-inbox"></i> Recent Messages</h2>
                <button class="refresh-btn" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
            </div>

            <div class="filter-bar">
                <select id="subjectFilter" onchange="filterMessages()">
                    <option value="">All Subjects</option>
                    <option value="Course Inquiry">Course Inquiry</option>
                    <option value="Corporate Training">Corporate Training</option>
                    <option value="Partnership">Partnership Opportunity</option>
                    <option value="Technical Support">Technical Support</option>
                    <option value="General Inquiry">General Inquiry</option>
                    <option value="Enrollment Help">Enrollment Help</option>
                    <option value="Feedback">Feedback</option>
                </select>
            </div>

            <div id="messagesContent">
                <div class="empty-state">
                    <i class="fas fa-spinner"></i>
                    <p>Loading messages...</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let allMessages = [];

        function loadMessages() {
            fetch('./get-messages.php')
                .then(r => r.json())
                .then(data => {
                    allMessages = data.messages || [];
                    updateStats(data);
                    displayMessages(allMessages);
                })
                .catch(e => {
                    document.getElementById('messagesContent').innerHTML = `
                        <div class="empty-state">
                            <i class="fas fa-exclamation-circle"></i>
                            <p>Error loading messages</p>
                        </div>
                    `;
                });
        }

        function updateStats(data) {
            document.getElementById('totalMessages').textContent = data.total || 0;
            document.getElementById('todayMessages').textContent = data.today || 0;
            document.getElementById('uniqueSubjects').textContent = data.subjects || 0;
        }

        function displayMessages(messages) {
            if (!messages.length) {
                document.getElementById('messagesContent').innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No messages yet</p>
                    </div>
                `;
                return;
            }

            const html = messages.map(msg => `
                <div class="message-item">
                    <div class="message-header">
                        <span>
                            <span class="message-from">${escapeHtml(msg.name)}</span>
                            <span class="message-subject">${escapeHtml(msg.subject)}</span>
                        </span>
                    </div>
                    <div class="message-meta">
                        <i class="fas fa-clock"></i> ${msg.time_ago}
                    </div>
                    <div class="message-content">
                        ${escapeHtml(msg.message)}
                    </div>
                    <div class="message-footer">
                        <span><i class="fas fa-envelope"></i> ${escapeHtml(msg.email)}</span>
                        ${msg.phone ? `<span><i class="fas fa-phone"></i> ${escapeHtml(msg.phone)}</span>` : ''}
                        <span><i class="fas fa-location-dot"></i> ${escapeHtml(msg.country || 'Unknown')}</span>
                        <span><i class="fas fa-tag"></i> ${escapeHtml(msg.message_id)}</span>
                        <button class="copy-btn" onclick="copyToClipboard('${escapeHtml(msg.email)}')">
                            <i class="fas fa-copy"></i> Copy Email
                        </button>
                    </div>
                </div>
            `).join('');

            document.getElementById('messagesContent').innerHTML = html;
        }

        function filterMessages() {
            const subject = document.getElementById('subjectFilter').value;
            const filtered = subject ? allMessages.filter(m => m.subject === subject) : allMessages;
            displayMessages(filtered);
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Email copied to clipboard!');
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Load messages on page load
        loadMessages();

        // Auto-refresh every 10 seconds
        setInterval(loadMessages, 10000);

        // Handle logout
        if (new URLSearchParams(window.location.search).has('logout')) {
            window.location.href = 'admin-messages.php';
        }
    </script>
</body>
</html>
