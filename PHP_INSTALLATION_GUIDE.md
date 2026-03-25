# Windows PHP Installation & Setup Guide

## QUICK START (5 Minutes)

Follow these steps to install PHP and start storing customer data:

---

## Step 1: Download PHP

1. Go to: **https://www.php.net/downloads**
2. Under "Windows builds", click **"Windows downloads"**
3. Download: **VC15 x64 Thread Safe (ZIP)** 
   - File name: `php-8.x.x-Win32-vc15-x64.zip` (8.x.x = current version)

---

## Step 2: Extract PHP

1. Right-click the downloaded ZIP file
2. Select: **"Extract All..."**
3. Extract to: **`C:\php`**
   - ✅ You should have: `C:\php\php.exe`, `C:\php\php-config`, etc.

---

## Step 3: Add PHP to Windows PATH

This allows running `php` from any folder:

### Option A: Automatic (Easiest)
Run PowerShell as Administrator and paste this:
```powershell
$phpPath = 'C:\php'
$currentPath = [System.Environment]::GetEnvironmentVariable('Path', 'Machine')
if ($currentPath -notlike "*$phpPath*") {
    [System.Environment]::SetEnvironmentVariable('Path', "$currentPath;$phpPath", 'Machine')
    Write-Host "✓ PHP added to PATH"
} else {
    Write-Host "✓ PHP already in PATH"
}
```

Then close and reopen PowerShell to apply changes.

### Option B: Manual
1. Press `Win + X` → Settings
2. Search: `"environment variables"`
3. Click: **"Edit the system environment variables"**
4. Click: **"Environment Variables"** button
5. Under "System variables", select **"Path"** → Click **"Edit"**
6. Click **"New"** → Type: `C:\php`
7. Click **OK** on all dialogs

---

## Step 4: Verify PHP Installation

Open PowerShell and run:
```bash
php --version
```

You should see:
```
PHP 8.2.x (cli) (built: ...)
```

If you see "php is not recognized", restart PowerShell or your computer.

---

## Step 5: Stop Python Server & Start PHP Server

```bash
# 1. Go to project folder
cd "c:\Users\MANJUNATH B G\adeptskil"

# 2. Stop any running Python server (Ctrl+C if running)

# 3. Start PHP development server
php -S localhost:8000
```

You should see:
```
[Mon Mar 23 14:30:00 2026] PHP 8.2.x Development Server started at http://localhost:8000
```

---

## Step 6: Test It!

1. Open browser: **http://localhost:8000/enrollment_with_fees.html?course=Account%20Management**
2. Fill form → Select pricing → Choose payment → Complete payment
3. **Success page should appear** (no more 405 errors!)
4. Check browser console (F12) - you should see: **`"✓ Enrollment processed on backend"`**

---

## Step 7: Verify Database Creation

After completing a test payment, the database file `enrollments.db` should be created in your project folder.

To view customer data (Windows):

### Using Online Tool (Easiest):
1. Upload `enrollments.db` to: https://sqliteviewer.app
2. View all customer records instantly

### Using SQLite Command Line:
```bash
# Download: https://www.sqlite.org/download.html
# Extract sqlite3.exe to C:\sqlite
cd "c:\Users\MANJUNATH B G\adeptskil"
sqlite3 enrollments.db
```

Then type:
```sql
SELECT * FROM enrollments;
```

---

## Database Structure

Your customer database has these tables:

### **enrollments** (Main Customer Table)
```
Columns:
- id (auto-increment)
- invoice_id (unique invoice number)
- full_name
- email
- phone
- company
- city
- pincode
- address
- course
- amount
- payment_method (PayPal/Razorpay/Credit Card)
- payment_status (pending/completed)
- payment_id (transaction ID)
- comments
- created_at (timestamp)
- updated_at (timestamp)
```

### **payment_logs** (Payment Transaction History)
```
- id
- invoice_id (links to enrollments)
- payment_method
- status
- response_data (JSON)
- created_at
```

---

## Access Customer Data

### Method 1: View via Browser (Recommended for Testing)
1. Upload `enrollments.db` to: **https://sqliteviewer.app**
2. Browse all records

### Method 2: Create Admin Dashboard
Create a new file: `view_enrollments.php`
```php
<?php
require_once(__DIR__ . '/db_config.php');
$db = getDB();
$stmt = $db->query("SELECT * FROM enrollments ORDER BY created_at DESC");
$enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<table border="1">
  <tr>
    <th>Name</th><th>Email</th><th>Course</th><th>Amount</th><th>Status</th><th>Date</th>
  </tr>
  <?php foreach($enrollments as $e): ?>
  <tr>
    <td><?= htmlspecialchars($e['full_name']) ?></td>
    <td><?= htmlspecialchars($e['email']) ?></td>
    <td><?= htmlspecialchars($e['course']) ?></td>
    <td>$<?= number_format($e['amount'], 2) ?></td>
    <td><?= $e['payment_status'] ?></td>
    <td><?= $e['created_at'] ?></td>
  </tr>
  <?php endforeach; ?>
</table>
```

### Method 3: Export to CSV
```php
// Add to a new file: export_enrollments.php
<?php
require_once(__DIR__ . '/db_config.php');
$db = getDB();
$stmt = $db->query("SELECT * FROM enrollments ORDER BY created_at DESC");

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="enrollments.csv"');

$fp = fopen('php://output', 'w');
fputcsv($fp, ['Name', 'Email', 'Phone', 'Course', 'Amount', 'Status', 'Date']);

foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    fputcsv($fp, [
        $row['full_name'],
        $row['email'],
        $row['phone'],
        $row['course'],
        $row['amount'],
        $row['payment_status'],
        $row['created_at']
    ]);
}
fclose($fp);
```

---

## Troubleshooting

| Problem | Solution |
|----------|----------|
| "php not found" | Run PowerShell as Admin, restart, or add PATH manually |
| "Address already in use :8000" | Kill existing process: `netstat -ano \| findstr :8000` then `taskkill /PID [PID]` |
| "Database locked" | Close any SQLite views and restart PHP server |
| Emails not sending | Configure SMTP in `config.php` or use SendGrid API |
| Success page still blank | Check browser console (F12) for JavaScript errors |

---

## Next Steps

1. ✅ Install PHP
2. ✅ Start PHP server
3. ✅ Test payment flows
4. ✅ View customer database
5. ⏳ (Optional) Add admin dashboard for managing enrollments
6. ⏳ (Optional) Configure email notifications
7. ⏳ (Optional) Set up payment webhooks for PayPal/Razorpay

---

## Files Reference

```
adeptskil/
├── db_config.php                    ← Database connection & helpers
├── process_enrollment_new.php       ← New version (saves to DB)
├── process_enrollment.php           ← Old version (backup)
├── enrollments.db                   ← SQLite database (auto-created)
├── email_log.txt                    ← Email send log
└── [other files...]
```

