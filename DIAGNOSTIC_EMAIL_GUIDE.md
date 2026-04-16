# Email Delivery Diagnostic Guide

## Problem
Customers are not receiving confirmation emails after PayPal payment.

## Root Causes Addressed
✅ Missing `notify_url` in PayPal form - FIXED  
✅ Enrollment not stored in database before payment - FIXED  
✅ IPN lookup using wrong field - FIXED  
✅ SMTP error logging was minimal - IMPROVED  

## How to Diagnose

### Step 1: Access the Diagnostic Dashboard
Visit this URL in your browser:
```
https://adeptskil.com/diagnostic-email.php
```

This dashboard shows:
- ✅ Configuration Status (MAIL_METHOD, SMTP settings)
- ✅ Database Status (total enrollments, recent payments)
- ✅ Recent Email Logs (sent emails)
- ✅ SMTP Debug Log (connection errors)
- ✅ IPN Handler Log (PayPal webhook status)

### Step 2: Send a Test Email
1. Go to **Section 6** on the diagnostic dashboard
2. Enter your email address (e.g., vidyashree6407@gmail.com)
3. Click "📧 Send Test Email"
4. Check your inbox for the test email
5. **Important:** Check your SPAM/JUNK folder too!

**What should happen:**
- If you receive the test email → **SMTP is working!**
- If you don't see it → Check SMTP Debug Log for errors (usually SSL certificate or authentication issue)

### Step 3: Check Email Logs
1. Go to **Section 3** on the diagnostic dashboard
2. Look at the "Last 5 emails sent" list
3. If emails are being recorded there but not delivered → **Email provider filtering issue**

### Step 4: Monitor IPN Events
1. Go to **Section 5** on the diagnostic dashboard
2. Process a payment on your site
3. Check the IPN Handler Log for entries like:
   ```
   [timestamp] IPN Handler started
   [timestamp] IPN Received: {...}
   [timestamp] ✓ Enrollment FOUND
   [timestamp] 📧 Sending confirmation emails to: customer@email.com
   ```

If you DON'T see these entries → **PayPal is not sending IPN callbacks**

## Common Issues & Solutions

### Issue 1: Test Email Not Received
**Symptom:** Test email disappears into void  
**Solution:**
1. Check SPAM/JUNK folder
2. Check SMTP Debug Log for SSL errors
3. Verify SMTP credentials are correct in config.php
4. Some providers require whitelisting sender domain

### Issue 2: IPN Handler Log Empty
**Symptom:** IPN log shows no activity after payment  
**Solution:**
1. Verify `notify_url` is set in PayPal form (enrollment.html line ~260)
2. Verify your PayPal business account is configured for IPN
3. Check if PayPal firewall is blocking our server IP
4. PayPal must be able to reach: `https://adeptskil.com/ipn_handler.php`

### Issue 3: Enrollment Not Found in IPN Log
**Symptom:** IPN log shows "✗ ENROLLMENT NOT FOUND"  
**Solution:**
1. Verify enrollment form is storing data BEFORE payment (should happen at line ~430 in enrollment.html)
2. Check database has the enrollment record (Section 2 of diagnostic)
3. Verify invoice number from PayPal matches what's in database

### Issue 4: Configuration Shows Wrong MAIL_METHOD
**Symptom:** MAIL_METHOD is 'file' or 'php' instead of 'smtp'  
**Solution:**
1. Edit config.php (line 17)
2. Change: `define('MAIL_METHOD', 'smtp');`
3. Verify SMTP credentials below that line

## Quick Checklist

- [ ] Test email sends successfully
- [ ] Test email received (not in spam)
- [ ] SMTP Debug Log shows successful connections
- [ ] Database has enrollment records
- [ ] IPN log shows "✓ ENROLLMENT FOUND" after payment
- [ ] IPN log shows "📧 Sending confirmation emails to:"
- [ ] Actual customer receives confirmation email

## Files Modified

1. **enrollment.html** - Added notify_url and database storage
2. **config.php** - Enhanced SMTP error logging
3. **ipn_handler.php** - Improved enrollment lookup and logging
4. **diagnostic-email.php** - New diagnostic dashboard

## Next Steps

1. Send test email using diagnostic dashboard
2. Check SMTP Debug Log for any errors
3. Process a test payment
4. Monitor IPN Handler Log
5. Verify customer receives email

If issues persist after these steps, the most likely causes are:
- Email provider's spam filters (whitelist adeptskil.com domain)
- PayPal's IPN settings not configured
- GoDaddy SMTP credentials expired or incorrect

---

**Access Diagnostic Dashboard:** https://adeptskil.com/diagnostic-email.php
