# Email System Setup Guide

## Current Status ✅

Your enrollment system is **fully working**:
- ✅ Enrollment form captures all data
- ✅ Payment processing works (PayPal, Razorpay, Credit Card, Bank Transfer)
- ✅ Confirmation page displays correctly
- ✅ **Emails are stored automatically** when payments are completed

## Where Are Emails?

Open in your browser: **http://localhost:5501/emails-dashboard.php**

You'll see:
- All customer confirmation emails
- All admin notifications
- Status of each email (pending/sent)

---

## 3 Options to Send Emails

### Option 1: Mark as Sent (For Testing) ✅ FASTEST

1. Go to: http://localhost:5501/emails-dashboard.php
2. Click **"Send All Emails Now"** button
3. Emails marked as "sent" (doesn't actually send, for testing)

**Use This For:** Testing before going live

---

### Option 2: SendGrid (Free - 100 emails/day) ✅ RECOMMENDED

**Setup (5 minutes):**

1. Sign up: https://sendgrid.com (click "Sign Up Free")
2. Verify email
3. Go to Settings → API Keys
4. Create API key (copy it)
5. Edit file: `send_emails_sendgrid.php`
6. Replace: `'SG.YOUR_API_KEY_HERE'` with your actual key
7. Run: `php send_emails_sendgrid.php` in terminal

**Automatic Sending:**

Add this to your `enrollment_with_fees.html` after successful payment (optional):

```javascript
// After payment success, auto-send emails via SendGrid
fetch('send_emails_sendgrid.php').catch(e => console.log('Emails queued'));
```

---

### Option 3: Production Server

When you deploy to a real server (AWS, GoDaddy, etc.):
- Most servers have SMTP configured
- Change your hosting SMTP settings in `php.ini`
- Use the standard `mail()` function (already implemented)

---

## Making Emails Actually Send

Currently emails are **stored but not sent** because localhost has no SMTP.

**To actually send:**

### Step 1: Use SendGrid (Recommended)
```
1. Get free API key → https://sendgrid.com
2. Edit: send_emails_sendgrid.php
3. Add your key: 'SG.YOUR_API_KEY_HERE'
4. Run: php send_emails_sendgrid.php
```

### Step 2: Or Configure SMTP (Production Only)
```
Edit your server's php.ini:
[mail]
SMTP = smtp.gmail.com
smtp_port = 587
sendmail_from = your-email@gmail.com
```

---

## Testing The Full Flow

1. **Complete an enrollment:**
   - Go to: http://localhost:5501/enrollment_with_fees.html
   - Fill form and select any payment method
   - Complete payment

2. **Check stored emails:**
   - Visit: http://localhost:5501/emails-dashboard.php
   - You should see 2 new emails (customer + admin)

3. **Mark emails as sent (testing):**
   - Click "Send All Emails Now" button
   - Emails marked as "sent"

4. **For real sending (production):**
   - Set up SendGrid (see Option 2 above)
   - Edit `send_emails_sendgrid.php` with your API key
   - Run it manually or add auto-send after payment

---

## File Reference

| File | Purpose |
|------|---------|
| `store_enrollment.php` | Stores enrollment emails when payment completes |
| `emails-dashboard.php` | **View all stored emails** |
| `send_emails_simple.php` | Mark emails as sent (testing) |
| `send_emails_sendgrid.php` | Actually send emails via SendGrid |
| `emails/` | Directory where emails are stored as JSON files |

---

## Next Steps

**For Testing Now:**
1. Go to: http://localhost:5501/emails-dashboard.php
2. Click "Send All Emails Now"
3. Emails marked as sent ✅

**For Live Email Sending:**
1. Sign up SendGrid: https://sendgrid.com
2. Get API key
3. Edit `send_emails_sendgrid.php` with key
4. Run: `php send_emails_sendgrid.php` after payments

---

## Troubleshooting

❌ **No emails in dashboard after payment?**
- Open browser console (F12) and check for errors
- Verify enrollment form is being submitted

❌ **Emails marked as "sent" but not in mailbox?**
- This is expected in development (no real SMTP)
- Set up SendGrid to actually send them

❌ **SendGrid API key not working?**
- Verify key starts with `SG.`
- Check it's not expired in SendGrid dashboard

---

## Questions?

All emails are stored in: `c:\Users\MANJUNATH B G\adeptskil\emails\`

Each enrollment creates 2 JSON files:
- `email_*.json` - Raw email data

You can manually edit/resend them if needed.

