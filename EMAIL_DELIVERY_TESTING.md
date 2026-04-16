# Email Delivery - Verification & Testing Checklist

## Summary of Changes Made

### 1. ✅ Fixed PayPal Form (enrollment.html)
- Added missing `notify_url` parameter pointing to ipn_handler.php
- Added database storage BEFORE redirecting to PayPal
- This ensures enrollment record exists when IPN arrives

### 2. ✅ Enhanced SMTP Logging (config.php)
- Added detailed error logging to `smtp_debug.log`
- SSL verification disabled for compatibility
- Increased timeout to 15 seconds
- Better error messages for troubleshooting

### 3. ✅ Improved IPN Handler (ipn_handler.php)
- Added filename parameter early detection
- Fixed enrollment lookup to use invoice number first
- Added detailed debug logging for every step
- Uses separate log file: `ipn_handler.log`

### 4. ✅ Created Diagnostic Dashboard (diagnostic-email.php)
- Visual interface to check all email systems
- Test email sending capability
- Live log viewing for SMTP and IPN
- Database enrollment verification

---

## Testing Workflow

### PHASE 1: SMTP Configuration Test (5 minutes)

**Step 1:** Open diagnostic dashboard
```
https://adeptskil.com/diagnostic-email.php
```

**Step 2:** Check Section 1 - Configuration Status
- Verify MAIL_METHOD = "smtp"
- Verify SMTP_HOST = "smtpout.secureserver.net"
- Verify SMTP_PORT = 465

**Step 3:** Send test email (Section 6)
- Enter your email address
- Click "📧 Send Test Email"
- Wait 5 seconds

**Step 4:** Check results
```
If email received in inbox:
  ✅ SMTP is WORKING - Move to Phase 2
  
If email NOT received:
  ❌ Check SPAM/JUNK folder
  ❌ Review SMTP Debug Log (Section 4)
  ❌ Look for error messages like "SSL", "Auth", "Connection"
  ❌ If SSL error: SMTP credentials may be invalid
  ❌ Contact GoDaddy support to verify SMTP settings
```

---

### PHASE 2: Enrollment Database Test (5 minutes)

**Step 1:** Check database (Section 2 on diagnostic dashboard)
```
Should see:
- Total Enrollments: > 0
- Recent Enrollments table populated
```

**Step 2:** If database is empty:
```
- Go to enrollment form: /enrollment.html
- Fill in all fields
- Enter test email address
- Click "Continue to Payment"
- Check diagnostic dashboard Section 2 again
- Should now see a pending enrollment
```

---

### PHASE 3: IPN Webhook Test (10 minutes)

**Step 1:** Verify IPN Settings
```
Open diagnostic-email.php
Go to Section 5: IPN Handler Log
- If empty: "ℹ IPN log not created yet"
- Expected after first PayPal IPN
```

**Step 2:** Simulate Payment (On diagnostic dashboard Section 7)
- Click "🎯 Simulate Payment Email"
- This will:
  ✓ Find last pending enrollment
  ✓ Retrieve customer email
  ✓ Trigger sendPaymentConfirmationEmails()
  ✓ Should see result message

**Step 3:** Check outcomes
```
If "✓ Confirmation emails triggered for:":
  ✅ Email sending mechanism works
  ✅ Move to Phase 4
  
If error occurs:
  ❌ Likely database connection issue
  ❌ Check database credentials in db_config.php
```

---

### PHASE 4: Real Payment Test (15 minutes)

**Step 1:** Create test enrollment
- Go to /enrollment.html
- Fill in form with test data
- Use **TEST email address** (not your production email)
- Click "Continue to Payment"
- Should go to PayPal

**Step 2:** Complete test payment on PayPal
- Use PayPal sandbox credentials if testing
- Or use real payment method
- After payment, click "Return to Merchant"

**Step 3:** Monitor IPN Handler
- Open diagnostic dashboard
- Refresh it a few times
- Go to Section 5: IPN Handler Log
- Look for entries like:
  ```
  [timestamp] IPN Handler started
  [timestamp] IPN Received: {...}
  [timestamp] ✓ Enrollment FOUND
  [timestamp] 📧 Sending confirmation emails to: testemail@example.com
  ```

**Step 4:** Check test email inbox
```
If email received:
  ✅ ALL SYSTEMS WORKING!
  ✅ Issue must be email provider filtering
  
If email NOT received but IPN log shows "📧 Sending...":
  ⚠️ Email sent but filtered by provider
  Solution: Add Adeptskil domain to email whitelist
```

---

## Log Files to Monitor

### 1. SMTP Debug Log
```
Path: /smtp_debug.log
Shows: SMTP connection attempts, authentication, send results
Look for: ✓ SENT or ✗ FAILED messages
```

### 2. IPN Handler Log  
```
Path: /ipn_handler.log
Shows: Every PayPal IPN event received
Look for: 
  - "IPN Received"
  - "✓ Enrollment FOUND"
  - "📧 Sending confirmation emails"
  - Any ✗ error messages
```

### 3. Email Storage
```
Path: /emails/ directory
Shows: JSON files for each email attempt
File format: EMAIL-YYYYMMDDHHmmss-#####.json
Uses: Can view which emails were "sent" locally
```

### 4. Mail Log
```
Path: /mail_log.txt
Shows: All email attempts with subject and recipient
Format: [timestamp] TO: email | SUBJECT: ... | FROM: ...
```

---

## Common Issues & Fixes

### ❌ "ENROLLMENT NOT FOUND" in IPN log
**Cause:** Enrollment wasn't stored before payment  
**Fix:** Verify enrollment.html is calling process_enrollment.php (line ~450)

### ❌ SMTP Connection Failed
**Cause:** GoDaddy credentials wrong or SSL certificate issue  
**Fix:** 
1. Verify credentials in config.php
2. Test with: telnet smtpout.secureserver.net 465
3. Contact GoDaddy support

### ❌ "IPN Received but NOT verified"
**Cause:** PayPal verification failed  
**Fix:** 
1. Check if in sandbox vs production mode
2. Verify business email matches PayPal account
3. Check if using correct IPN URL

### ❌ Email sent but not delivered
**Cause:** Provider spam filtering (MOST COMMON)  
**Fix:**
1. Get Adeptskil domain whitelisted
2. Check SPF/DKIM/DMARC records
3. Try different email provider
4. Add "Adeptskil" to subject line

---

## Success Criteria

✅ Phase 1: Test email received in inbox  
✅ Phase 2: Database has enrollment record  
✅ Phase 3: Simulate payment works  
✅ Phase 4: Real payment triggers confirmation email  

---

## Quick Diagnostics

**Problem:** No emails sent  
**Check:** Section 4 SMTP Debug Log for "✗ FAILED"  

**Problem:** Emails logged but not received  
**Check:** Email provider spam folder  

**Problem:** IPN log empty  
**Check:** PayPal is sending webhook to ipn_handler.php  

**Problem:** IPN received but enrollment not found  
**Check:** enrollment.html is storing to database before payment  

---

## Access Points

- **Enrollment Form:** https://adeptskil.com/enrollment.html
- **Diagnostic Dashboard:** https://adeptskil.com/diagnostic-email.php
- **Admin View Emails:** https://adeptskil.com/emails-dashboard.php
- **Email Log:** Check /mail_log.txt via FTP

---

## Email Flow Diagram

```
Customer Fills Form
        ↓
[enrollment.html] Validates & Stores to Database ✅ (NEW)
        ↓
[enrollment.html] Shows PayPal Form with notify_url ✅ (NEW)
        ↓
[PayPal] Customer Completes Payment
        ↓
[PayPal] Sends IPN to ipn_handler.php
        ↓
[ipn_handler.php] Looks up Enrollment by Invoice ✅ (IMPROVED)
        ↓
[ipn_handler.php] Calls sendPaymentConfirmationEmails()
        ↓
[config.php] Routes to SMTP with better logging ✅ (IMPROVED)
        ↓
[GoDaddy SMTP] Sends email
        ↓
[Provider] May filter to spam
        ↓
Customer's Inbox (or Spam folder)
```

---

## Files Changed

1. `/enrollment.html` - Added notify_url and database storage
2. `/config.php` - Enhanced SMTP logging
3. `/ipn_handler.php` - Improved lookup and logging
4. `/diagnostic-email.php` - New diagnostic tool
5. `/DIAGNOSTIC_EMAIL_GUIDE.md` - This guide

---

**Start testing:** https://adeptskil.com/diagnostic-email.php
