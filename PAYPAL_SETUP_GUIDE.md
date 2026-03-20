# PayPal Integration Setup Guide for Adeptskil

## Overview
This guide explains how to set up PayPal payment integration for course enrollment. The system now includes:
- ✅ Full enrollment form (Name, Email, Phone, City, Company, Message)
- ✅ PayPal payment processing
- ✅ Automated emails to both student and admin
- ✅ Payment confirmation tracking

## Step 1: Create/Configure PayPal Business Account

1. **Create PayPal Account** (if you don't have one):
   - Go to https://www.paypal.com
   - Click "Sign Up" and choose "Business" account
   - Complete the registration process

2. **Access PayPal Developer Dashboard**:
   - Visit https://developer.paypal.com
   - Log in with your PayPal account
   - Click "Dashboard" from the top menu

## Step 2: Get Your Client ID

1. **Locate Credentials**:
   - In the Dashboard, go to **Apps & Credentials** (left sidebar)
   - Select **Sandbox** dropdown (for testing) or **Live** (for production)
   - Under "REST API signature", you'll see your credentials
   - Copy the **Client ID** (starts with `AXM...` or similar)

2. **For Testing First** (RECOMMENDED):
   - Use **Sandbox** mode to test the integration
   - Go to **Sandbox** > **Accounts** tab
   - You'll have test accounts created automatically
   - Use these to test the payment flow

## Step 3: Update enrollment.html with Your Client ID

Open `enrollment.html` and find this line (near the top of the `<script>` section):

```html
<script src="https://www.paypal.com/sdk/js?client-id=YOUR_PAYPAL_CLIENT_ID&currency=USD"></script>
```

Replace `YOUR_PAYPAL_CLIENT_ID` with your actual Client ID:

```html
<script src="https://www.paypal.com/sdk/js?client-id=AXM1234567890abcdefghijklmnop&currency=USD"></script>
```

## Step 4: Set Course Pricing

In `enrollment.html`, find the `coursePrices` object in the JavaScript section:

```javascript
const coursePrices = {
    'default': 99.00,
    // Add your courses with prices here
    // 'Python Programming': 149.00,
    // 'Leadership Skills': 199.00,
};
```

Add your course names and prices:

```javascript
const coursePrices = {
    'default': 99.00,
    'Python Programming': 149.00,
    'Leadership Skills': 199.00,
    'Advanced Management': 249.00,
    'Digital Marketing Basics': 79.00
};
```

**Make sure the course names match exactly** with the course names in `courses.html`.

## Step 5: Test the Integration (Sandbox Mode)

1. **Keep Client ID in Sandbox Mode**:
   - Don't change to Live mode yet
   - Use the Sandbox Client ID for testing

2. **Test Payment Flow**:
   - Open your website locally: `http://localhost:8000`
   - Click "Enroll Now" on any course
   - Fill in the enrollment form with any details
   - Click "Continue to Payment"
   - PayPal checkout should appear
   - Use PayPal Sandbox test credentials to complete payment:
     - **Buyer Account**: Check your PayPal Developer Dashboard > Sandbox > Accounts
     - Use the pre-created test buyer account
     - Any password works (usually test account details are provided)

3. **Check Enrollment Records**:
   - Enrollments are saved to `enrollments.json`
   - Check your email inbox for confirmation emails
   - Admin should receive detailed enrollment notification

## Step 6: Go Live (Production)

When ready for real payments:

1. **Switch to Live Client ID**:
   - In PayPal Dashboard, switch from **Sandbox** to **Live**
   - Copy your Live Client ID
   - Update `enrollment.html` with the Live Client ID

2. **Test with Real Payments**:
   - Make a test enrollment with real payment
   - Verify funds appear in your PayPal account
   - Check you're receiving emails correctly

## Understanding the New Form Flow

### User Perspective:
1. Fill out enrollment form with all details
2. Click "Continue to Payment"
3. PayPal payment window appears
4. Complete payment via PayPal
5. Redirected to thank you page
6. Receives confirmation email

### Form Fields:
- **Name** (required) - Student's full name
- **Email** (required) - For sending confirmations
- **Phone** (required) - Contact information
- **City** (required) - Student location
- **Company** (optional) - Company/organization name
- **Message** (optional) - Additional questions/notes

### System Records:
All enrollment data is saved with:
- Student details (name, email, phone, city)
- Course information and price
- PayPal transaction ID
- Payment status
- Timestamp

## Email Notifications

### Student Receives:
- Course name and enrollment details
- Confirmation of payment
- PayPal order ID
- Enrollment ID for reference
- Admin contact information

### Admin Receives:
- All student details
- Course details and price
- Payment information
- Student's additional message
- Timestamp and enrollment ID

## File Changes Summary

### Updated Files:
1. **enrollment.html** - Added PayPal SDK, payment section, city field
2. **process_enrollment.php** - Updated to handle city and payment data

### New Features:
- PayPal Smart Buttons integration
- City field in enrollment form
- Enhanced email notifications
- Payment tracking in enrollment records

## Testing Checklist

- [ ] Client ID is correct in enrollment.html
- [ ] Course prices match course names
- [ ] Can access enrollment form
- [ ] Form validation works (required fields)
- [ ] Payment section appears after form submission
- [ ] PayPal button displays correctly
- [ ] Can complete test payment in Sandbox
- [ ] Confirmation email received (student)
- [ ] Admin notification received
- [ ] Enrollment data saved to enrollments.json
- [ ] Ready to switch to Live mode

## Troubleshooting

### PayPal Button Not Showing
- Check browser console for errors (F12)
- Verify Client ID in enrollment.html matches your actual ID
- Ensure you're using correct Sandbox/Live mode

### Emails Not Sending
- Check `config.php` for correct ADMIN_EMAIL
- Verify MAIL_METHOD is set to 'file' or 'php'
- Check `emails/` directory for saved emails

### Payment Fails
- In Sandbox, use test account credentials
- Check PayPal dashboard for transaction details
- Review browser console for error messages

## Currency Settings

The system is configured for **USD**. To change:
1. In `enrollment.html`, find: `&currency=USD`
2. Change to your currency code (e.g., `&currency=EUR` for Euros)
3. Update course prices in `coursePrices` object

Common currency codes:
- USD = US Dollar
- EUR = Euro
- GBP = British Pound
- INR = Indian Rupee
- AUD = Australian Dollar

## Security Notes

- Client ID (public) is safe to have in frontend code
- Never expose your Client Secret (keep in backend only)
- All transactions go through PayPal's secure servers
- Student data is encrypted in transit
- Email addresses validated before sending

## Support

For PayPal API questions:
- PayPal Developer: https://developer.paypal.com/docs/
- PayPal Support: https://www.paypal.com/support

For Adeptskil questions:
- Check process_enrollment.php for data handling
- Review config.php for email settings
- Check emails/ directory for sent emails

---

**Last Updated**: March 2026
**Version**: 1.0 - PayPal Integration Complete
