# HTTP 405 Error Fix - Local Development Setup

## Problem
You're seeing **HTTP ERROR 405 "Method Not Allowed"** when completing a payment.

### Root Cause
Python's built-in HTTP server (`python -m http.server`) **does NOT execute PHP files**. When the success page tries to POST enrollment data to `process_enrollment.php`, Python's server tries to serve it as a static file, which doesn't support POST requests → **405 Error**.

---

## Solution (Choose One)

### ✅ Option 1: Use PHP Built-in Server (Recommended)

**Prerequisites:** PHP must be installed

**Steps:**

1. **Install PHP** (if not already installed):
   - Download from: https://www.php.net/downloads
   - Windows: Download "Windows builds" → VC15 x64 Thread Safe ZIP
   - Extract to a folder (e.g., `C:\php`)
   - Add PHP to Windows PATH environment variable

2. **Stop the Python server** (if running):
   - Press `Ctrl+C` in the terminal

3. **Start PHP server** instead:
   ```bash
   cd "c:\Users\MANJUNATH B G\adeptskil"
   php -S localhost:8000
   ```
   
   Output should show:
   ```
   Development Server started at http://localhost:8000
   ```

4. **Open in browser:**
   ```
   http://localhost:8000/enrollment_with_fees.html?course=Account%20Management
   ```

5. **Complete a test payment:**
   - Fill form → Select pricing → Choose payment method
   - Complete payment (test credentials included)
   - **You should now see the success page WITHOUT 405 errors!**

---

### ✅ Option 2: Continue Testing WITHOUT PHP (For Now)

**No installation needed! Use browser console to view data.**

When you complete a payment:
1. You'll see the **success page** (no 405 error anymore)
2. Open **Browser Console** (Press `F12` → Console tab)
3. You'll see the enrollment data displayed in a formatted box:
   ```
   === ENROLLMENT SUMMARY ===
   Full Name: John Doe
   Email: john@example.com
   Phone: +1234567890
   ...
   ```

This allows **front-end testing without PHP backend**.

---

## Verification Checklist

### With PHP Server:
- ✅ Success page loads without 405 error
- ✅ Data submitted to `process_enrollment.php`
- ✅ Browser console shows: `"✓ Enrollment processed on backend"`
- ✅ Enrollment DB updated (if configured)

### Without PHP (Browser Console Testing):
- ✅ Success page loads
- ✅ Browser console shows enrollment summary
- ✅ Console shows: `"⚠️ Backend endpoint not responding (PHP not installed?)"`
- ✅ Message provides setup instructions

---

## The 3 Payment Methods (Testing)

All three work without PHP backend (data displays in console):

### PayPal
- **Test Account**: Provided in sandbox
- **Amount**: Any amount (e.g., $49.99)
- **Redirect**: Success page + console data

### Razorpay
- **Test Mode**: Automatic (using test key)
- **Amount**: Converted to INR, allows any amount
- **Redirect**: Success page + console data

### Credit Card
- **Card Number**: `4242 4242 4242 4242` (Stripe test)
- **Expiry**: Any future date (e.g., `12/25`)
- **CVC**: Any 3 digits (e.g., `123`)
- **Redirect**: Success page + console data

---

## Quick Troubleshooting

| Issue | Cause | Solution |
|-------|-------|----------|
| **404 - Page not found** | Wrong URL path | Verify course URL: `?course=AccountManagement` (no spaces) |
| **405 - Method not allowed** | Using Python server | Switch to PHP server (`php -S localhost:8000`) |
| **Data not appearing in console** | Browser cache | Refresh page: `Ctrl+Shift+R` (hard refresh) |
| **PayPal form won't submit** | Sandbox issue | Check browser console for errors, try another method |
| **Razorpay modal not opening** | Script not loaded | Check console: look for "Failed to load Razorpay" error |

---

## File Structure for Reference

```
adeptskil/
├── enrollment_with_fees.html        ← Main enrollment form
├── success.html                     ← Payment success page (UPDATED)
├── cancel.html                      ← Payment cancelled page
├── payment_form_creditcard.html     ← Credit card form
├── course_fees.json                 ← Pricing database
├── process_enrollment.php           ← Backend endpoint (requires PHP)
├── ipn_handler.php                  ← PayPal webhook handler
├── config.php                       ← PHP configuration
└── [other course/page files]
```

---

## For Production

1. **Install PHP** on your production server
2. **Update config.php**:
   ```php
   // Change sandbox to live endpoints
   $paypal_email = 'your-business@email.com';
   $paypal_endpoint = 'https://www.paypal.com/cgi-bin/webscr'; // Remove 'sandbox'
   ```

3. **Update payment keys**:
   - Replace Razorpay test key with live key
   - Replace Stripe test key with live key
   - Update PayPal business email

4. **Enable HTTPS** (required for payment gateways)

---

## Quick Links
- PHP Downloads: https://www.php.net/downloads
- PayPal Sandbox: https://www.sandbox.paypal.com
- Razorpay Test Keys: https://dashboard.razorpay.com/
- Stripe Test Keys: https://dashboard.stripe.com/

---

## Need More Help?

**Check browser console (F12) for detailed error messages**, which will guide you to the solution. All errors now include helpful instructions.
