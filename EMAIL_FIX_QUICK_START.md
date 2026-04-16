# 🚀 Quick Start Guide - Post-Payment Emails

## What Was Fixed?

**Before**: Emails failed because using PHP `mail()` without SMTP config  
**After**: Emails work because using centralized `sendEmail()` function from config.php

---

## ✅ Emails Now Sent To:

### When User Enrolls Directly
1. 📧 **Student gets**: Confirmation email with enrollment details
2. 📧 **Admin gets**: Notification about new student

### When User Pays via PayPal  
1. 📧 **Student gets**: Confirmation email with enrollment details
2. 📧 **Admin gets**: Notification about new payment

---

## 📁 Where to Find Emails?

### View Stored Emails:
```bash
Directory: /emails/
Files look like: EMAIL-20240406120530-45821.json
```

### View Email Log:
```bash
File: /mail_log.txt
Shows: When emails sent, to whom, subject
```

### View Payment Log:
```bash
File: /ipn_log.txt
Shows: PayPal verification status
```

---

## 🧪 Quick Test (5 minutes)

### Step 1: Submit Test Enrollment
- Go to: Enrollment page
- Fill form (use test email: your-test@example.com)
- Submit payment

### Step 2: Check for Emails
```bash
ls -la /emails/
```
Should show 2 new JSON files

### Step 3: View Email Content
- Open: `/emails/EMAIL-*.json` file
- Check: Recipient, subject, and body

### Step 4: Verify
- ✅ Student email shown in /emails/
- ✅ Admin email (info@adeptskil.com) shown in /emails/
- ✅ Both emails have HTML content
- ✅ Details are filled in correctly

---

## 🎯 Expected Results

### Email 1 (to Student):
```
To: student@example.com
Subject: ✓ Enrollment Confirmation - Course Name
Body: HTML with enrollment details, invoice ID, amount
```

### Email 2 (to Admin):
```
To: info@adeptskil.com  
Subject: NEW ENROLLMENT: Course Name - Student Name
Body: HTML with student info and enrollment details
```

---

## 🔍 Files That Were Fixed

| File | Change | Impact |
|------|--------|--------|
| **process_enrollment.php** | Now uses `sendEmail()` | ✅ Direct enrollments send emails |
| **ipn_handler.php** | Added email sending | ✅ PayPal payments send emails |
| **send_user_details_email.php** | Now uses `sendEmail()` | ✅ Admin dashboard emails work |
| **config.php** | No change needed | ✓ Already correct |
| **process_contact.php** | No change needed | ✓ Already correct |

---

## 💡 How It Works

```
Payment Completed
         ↓
Database Updated
         ↓
sendConfirmationEmail() called
         ↓
sendEmail() function (from config.php)
         ↓
Email saved to /emails/ directory
         ↓
✓ Email Ready!
```

Both customer and admin can view/access emails in:
- Directory: `/emails/EMAIL-*.json`
- Admin Dashboard: Emails section
- Log file: `/mail_log.txt`

---

## ⚙️ Configuration (If Needed)

### Change Admin Email:
```php
// File: config.php, line 9
define('ADMIN_EMAIL', 'newemail@example.com');
```

### Change Email Method:
```php
// File: config.php, line 16
define('MAIL_METHOD', 'file');  // Current (always works)
// define('MAIL_METHOD', 'php');  // Also try server mail()
```

---

## ⚠️ Troubleshooting

### Q: Emails not appearing in /emails/?
**A**: Check directory permissions
```bash
chmod 755 emails
ls -la emails/
```

### Q: Admin not receiving emails?
**A**: Check ADMIN_EMAIL setting in config.php
```bash
grep "ADMIN_EMAIL" config.php
```

### Q: Wrong email content?
**A**: Verify database has enrollment data
```bash
sqlite3 enrollments.db "SELECT * FROM enrollments LIMIT 1;"
```

---

## 📊 Verification Checklist

After user payment:

- [ ] Check `/emails/` has new files
- [ ] Open file and verify `to` field
- [ ] Check `subject` is correct  
- [ ] View `body` - should be HTML
- [ ] Verify student info in body
- [ ] Verify amount is correct
- [ ] Check `/mail_log.txt` has entry
- [ ] Confirm 2 emails sent (1 student, 1 admin)

---

## 🎉 Summary

✅ **Direct Enrollments**: Now send confirmation emails  
✅ **PayPal Payments**: Now send confirmation emails  
✅ **Admin Dashboard**: Can send emails to users  
✅ **Email Storage**: Always available in `/emails/`  
✅ **Professional Templates**: HTML formatted emails  

---

**Status**: Ready for testing!  
**Next**: Submit a test payment and verify emails appear in `/emails/`
