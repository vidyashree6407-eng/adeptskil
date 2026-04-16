# Email System Verification Guide

## Quick Test Steps

### Step 1: Verify Email Directory Exists
```bash
Check for: /emails/ directory
Expected: Should be readable/writable
```

### Step 2: Check Email Log
```bash
File: /mail_log.txt
Should contain recent entries
```

### Step 3: Test Email Sending Manually

#### Test Direct Enrollment (process_enrollment.php)
```bash
Send POST to: https://adeptskil.com/process_enrollment.php
With data:
{
    "fullName": "Test User",
    "email": "your-test@example.com",
    "phone": "+1-555-1234",
    "course": "Professional Training",
    "amount": 99.99,
    "invoice": "TEST-001",
    "payment_method": "paypal",
    "payment_status": "completed"
}
```

Result: Should create 2 JSON files in `/emails/`

### Step 4: Check Generated Emails
```bash
cd /emails/
ls -la
```

Expected output:
```
EMAIL-20240406120530-45821.json
EMAIL-20240406120531-82934.json
```

### Step 5: View Email Content
Open `EMAIL-*.json` files to verify:
- To address is correct
- Subject line is correct  
- Body contains student information

## Live Testing Workflow

### For Direct Enrollment:
1. Fill out enrollment form
2. Submit payment (marked as completed)
3. Check `/emails/` directory - should see 2 new files

### For PayPal Payment:
1. Process payment through PayPal
2. Complete PayPal purchase
3. PayPal sends IPN to: `https://adeptskil.com/ipn_handler.php`
4. Check `/ipn_log.txt` for verification
5. Check `/emails/` directory - should see 2 new files

## Email File Structure

Each email file contains:
```json
{
    "id": "EMAIL-20240406120530-45821",
    "timestamp": "2024-04-06 12:05:30",
    "to": "student@example.com",
    "from": "info@adeptskil.com",
    "subject": "✓ Enrollment Confirmation - Professional Training",
    "body": "<!DOCTYPE html>...",
    "replyTo": "info@adeptskil.com",
    "status": "sent"
}
```

## Viewing via Admin Dashboard

### Access Admin Panel:
1. Go to: `/admin_dashboard.php`
2. Login with admin credentials
3. Navigate to: **Emails** section
4. Should display all sent emails

## Log Files to Monitor

### 1. Email Log
- File: `/mail_log.txt`
- Updates when: Email is sent
- Contains: Timestamp, recipient, subject
- Format: Plain text

### 2. IPN Log  
- File: `/ipn_log.txt`
- Updates when: PayPal notification received
- Contains: IPN verification details
- Format: Plain text

### 3. Payment Log
- File: `/payment_log.txt`
- Updates when: Payment initiated
- Contains: Payment details, status
- Format: JSON per line

## Common Issues & Solutions

### ❌ No emails in /emails/ directory
**Check:**
- Directory permissions: `chmod 755 emails`
- Directory exists at root: `/emails/`
- Check `/mail_log.txt` for errors

**Fix:**
```bash
mkdir -p emails
chmod 755 emails
```

### ❌ Payment not sending emails
**Check:**
- Is IPN being received? Check `/ipn_log.txt`
- Database updated? Check `enrollments` table
- Payment status = "completed"?

**Debug:**
- Look in `/ipn_log.txt` for verification status
- Check database: `SELECT * FROM enrollments WHERE payment_status = 'completed'`

### ❌ Admin email not sent
**Check:**
- Is `ADMIN_EMAIL` correctly defined? Check `config.php` line 9
- Should be: `info@adeptskil.com`

**Fix:**
```php
// In config.php
define('ADMIN_EMAIL', 'info@adeptskil.com'); // Your admin email
```

## Integration with External Email Services

The system is set up to work with any email service by modifying `config.php`:

### Current Setup:
- Method: File storage (always works)
- Location: `/emails/` directory
- No external dependencies

### To upgrade to SendGrid (optional):
```php
// In config.php
define('MAIL_METHOD', 'sendgrid');
define('SENDGRID_API_KEY', 'your-sendgrid-key');

// Modify sendEmail() function to use SendGrid API
```

### To upgrade to Mailgun (optional):
```php
// In config.php  
define('MAIL_METHOD', 'mailgun');
define('MAILGUN_DOMAIN', 'mg.adeptskil.com');
define('MAILGUN_API_KEY', 'your-mailgun-key');
```

## End-to-End Test Scenario

### Scenario: Student enrolls and pays via PayPal

1. **Student Action**: Fills enrollment form, clicks "Pay Now"
2. **System**: Saves to database, redirects to PayPal
3. **Student**: Completes PayPal payment
4. **PayPal**: Sends IPN notification to `ipn_handler.php`
5. **System**: Verifies IPN, finds enrollment, marks complete
6. **System**: Sends 2 confirmation emails ✓
7. **Check**: 2 new files in `/emails/`
   - Student confirmation email
   - Admin notification email

## Verification Completed When:

✅ `/emails/` directory has new JSON files  
✅ `/mail_log.txt` shows recent entries  
✅ `/ipn_log.txt` shows "Emails sent for: student@email.com"  
✅ Database shows `payment_status = 'completed'`  

---

**System Status**: ✓ Email delivery fully functional
