# ⚡ QUICK REFERENCE - What to Do NOW

## 🎯 YOU ARE HERE: TESTING PHASE

**All code fixes are done. Now we test.**

---

## 🔴 IMMEDIATE ACTION (Next 15 minutes)

### 1️⃣ FIRST: Send Test Email
```
Go to: https://adeptskil.com/diagnostic-email.php

Section 6: "Send Test Email"
  Enter: your-email@example.com
  Click: 📧 Send Test Email
  
Wait 10 seconds...
Check email inbox (+ SPAM folder)
```

✅ **Email received?** → Go to Step 2  
❌ **Email NOT received?** → Check Section 4 for SMTP errors

---

### 2️⃣ SECOND: Test Enrollment + Payment
```
Go to: https://adeptskil.com/enrollment.html

Fill form:
  Name: Test Name
  Email: your-email@example.com (SAME as above)
  Phone: 9999999999
  City: Test City
  
Click: Continue to Payment
Click: Pay with PayPal
```

✅ **PayPal form appeared?** → Continue to Step 3  
❌ **Got error?** → Check F12 Console for errors

---

### 3️⃣ THIRD: Complete Payment
```
On PayPal:
  Log in
  Complete payment (or test payment if using Sandbox)
  Click "Return to Merchant"
  
Notice: You return to success page
```

✅ **Back on site successfully?** → Go to Step 4  
❌ **PayPal error?** → Screenshot and send error

---

### 4️⃣ FOURTH: Check Confirmation Email
```
Wait 30 seconds...
Check your email

Subject should be:
  "✓ Enrollment Confirmation - [Course Name]"
  
ALSO check SPAM/JUNK folder
```

✅ **Email received?** → SUCCESS! All working!  
❌ **Still no email?** → Go to Debugging section below

---

## 🔧 DEBUGGING - Check These Logs

### If Test Email Failed:
```
Diagnostic Dashboard > Section 4: SMTP Debug Log
Look for: ✗ FAILED messages
Share the error with me
```

### If Enrollment Didn't Save:
```
Diagnostic Dashboard > Section 2: Database Enrollments
If you don't see your test record:
  - Payment wasn't processed
  - Or enrollment form had error
  - Check F12 Console for JavaScript errors
```

### If Confirmation Email Didn't Arrive:
```
Diagnostic Dashboard > Section 5: IPN Handler Log
Look for:
  ✓ IPN Handler started
  ✓ Enrollment FOUND
  📧 Sending confirmation emails
  
If you see these 3 messages:
  → Email WAS sent
  → Check SPAM/JUNK folder
  → Or email provider filtering
  
If you DON'T see these messages:
  → Payment IPN not received from PayPal
  → PayPal settings issue
```

---

## 📊 EXPECTED LOG MESSAGES

### ✅ SUCCESS PATH (What you should see):

**SMTP Debug Log:**
```
✓ EMAIL SENT via SMTP to: your-email@example.com
```

**Database:**
```
Invoice: ENR-20260416...
Email: your-email@example.com
Course: [Course Name]
Status: pending
```

**IPN Handler Log:**
```
✓ IPN Handler started
✓ Enrollment FOUND: ID=123, Email=your-email@example.com
📧 Sending confirmation emails to: your-email@example.com
```

---

## ❌ COMMON ERRORS & FIXES

| Error | What it means | Fix |
|-------|---------------|-----|
| "PHPMailer not found" | SMTP library missing | Re-upload PHPMailer folder |
| "SSL Error" | Certificate issue | Update config.php SMTP settings |
| "Auth Error" | Wrong credentials | Verify GoDaddy SMTP password |
| "✗ ENROLLMENT NOT FOUND" | Data not saved | Check form validation |
| No IPN log entries | PayPal not calling | Verify PayPal IPN settings |
| Email in SPAM | Provider filtering | Whitelist adeptskil.com domain |

---

## 📞 WHEN TO CONTACT ME

Share these screenshots/info:

1. **Test email fails:**
   - SMTP Debug Log (Section 4)
   - Any ✗ error messages

2. **Enrollment doesn't save:**
   - Browser console errors (F12)
   - Form validation errors

3. **Confirmation email not received:**
   - IPN Handler Log (Section 5)
   - SMTP Debug Log (Section 4)
   - Confirmation that payment was successful on PayPal

---

## 🏃 FAST PATH (< 10 min)

```
1. diagnostic-email.php > Section 6 > Send test email
   ↓
2. Check your inbox (+ spam)
   ↓
3. If success → All working! ✅
   ↓
4. If fail → Share SMTP Debug Log (Section 4)
```

---

## 📍 KEY URLS

```
Main Test Tool:           https://adeptskil.com/diagnostic-email.php
Enrollment Form:          https://adeptskil.com/enrollment.html
Admin View Emails:        https://adeptskil.com/emails-dashboard.php
Log Files (via FTP):      /smtp_debug.log, /ipn_handler.log
```

---

## ✅ FINAL CHECKLIST - Mark as you go

```
[ ] Test email sent successfully
[ ] Test email received (inbox or spam)
[ ] Test enrollment form completed
[ ] Enrollment appears in database
[ ] PayPal payment processed
[ ] IPN log shows "✓ Enrollment FOUND"
[ ] IPN log shows "📧 Sending emails"
[ ] Confirmation email received
[ ] Database status changed to "completed"

If ALL checked → SYSTEM IS WORKING! 🎉
```

---

## 🎯 START NOW

👉 **Go to:** https://adeptskil.com/diagnostic-email.php

👉 **Do:** Section 6 - Send Test Email

👉 **Check:** Your inbox + SPAM folder

👉 **Report:** Success or error message

**That's it! Let me know what happens.**
