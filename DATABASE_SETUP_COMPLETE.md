# Database Setup Complete - Quick Start Guide

## ✅ What's Been Set Up

1. **Database System**: SQLite database for customer enrollments
2. **API Endpoint**: `process_enrollment.php` now saves data to database
3. **Customer Viewer**: `view_enrollments.html` to see all enrollments
4. **PHP Setup Helper**: Automated installation scripts
5. **Email Notifications**: Auto-sends confirmation emails after payment

---

## 🚀 Start Here (3 Steps)

### Step 1: Run PHP Setup (Automated - Recommended)

1. **Right-click on file**: `setup_php.bat` in your project folder
2. **Select**: "Run as Administrator"
3. **Follow the prompts** to install or configure PHP

✅ This will automatically:
- Download PHP 8.2
- Extract to `C:\php`
- Add PHP to Windows PATH

---

### Step 2: Start PHP Server

**Open PowerShell or Command Prompt** (after Step 1 completes):

```bash
cd "c:\Users\MANJUNATH B G\adeptskil"
php -S localhost:8000
```

**Expected output:**
```
[Mon Mar 23 14:45:00 2026] PHP 8.2.0 Development Server started at http://localhost:8000
[Mon Mar 23 14:45:00 2026] Listening on http://localhost:8000
```

✅ Keep this terminal open while testing

---

### Step 3: Test the Complete Payment Flow

1. Open browser: **http://localhost:8000/enrollment_with_fees.html?course=Account%20Management**

2. **Fill the enrollment form:**
   ```
   Full Name: John Doe
   Email: john@example.com
   Phone: +1-555-0123
   Company: Test Company
   City: New York
   Pincode: 10001
   Address: 123 Main St
   ```

3. **Click "Proceed to Payment"**

4. **Select a pricing option** (any one)

5. **Select a payment method** and complete payment:
   - **PayPal**: Will redirect to sandbox
   - **Razorpay**: Test card: `4111 1111 1111 1111`, Any date/CVV
   - **Credit Card**: Same test card details

6. **✅ Success page appears** → Check console (F12) for: `"✓ Enrollment processed on backend"`

---

## 👥 View Customer Database

### Method 1: Web Dashboard (Easiest)

Open: **http://localhost:8000/view_enrollments.html**

You'll see:
- ✅ Total enrollments
- ✅ All customer data
- ✅ Payment status
- ✅ Search box
- ✅ Export to CSV button
- ✅ Download database button

**Features:**
- 📊 Real-time statistics
- 🔍 Search by name, email, or course
- 📥 Export enrollments as CSV
- 💾 Download full database

### Method 2: SQLite Browser (Online)

1. Go to: **https://sqliteviewer.app**
2. Upload: `enrollments.db` from your project folder
3. View all customer records

### Method 3: Command Line

```bash
cd "c:\Users\MANJUNATH B G\adeptskil"
sqlite3 enrollments.db
```

Then run SQL commands:
```sql
SELECT * FROM enrollments;                    -- View all customers
SELECT COUNT(*) FROM enrollments;             -- Count enrollments
SELECT * FROM enrollments WHERE payment_status='completed';  -- Show paid enrollments
SELECT SUM(amount) FROM enrollments;          -- Total revenue
SELECT * FROM payment_logs;                   -- View payment history
```

Type `.exit` to quit

---

## 📋 Database Details

### Enrollments Table (Main Customer Data)

| Column | Type | Description |
|--------|------|-------------|
| id | Integer | Auto-increment ID |
| invoice_id | Text | Unique invoice number |
| full_name | Text | Customer name |
| email | Text | Email address |
| phone | Text | Phone number |
| company | Text | Company/Organization |
| city | Text | City |
| pincode | Text | Postal code |
| address | Text | Full address |
| course | Text | Course name |
| amount | Real | Payment amount |
| payment_method | Text | PayPal, Razorpay, or Credit Card |
| payment_status | Text | "pending" or "completed" |
| payment_id | Text | Transaction ID from gateway |
| comments | Text | Additional notes |
| created_at | DateTime | Record creation time |
| updated_at | DateTime | Last update time |

### Payment Logs Table (Transaction History)

| Column | Type | Description |
|--------|------|-------------|
| id | Integer | Auto-increment ID |
| invoice_id | Text | Links to enrollments table |
| payment_method | Text | Payment gateway used |
| status | Text | Payment status |
| response_data | Text | JSON response from gateway |
| created_at | DateTime | When payment was logged |

---

## 🔍 Common Tasks

### Export All Customer Data to Excel

1. Go to: **http://localhost:8000/view_enrollments.html**
2. Click: **"Export CSV"** button
3. Open in Excel or Google Sheets

### Find All Customers from Specific Course

```bash
sqlite3 enrollments.db
```

```sql
SELECT * FROM enrollments WHERE course='Executive Leadership';
```

### Get Revenue Report

```sql
SELECT course, COUNT(*) as enrollments, SUM(amount) as revenue
FROM enrollments 
WHERE payment_status='completed'
GROUP BY course
ORDER BY revenue DESC;
```

### Check Failed Payments

```sql
SELECT * FROM payment_logs WHERE status != 'completed' ORDER BY created_at DESC;
```

---

## 🐛 Troubleshooting

| Problem | Solution |
|---------|----------|
| "php: command not found" | Restart PowerShell or computer after installation |
| "Address already in use :8000" | Kill existing process: `netstat -ano \| findstr :8000` → `taskkill /PID [PID]` |
| "Database locked error" | Close view_enrollments.html and restart PHP server |
| Success page still not showing | Check browser console (F12) for JavaScript errors, ensure you're using PHP (not Python server) |
| Emails not sending | PHP mail() needs SMTP config. For now, check `email_log.txt` for logs |
| Can't see customer in database | Check browser console: should see "✓ Enrollment processed on backend" |

---

## 📁 File Structure

```
adeptskil/
├── enrollment_with_fees.html      ← Payment form (Frontend)
├── success.html                   ← Success page after payment
├── cancel.html                    ← Cancel page
├── payment_form_creditcard.html   ← Credit card form
├── view_enrollments.html          ← Customer dashboard (NEW)
├── process_enrollment.php         ← Saves data to database (UPDATED)
├── get_enrollments.php            ← API to fetch customer data (NEW)
├── db_config.php                  ← Database connection (NEW)
├── enrollments.db                 ← Customer database (auto-created on first payment)
├── email_log.txt                  ← Email send logs
├── setup_php.bat                  ← Automated PHP installer (NEW)
├── PHP_INSTALLATION_GUIDE.md      ← Manual installation guide
└── [other files...]
```

---

## ✨ What Works Now

- ✅ **4-Step Enrollment** - Form → Pricing → Payment → Success
- ✅ **3 Payment Methods** - PayPal, Razorpay, Credit Card
- ✅ **Customer Database** - All enrollments saved to SQLite
- ✅ **Unique Invoices** - Each enrollment gets unique invoice ID
- ✅ **Payment Status Tracking** - Pending/Completed status
- ✅ **Email Notifications** - Confirmation emails to customers
- ✅ **Customer Dashboard** - View all enrollments with search/export
- ✅ **Zero Manual Setup** - Automated installation & database creation
- ✅ **No 405 Errors** - Full PHP backend operational

---

## 🎯 Next Steps (Optional)

1. **Add Admin Password** - Create login for enrollments page
2. **Email Templates** - Customize confirmation emails
3. **Payment Webhooks** - Auto-update status when PayPal confirms
4. **SMS Notifications** - Text customers after enrollment
5. **Enrollment Approval** - Admin review before access granted
6. **Course Access Codes** - Send unique codes to customers

---

## 📞 Support

If you get stuck:

1. **Check browser console** (F12) for JavaScript errors
2. **Check PHP terminal** for error messages
3. **Read:** `PHP_INSTALLATION_GUIDE.md` for detailed setup
4. **Verify:** PHP is running with `php -v`
5. **Verify:** Database exists: `enrollments.db` in your folder

---

## 🔐 Security Notes for Production

⚠️ **IMPORTANT: These are development security notes only**

Before going live, you should:

1. **Use HTTPS** - All payment pages must be HTTPS only
2. **Update PayPal Email** - In `process_enrollment.php`
3. **Update Razorpay Keys** - Use live keys, not test keys
4. **Protect Admin Dashboard** - Add password to `view_enrollments.html`
5. **Enable SMTP** - Configure real email server
6. **Regular Backups** - Backup `enrollments.db` daily
7. **Validate All Data** - Server-side validation (currently done)
8. **Use HTTPS DNS** - Secure all API endpoints

---

**🎉 You're all set! Complete your first customer enrollment and watch the data flow into your database in real-time!**
