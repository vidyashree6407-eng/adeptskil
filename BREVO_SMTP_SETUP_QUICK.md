# Brevo SMTP Setup Guide - 5 Minutes to Real Emails

## 📧 What You'll Get
- ✅ Real emails sent to users
- ✅ 300 free emails per day
- ✅ Professional delivery
- ✅ No spam folders
- ✅ Delivery tracking

---

## 🚀 Step 1: Sign Up (2 minutes)

### Go to Brevo:
```
https://brevo.com/
```

### Click "Sign Up Free"
- Email: your-email@example.com
- Password: Create one
- Company: Adeptskil
- Phone: Your number

### Check email & verify

---

## 🔑 Step 2: Get SMTP Credentials (2 minutes)

### After login, go to:
```
Settings → SMTP & API
```

### Copy these 4 things:
```
SMTP Host:     smtp-relay.brevo.com
SMTP Port:     587
SMTP Username: your-email@example.com (or provided username)
SMTP Password: xxxxxxxxxxxxxxxxxx (your API key)
```

---

## ⚙️ Step 3: Update Your Code (1 minute)

### Open: config.php

### Find line 16:
```php
define('MAIL_METHOD', 'file');
```

### Change to:
```php
define('MAIL_METHOD', 'smtp');
define('SMTP_HOST', 'smtp-relay.brevo.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@example.com');  // From Brevo
define('SMTP_PASSWORD', 'your-api-key');             // From Brevo
define('SMTP_FROM_EMAIL', 'info@adeptskil.com');     // Keep this
define('SMTP_FROM_NAME', 'Adeptskil');               // Keep this
```

### Complete example:
```php
// Email Configuration
define('ADMIN_EMAIL', 'info@adeptskil.com');
define('SITE_NAME', 'Adeptskil');
define('SITE_URL', 'https://adeptskil.com');

// SMTP Configuration - Brevo
define('MAIL_METHOD', 'smtp');
define('SMTP_HOST', 'smtp-relay.brevo.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-brevo-email@example.com');
define('SMTP_PASSWORD', 'your-brevo-api-key');
define('SMTP_FROM_EMAIL', 'info@adeptskil.com');
define('SMTP_FROM_NAME', 'Adeptskil');

// Rest of config stays the same...
```

---

## ✅ Step 4: Test It

### Method 1: Test with Contact Form
1. Go to Contact page
2. Fill out form
3. Submit
4. Should see "Message received" confirmation
5. Check admin email - should receive notification

### Method 2: Manual Test
```php
// Add this to a test file temporarily
require_once(__DIR__ . '/config.php');

$result = sendEmail(
    'your-email@example.com',
    'Test Email from Adeptskil',
    'If you see this, emails are working!'
);

if ($result) {
    echo "✅ Email sent successfully!";
} else {
    echo "❌ Email failed to send";
}
```

---

## ✅ Expected Results

### What You'll See:
1. **Admin Dashboard** → Emails section shows emails
2. **Your Inbox** → Receives real emails
3. **Mail Log** → `/mail_log.txt` shows activity
4. **Brevo Dashboard** → Shows sent/delivered emails

### When Student Enrolls:
1. ✅ Student gets enrollment confirmation
2. ✅ Admin gets notification
3. ✅ Both emails in Brevo dashboard
4. ✅ Both emails in admin `/emails/` directory

---

## 🆘 Troubleshooting

### "SMTP Connection Failed"
**Check:**
- Your internet connection
- Firewall/VPN not blocking port 587
- Credentials are exactly correct (copy-paste recommended)

**Fix:**
```php
// Add debug to config.php temporarily
error_log("SMTP connecting to: " . SMTP_HOST . ":" . SMTP_PORT);
```

### "Authentication Failed"
**Check:**
- Username is correct (usually email address from Brevo)
- Password/API key is complete and correct
- No extra spaces

**Fix:**
- Delete from Brevo and create new API key

### Emails Still Not Sending
**Check in order:**
1. Is `MAIL_METHOD` set to `'smtp'`?
2. Are SMTP_HOST and SMTP_PORT correct?
3. Check `/mail_log.txt` for error messages
4. Check Brevo dashboard for bounces

---

## 📊 Usage Tracking

### Monitor in Brevo Dashboard:
```
Dashboard → Analytics
Shows:
- Emails sent: X
- Opened: Y%
- Clicked: Z%
- Bounced: W
- Complaints: V
```

### Your Log Files:
```
/mail_log.txt          ← All email attempts
/ipn_log.txt          ← PayPal payments
/emails/              ← Stored email copies
```

---

## 💡 Tips & Best Practices

### Don't forget to update:
- [ ] SMTP_USERNAME - from Brevo account
- [ ] SMTP_PASSWORD - from Brevo API
- [ ] SMTP_FROM_EMAIL - should be info@adeptskil.com

### Security:
- ⚠️ **NEVER** commit config.php with real credentials to GitHub
- Add to .gitignore:
  ```
  config.php
  .env
  *.key
  ```

### Testing:
- Test locally first
- Use test email addresses
- Check spam folder first time

### Monitoring:
- Check Brevo dashboard weekly
- Monitor bounce/complaint rates
- Update email lists if high bounces

---

## ❓ FAQ

### Q: Do I need to change my code?
**A:** No! Your `sendEmail()` function stays the same. Only config changes.

### Q: What if I exceed 300/day?
**A:** Brevo will queue emails (queue up all day, send overnight) or upgrade plan ($20+).

### Q: Can I use any email address?
**A:** Yes, but SPF/DKIM better with domain email (info@adeptskil.com recommended).

### Q: Do I need PHP extensions?
**A:** No, uses standard sockets (fsockopen/stream functions).

### Q: Can I go back to file-based?
**A:** Yes! Just change MAIL_METHOD back to 'file' in config.php

### Q: What if Brevo goes down?
**A:** Emails queue and retry. Your admin dashboard emails still stored locally.

---

## 🎉 Done!

After these 4 steps:
- ✅ Real emails sending to users
- ✅ Admin gets notifications
- ✅ Professional email delivery
- ✅ No spam folder issues
- ✅ Can track opens/clicks (in Brevo)
- ✅ 300 free emails per day

---

## 📞 Support

- **Brevo Help:** https://help.brevo.com/
- **Your Logs:** Check `/mail_log.txt` for details
- **Brevo Dashboard:** https://app.brevo.com/dashboard/

---

**Total Setup Time:** 5 minutes ⏱️
**Cost:** $0/month (free tier)
**Emails/month:** ~10,000 free
