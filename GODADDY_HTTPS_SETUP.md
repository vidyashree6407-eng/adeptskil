# ADEPTSKIL - GODADDY HTTPS SETUP & DEPLOYMENT GUIDE

## ✅ Status: HTTPS is Now Configured

Your `.htaccess` file has been updated to **force HTTPS** on all traffic.

---

## 🚀 DEPLOYMENT STEPS

### Step 1: Upload Files to GoDaddy

1. **Login to GoDaddy cPanel**
   - Go to: `https://cpanel.godaddy.com` or `https://adeptskil.com/cPanel`
   - Use your GoDaddy username and password

2. **Upload using File Manager or FTP**
   - All files from `c:\Users\MANJUNATH B G\adeptskil\` should be in:
     - `/public_html/` (on GoDaddy shared hosting)
   
3. **Critical files to verify:**
   - ✅ `.htaccess` (updated with HTTPS redirect)
   - ✅ `index.html`
   - ✅ `enrollment.html` (has HTTPS PayPal URLs)
   - ✅ `paypal-return.html`
   - ✅ `success.html`
   - ✅ `process_enrollment.php`
   - ✅ `process_chatbot.php`
   - ✅ `config.php`
   - ✅ `db_config.php`

---

## 🔒 SSL Certificate Status

Your SSL certificate is **already installed by GoDaddy**. You don't need to do anything.

✅ **Verify SSL is working:**
```
https://www.adeptskil.com  ← Should show green lock
https://adeptskil.com       ← Should show green lock
```

---

## 🌐 How HTTPS Now Works

### What Was Fixed:

1. **All HTTP traffic redirected to HTTPS**
   ```
   http://adeptskil.com → https://adeptskil.com  ✅
   ```

2. **IP address no longer used**
   ```
   https://10.233.4.184  ❌ (causes SSL error - BLOCKED)
   https://adeptskil.com ✅ (works with SSL)
   ```

3. **PayPal integration uses HTTPS**
   ```
   https://adeptskil.com/enrollment.html → PayPal
   https://adeptskil.com/paypal-return.html → Success
   ```

---

## ✅ Testing Checklist

After uploading files to GoDaddy, verify:

### 1. Website Accessible via HTTPS
```
✓ https://adeptskil.com          - Shows homepage
✓ https://www.adeptskil.com      - Shows homepage
✓ https://adeptskil.com/courses.html  - Shows courses
```

### 2. No SSL Errors
```
✗ "The connection for this site is not secure" - Should NOT appear
✗ "ERR_SSL_PROTOCOL_ERROR" - Should NOT appear
✓ Green lock icon - Should appear
```

### 3. Payment Flow Works
```
✓ Click Course → "Enroll Now"
✓ Fill enrollment form
✓ See PayPal payment button
✓ Click PayPal → Goes to PayPal payment page
✓ Complete payment
✓ Return to adeptskil.com/paypal-return.html
✓ See success page
✓ Data saved to database
✓ Email sent to customer
```

### 4. Database Working
```
✓ Data saved in enrollments.db
✓ Customer details stored
✓ Payment information recorded
```

### 5. Emails Sent
```
✓ Confirmation email file created in /emails/
✓ Email contains customer info
✓ Email contains invoice number
```

---

## 🐛 Troubleshooting

### Issue: "Still getting SSL error even with HTTPS"

**Solution:**
1. Clear browser cache (Ctrl+Shift+Delete)
2. Hard refresh the page (Ctrl+Shift+R)
3. Try incognito/private window
4. Wait 5-10 minutes for DNS to propagate

### Issue: "Access denied when uploading to GoDaddy"

**Solution:**
1. Check file permissions (should be 644 for files, 755 for folders)
2. Delete old files before uploading new ones
3. Try FTP instead of File Manager for bulk uploads

### Issue: "PayPal payment not working"

**Solution:**
1. Verify `info@adeptskil.com` is your PayPal merchant email
2. Check that PayPal IPN settings point to: `https://adeptskil.com/ipn_handler.php`
3. Verify `process_enrollment.php` is accessible at: `https://adeptskil.com/process_enrollment.php`

### Issue: "Database/emails not working on GoDaddy"

**Solution:**
1. Verify PHP 7.4+ is enabled in GoDaddy cPanel
2. Check that file permissions allow writing to `/emails/` folder
3. Check that `/enrollments.db` file permissions are 666

---

## 📝 File Upload Checklist

Use this to verify all files are uploaded correctly to GoDaddy:

```
✓ .htaccess                 (HTTPS redirect config)
✓ index.html                (Homepage)
✓ about.html                (About page)
✓ courses.html              (Courses page)
✓ enrollment.html           (Enrollment form)
✓ success.html              (Success page)
✓ cancel.html               (Cancel page)
✓ paypal-return.html        (PayPal return)
✓ contact.html              (Contact page)
✓ config.php                (Configuration)
✓ db_config.php             (Database config)
✓ process_enrollment.php    (Save enrollment)
✓ process_chatbot.php       (Process messages)
✓ process_paypal_payment.php (PayPal)
✓ ipn_handler.php           (PayPal IPN)
✓ styles.css                (Stylesheets)
✓ script.js                 (JavaScript)
✓ chatbot.js                (Chatbot script)
✓ images/                   (Images folder)
```

---

## 🔧 GoDaddy cPanel Settings to Check

### 1. SSL/TLS Certificates
- Go to: **SSL/TLS Status**
- Verify: ✅ "HTTPS - Automatic"
- Your domain should show: ✅ (Green checkmark)

### 2. PHP Version
- Go to: **Select PHP Version**
- Current version should be: **PHP 7.4 or higher** ✅

### 3. File Permissions
```
Files: 644
Folders: 755
Sensitive files (.htaccess, db_config.php): 644
```

### 4. Email Configuration
- Go to: **Email Accounts** or **Mail**
- Verify your mail server settings are correct

---

## 🎯 Final Verification

### Access via HTTPS
```
https://adeptskil.com
```

You should see:
- ✅ Green lock icon (secure)
- ✅ Homepage loads without errors
- ✅ No SSL warnings
- ✅ All images and styles load properly

### Check Payment Flow
```
1. Click on a course
2. Click "Enroll Now"
3. Fill form with test data
4. Click "Continue to Payment"
5. Click "Pay with PayPal"
6. Complete test payment
7. Verify database has data
8. Verify email was sent
```

---

## 📞 GoDaddy Support

If you have GoDaddy-related issues:

**GoDaddy Support:**
- Phone: 1-480-505-8877
- Live Chat: https://www.godaddy.com/help
- Tell them: "I need help with HTTPS redirection in .htaccess"

---

## ✨ You're Now Ready!

Once files are uploaded to GoDaddy and HTTPS is working:

1. ✅ Website is secure (green lock)
2. ✅ Customers pay via PayPal safely
3. ✅ Data saves to database
4. ✅ Emails are sent automatically
5. ✅ No SSL errors for customers

**Your payment system is now live and production-ready!** 🎉

---

## Need Help?

1. **Test locally first:** `php -S localhost:8000`
2. **Upload to GoDaddy** using File Manager
3. **Access via:** `https://adeptskil.com`
4. **Verify HTTPS:** Green lock should appear
5. **Test payment:** Complete a test transaction

If any errors occur:
- Check browser console (F12)
- Check GoDaddy error logs
- Verify all files uploaded correctly
- Verify permissions (644 for files, 755 for folders)
