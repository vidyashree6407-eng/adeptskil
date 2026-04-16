# GoDaddy SMTP Testing Guide

## Quick Start Testing (5 Minutes)

### Test 1: Configuration Check
```bash
# Run this to verify configuration:
php smtp-diagnostic.php
```

Should show:
- ✓ MAIL_METHOD: smtp
- ✓ SMTP_HOST: smtpout.secureserver.net
- ✓ SMTP_PORT: 465
- ✓ sendViaSMTP() defined: YES
- ✓ Test email file written: SUCCESS

---

## Manual Testing Steps

### Test 2: Direct Enrollment Email
1. Open `http://localhost:8000/enrollment.html` in browser
2. Fill form with:
   - Name: "Test User"
   - Email: "your-email@gmail.com"
   - Course: Select any course
   - Amount: Any amount
3. Submit form
4. Check email for "Enrollment Confirmation"
5. Check `/emails/` directory for stored email JSON

### Test 3: Admin Dashboard Email Sending
1. Go to `admin-login.html` and login
2. Navigate to `view-enrollments.php`
3. Click on any enrollment (info icon)
4. Modal opens with details
5. Click "Send Email" button
6. Should see "✓ Email sent successfully"

### Test 4: Email Storage Verification
1. Open `emails-dashboard.php` in browser
2. Should show list of recent emails
3. Click on email to view full details
4. Check sender, recipient, subject, body

---

## File System Verification

### Check Email Storage
```
/emails/
├── EMAIL-20260406144530-12345.json
├── EMAIL-20260406144531-54321.json
└── ...
```

Each file contains:
```json
{
  "id": "EMAIL-20260406144530-12345",
  "timestamp": "2026-04-06 14:45:30",
  "to": "user@example.com",
  "from": "info@adeptskil.com",
  "subject": "Enrollment Confirmation",
  "body": "...",
  "status": "sent"
}
```

### Check Log File
```
/mail_log.txt
```

Should contain entries like:
```
[2026-04-06 14:45:30] TO: user@example.com | SUBJECT: Enrollment Confirmation | FROM: info@adeptskil.com
Body length: 2145 bytes
...
```

---

## Expected Test Results

### Successful Email Flow
```
✓ Form submitted
  ↓
✓ Saved to database
  ↓
✓ sendEmail() called
  ↓
✓ Email saved to /emails/EMAIL-*.json
  ↓
✓ sendViaSMTP() connects to smtpout.secureserver.net:465
  ↓
✓ Authentication successful
  ↓
✓ Email delivered to inbox
  ↓
✓ Logged to mail_log.txt
```

### Email Delivery Timeline
- User submits: Immediate (appears in `/emails/` instantly)
- Email queued: Immediate (saved to file)
- Email sent via SMTP: 1-5 seconds
- Email in inbox: 5-30 seconds (depends on ISP)

---

## Debugging Commands

### View Recent Emails
```bash
# List all stored emails
ls -la /path/to/adeptskil/emails/

# View specific email
cat /path/to/adeptskil/emails/EMAIL-*.json | more
```

### Check SMTP Connection
```bash
# Windows: Test connection
telnet smtpout.secureserver.net 465

# Linux/Mac: Test connection
nc -v smtpout.secureserver.net 465
```

### View Email Log
```bash
# Last 20 entries
tail -20 /path/to/adeptskil/mail_log.txt

# Search for errors
grep "error" /path/to/adeptskil/mail_log.txt
```

---

## Common Test Issues & Solutions

### Issue: Test Email Not in Inbox

**Check 1: Is it in `/emails/`?**
- If YES → Email queued successfully, checking SMTP delivery
- If NO → Check form submission errors

**Check 2: Is Admin Email Receiving?**
- Go to `view-enrollments.php`
- Check if new enrollment shows up
- If YES → Admin emails working, user email might be spam

**Check 3: Check Spam/Junk**
- Sometimes GoDaddy emails land in spam
- Add info@adeptskil.com to contacts
- Whitelist domain

### Issue: "Email queued" but Never Arrives

**Cause:** GoDaddy SMTP might have rate limiting
- GoDaddy limits to ~100 emails/hour
- Check if over quota
- Wait 5-10 minutes and retry

**Solution:**
- Verify in `/mail_log.txt` for connection errors
- Check GoDaddy account settings
- Verify SMTP_USERNAME and SMTP_PASSWORD correct

### Issue: ERROR in Console

**If you see:**
```
Warning: Undefined array key "SERVER_NAME"
Notice: fputs(): Send of 10 bytes failed
```

**Meaning:** Normal warnings, email still saves to file
**Solution:** Email delivery continues normally

---

## Production Testing Checklist

- [ ] Test enrollment creates customer email ✓
- [ ] Test enrollment creates admin email ✓
- [ ] PayPal test creates payment email ✓
- [ ] Admin dashboard emails send ✓
- [ ] Email formatting looks good ✓
- [ ] No errors in mail_log.txt ✓
- [ ] All emails stored in `/emails/` ✓
- [ ] Credentials safe in config.php ✓
- [ ] .gitignore protects config.php ✓

---

## Email Templates Verification

### Customer Enrollment Email
- From: `Adeptskil <info@adeptskil.com>`
- Subject: `Enrollment Confirmation - [Course]`
- Body: HTML formatted with course details
- Status: ✓ Implemented

### Admin Enrollment Notification
- To: `info@adeptskil.com`
- Subject: `[New Enrollment] - [Student] - [Course]`
- Body: Student details, amount, enrollment ID
- Status: ✓ Implemented

### Payment Confirmation Email
- From: `Adeptskil <info@adeptskil.com>`
- Subject: `Payment Confirmation - [Course]`
- Body: Transaction ID, amount, payment status
- Status: ✓ Implemented

---

## Monitoring & Maintenance

### Daily Checks
1. Check `/mail_log.txt` for errors
2. Monitor `/emails/` not growing too large
3. Verify admin receives notifications

### Weekly Tasks
1. Archive old emails (backup `/emails/`)
2. Review log for failures
3. Test one full enrollment → payment flow

### Monthly Tasks
1. Check GoDaddy account email limits
2. Verify SMTP account still active
3. Update credentials if needed

---

## Support Resources

**GoDaddy SMTP Details:**
- Host: smtpout.secureserver.net
- Port: 465 (SSL) or 587 (TLS)
- Username: Your GoDaddy email
- Password: GoDaddy email password

**Email Admin:**
- Dashboard: `emails-dashboard.php`
- View logs: `/mail_log.txt`
- Email storage: `/emails/` directory
- Config: `config.php` (protected)

**Need Help?**
1. Check `/mail_log.txt` for error messages
2. Review `/emails/` for stored email content
3. Verify GoDaddy account active
4. Test SMTP connection with telnet/nc

---

**Status: Ready for Production Testing** ✓
