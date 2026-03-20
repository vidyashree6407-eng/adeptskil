# PayPal Enrollment Integration - Complete Setup Guide

## ✅ What's Already Implemented

Your Adeptskil enrollment form now has full PayPal integration:

1. **enrollment.html** - Course enrollment form with PayPal payment button
2. **process_enrollment.php** - Backend to process enrollment and save data
3. **payment_config.php** - Central configuration for PayPal and course pricing
4. **get_enrollment_config.php** - API endpoint to serve configuration to the form

## 🚀 Quick Setup Steps

### Step 1: Create a PayPal Business Account (if you don't have one)
- Visit https://www.paypal.com
- Click "Sign Up"
- Select "Business Account"
- Complete the registration

### Step 2: Get Your PayPal Sandbox Client ID (for testing)
1. Go to https://developer.paypal.com
2. Sign in with your PayPal account
3. Click **Apps & Credentials** (in the left menu)
4. Select **Sandbox** (at the top)
5. Under "Client ID", you'll see your **Sandbox Client ID**
6. Copy this ID (looks like: `AXM1234567890abcdefghijklmn...`)

### Step 3: Configure Your Enrollment System
1. Open `payment_config.php` in the project  
2. Find this line (around line 15):
   ```php
   define('PAYPAL_CLIENT_ID', 'YOUR_PAYPAL_CLIENT_ID_HERE');
   ```
3. Replace `YOUR_PAYPAL_CLIENT_ID_HERE` with your actual Sandbox Client ID
4. Save the file

**Example after update:**
```php
define('PAYPAL_CLIENT_ID', 'AXM1Jk-Dw1z2b3c4d5e6f7g8h9i0j1k2l3m4n5o');
```

### Step 4: Configure Course Pricing
1. In the same `payment_config.php` file, find the `$coursesPricing` array (around line 29)
2. Add your course names and prices:

```php
$coursesPricing = array(
    'default' => 99.00,  // Default price for courses not listed
    
    'Account Management' => 149.00,
    'Absence Management' => 129.00,
    'Conflict Management Training' => 139.00,
    'Crisis Management Training' => 159.00,
    'Effective Vendor Management' => 149.00,
    
    // Add more courses as needed
    // 'Your Course Name' => 99.00,
);
```

⚠️ **IMPORTANT**: Course names must match EXACTLY with the course names in `courses.html`

### Step 5: Test the PayPal Integration
1. Start your local server:
   ```bash
   python -m http.server 8000
   ```
2. Open http://localhost:8000/courses.html
3. Find any course and click "Enroll Now"
4. Fill in the enrollment form:
   - Name: Your Name
   - Email: your@email.com
   - Phone: 1234567890
   - City: Your City
5. Click "Continue to Payment"
6. You should see the PayPal payment button appear
7. Click the PayPal button to complete test payment

**Test Credentials (Sandbox):**
- Use PayPal or Credit Card option
- For PayPal login, use your PayPal Developer account
- For Credit Card: Use test card 4111 1111 1111 1111 with any future expiry date

### Step 6: Go Live with Real Payments
When you're ready for production:

1. In https://developer.paypal.com, switch from **Sandbox** to **Live**
2. Copy your **Live Client ID**
3. Update `payment_config.php`:
   ```php
   define('PAYPAL_CLIENT_ID', 'YOUR_LIVE_CLIENT_ID');
   ```
4. Your system will automatically start processing real payments

---

## 📋 How It Works

### User Flow:
```
1. User visits courses.html
   ↓
2. Clicks "Enroll Now" on any course
   ↓
3. Redirected to enrollment.html with course name
   ↓
4. Fills enrollment form (Name, Email, Phone, City, etc.)
   ↓
5. Clicks "Continue to Payment"
   ↓
6. PayPal payment button appears
   ↓
7. Clicks PayPal button to pay
   ↓
8. Completes payment on PayPal
   ↓
9. Automatically redirected to thank-you.html
   ↓
10. Enrollment record saved with payment details
    Email sent to student and admin
```

### Technical Flow:
```
GET get_enrollment_config.php
  ↓ Returns: Client ID, Currency, Course Prices
  ↓
enrollment.html (Dynamic PayPal SDK loading)
  ↓ User submits form
  ↓
PayPal SDK creates order
  ↓ User approves payment
  ↓
POST process_enrollment.php (with PayPal order ID)
  ↓ Saves enrollment + payment data
  ↓ Sends confirmation emails
  ↓
Redirect to thank-you.html
```

---

## 🔧 File Overview

| File | Purpose |
|------|---------|
| `enrollment.html` | Enrollment form with PayPal button |
| `payment_config.php` | PayPal Client ID + Course pricing |
| `get_enrollment_config.php` | API to serve config to the form |
| `process_enrollment.php` | Backend to save enrollments + send emails |
| `courses.html` | Course listing with "Enroll Now" buttons |

---

## 📊 Enrollment Data Storage

Enrollments are saved to `enrollments.json` in JSON format with:
- Student name, email, phone, city
- Course name and price paid
- PayPal Order ID
- Payment status
- Timestamp

Example record:
```json
{
  "id": "ENR-20250310143022-4756",
  "timestamp": "2025-03-10 14:30:22",
  "fullName": "John Smith",
  "email": "john@example.com",
  "phone": "1234567890",
  "city": "New York",
  "course": "Account Management",
  "company": "ABC Corp",
  "message": "Interested in this course",
  "paypal_order_id": "5O190127070343715",
  "payment_status": "completed",
  "price": 149.00
}
```

---

## 📧 Email Notifications

### Student Receives:
✅ Enrollment confirmation  
✅ Course name and price paid  
✅ Order ID  
✅ Next steps

### Admin Receives:
✅ New enrollment notification  
✅ Full student details  
✅ Payment information  
✅ Call-to-action to follow up

Both emails are sent to the address in `config.php` (ADMIN_EMAIL)

---

## ❓ Troubleshooting

### PayPal Button Not Showing
- Check browser console (F12 > Console tab)
- Make sure `payment_config.php` has a valid Client ID (not the placeholder)
- Verify the Client ID matches your PayPal account

### Payment Shows as "Sandbox" but I Want Real Payments
- You're using a Sandbox Client ID
- Switch to Live Client ID in `payment_config.php`

### Course Price Not Matching
- Check `payment_config.php` - course name must match exactly
- Course names are case-sensitive
- Example: "Account Management" ≠ "account management"

### Emails Not Sending
- Check `config.php` for ADMIN_EMAIL setting
- Verify your server's PHP mail() function is working
- Check error logs

---

## 🎯 Next Steps

1. ✅ Get PayPal Client ID (Sandbox or Live)
2. ✅ Update `payment_config.php` with your Client ID
3. ✅ Add course pricing to `payment_config.php`
4. ✅ Test the enrollment flow
5. ✅ Switch to Live when ready

**That's it!** Your PayPal enrollment system is ready to use.

---

## 📞 Support

For PayPal-specific issues: https://www.paypal.com/support  
For PayPal Developer help: https://developer.paypal.com/support
