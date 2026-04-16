# 📊 VISUAL GUIDES & DIAGRAMS

## Email Confirmation Flow (Fixed Version)

```
┌─────────────────────────────────────────────────────────────────────────┐
│                    PAYMENT → CONFIRMATION FLOW                          │
└─────────────────────────────────────────────────────────────────────────┘

                           BEFORE (❌ BROKEN)
    
    Customer Payment          IPN Sent              Confirmation
    on PayPal                 to Server             Email
         │                         │                    │
         V                         V                    X
    ┌─────────┐  ❌         ┌──────────┐      ❌  ┌──────────┐
    │ PayPal  │ NO          │ Server   │ NO    EMAIL SENT
    │ Notified│ notify_url  │ Received │ database
    │         │             │ IPN      │       lookup
    └─────────┘             └──────────┘       └──────────┘
                                 │
                                 V
                            LOG NOTHING
                            
    Result: ❌ No email (customer confused)

─────────────────────────────────────────────────────────────────────────

                          AFTER (✅ FIXED)
    
    Customer         Payment    Enrollment    PayPal     IPN        Email
    Fills Form       Saved      Shows         Processes  Received   Sent
    on Form          to DB      Payment       Payment    with       to
    │                │          Form          │          Invoice    Customer
    V                V          │             V          │          │
    
    ┌─────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐
    │ Form    │→ │ STORE    │  │ PayPal   │→ │ PayPal   │→ │ Server   │
    │ Submit  │  │ DATABASE │  │ Form     │  │ Complete │  │ IPN      │
    │ (Step1) │  │ (Step2)  │  │ Ready    │  │ Payment  │  │ Handler  │
    └─────────┘  └──────────┘  (Step3)    │  └──────────┘  └──────────┘
                                │          │       │             │
                                └──────────┘       │             V
                                Continue to          │      ┌──────────┐
                                PayPal           ┌────────→ │ LOOKUP   │
                                                 │    notify_url│ DATABASE │
                                              invoice_id │ FIND EMAIL│
                                                 │    (Step4) └──────────┘
                                                 │               │
                                                 └───────────────┤
                                                       │          V
                                                       │    ┌──────────┐
                                                       └───→│ SEND     │
                                                            │ SMTP     │
                                                            │ EMAIL    │
                                                            └──────────┘
                                                                 │
                                                                 V
                                                            ✅ SUCCESS
                                                    Customer receives email
```

---

## Testing Flow Diagram

```
┌──────────────────────────────────────────────────────────────┐
│             YOUR TESTING WORKFLOW (15 min)                   │
└──────────────────────────────────────────────────────────────┘

PHASE 1: Verify Setup
┌─────────────────────────┐
│ Access Diagnostic Tool  │ → https://adeptskil.com/diagnostic-email.php
│ Check Section 1         │ → Should show: ✓ SMTP + PHPMailer
│ Verify config           │ → GoDaddy SMTP settings correct
└─────────────────────────┘ (2 min)
         │
         V
       ✅ OK? Continue
       ❌ Error? → Fix config in config.php


PHASE 2: Test SMTP
┌─────────────────────────┐
│ Section 6: Send Email   │ → Enter your-email@example.com
│ Send test email         │ → Click "Send Test Email"
│ Wait 10 seconds         │ → Check inbox + SPAM
│ Check for arrival       │
└─────────────────────────┘ (3 min)
         │
         V
       ✅ Email received? → SMTP works!
       ❌ No email? → Check Section 4 for error


PHASE 3: Test Enrollment
┌─────────────────────────┐
│ Go to enrollment form   │ → /enrollment.html
│ Fill all fields         │ → Use same email from Phase 2
│ Submit form             │ → Click "Continue to Payment"
│ Verify in database      │ → Check Diagnostic Section 2
└─────────────────────────┘ (4 min)
         │
         V
       ✅ Enrollment appears in DB? → Continue
       ❌ Not in DB? → Check browser console (F12)


PHASE 4: Test Payment
┌─────────────────────────┐
│ On PayPal form          │ → Click "Pay with PayPal"
│ Complete payment        │ → Login & finish payment
│ Check IPN log           │ → Diagnostic Section 5
│ Verify emails triggered │ → Should see "📧 Sending"
│ Check confirmation      │ → Inbox + SPAM folder
└─────────────────────────┘ (5 min)
         │
         V
       ✅ All steps complete + email received?
         → SUCCESS! 🎉 System working!
         
       ❌ Email not received?
         → Check Section 4 & 5 for errors
         → Check SPAM folder
         → Whitelist adeptskil.com domain
```

---

## Diagnostic Dashboard Guide

```
┌─────────────────────────────────────────────────────────────┐
│    DIAGNOSTIC DASHBOARD - What Each Section Shows           │
└─────────────────────────────────────────────────────────────┘

Section 1: Configuration Status
┌──────────────────────────┐
│ ✓ MAIL_METHOD = smtp     │  ← Should always be "smtp"
│ ✓ SMTP_HOST = GoDaddy    │  ← smtpout.secureserver.net
│ ✓ SMTP_PORT = 465        │  ← Standard for SSL
│ ✓ PHPMailer found        │  ← Critical dependency
└──────────────────────────┘
  Use: Verify initial setup


Section 2: Database & Enrollments
┌──────────────────────────┐
│ Total Enrollments: [N]   │  ← Should increase after each test
│ Recent Enrollments:      │
│  • Invoice ID            │  ← Generated by system
│  • Email                 │  ← Should match form input
│  • Course                │  ← Should match selected course
│  • Status: pending/...   │  ← Changes after payment
└──────────────────────────┘
  Use: Verify data is being saved


Section 3: Recent Email Logs
┌──────────────────────────┐
│ Last 5 emails sent:      │  ← Shows recent email attempts
│ • To: [email]            │  ← Recipient address
│ • Subject: [subject]     │  ← Email subject line
│ • Sent: [timestamp]      │  ← When email was sent
└──────────────────────────┘
  Use: Verify emails were queued


Section 4: SMTP Debug Log
┌──────────────────────────┐
│ ✓ EMAIL SENT via SMTP    │  ← Success message
│ ✗ FAILED: SSL Error      │  ← Error message (if any)
│ [timestamp] messages     │  ← Last entries first
└──────────────────────────┘
  Use: Debug SMTP connection issues


Section 5: IPN Handler Log
┌──────────────────────────┐
│ [timestamp] IPN Started   │  ← PayPal webhook received
│ [timestamp] Enrollment   │  ← Found enrollment in DB
│ [timestamp] Sending...   │  ← Triggered email sending
└──────────────────────────┘
  Use: Debug payment processing


Section 6: Send Test Email
┌──────────────────────────┐
│ Input: email@example.com │  ← Your email address
│ Send: 📧 Send Email      │  ← Triggers test email
│ Result: ✓ Sent / ✗ Error│  ← Immediate feedback
└──────────────────────────┘
  Use: Quick SMTP verification


Section 7: Simulate Payment
┌──────────────────────────┐
│ Simulate: Payment IPN    │  ← Trigger email without PayPal
│ Target: Last pending     │  ← Uses latest enrollment
│ Result: Emails sent      │  ← Confirmation message
└──────────────────────────┘
  Use: Test without payment
```

---

## Error Message Reference

```
When you see this...          It means...           What to do...

✗ PHPMailer not found        SMTP library missing   Upload /PHPMailer folder

✗ SSL Error                  Certificate issue     Update config.php line 31
                                                   Check SSL verification

✗ Auth Error                 Wrong password        Verify GoDaddy SMTP password
                                                   config.php line 30

✗ Connection timeout         Server unreachable    Check GoDaddy SMTP is active
                                                   Check firewall settings

✗ ENROLLMENT NOT FOUND       Payment received      Check database has enrollment
                             but no record         (enrollment.html step 2 failed)
                             in database

❌ No IPN log entries        PayPal not sending    Check PayPal IPN config
                             webhook               Verify notify_url in form

✗ Email in SPAM              Provider filtering    Whitelist adeptskil.com domain
                                                   Check SPF/DKIM records

[No entries]                 Test not run yet      Press F5 to refresh dashboard
(blank log)                                       Or complete next test step
```

---

## Success Indicators

```
✅ All Working = You'll See:

Test Email Phase:
  Section 4: ✓ EMAIL SENT via SMTP to: your-email@...
  Your Inbox: Email arrives within 10 seconds

Enrollment Phase:
  Section 2: Your test record in "Recent Enrollments"  
  Status: pending
  Email: Matches form input

Payment Phase:
  Section 5: ✓ IPN Handler started
  Section 5: ✓ Enrollment FOUND
  Section 5: 📧 Sending confirmation emails
  Your Inbox: Confirmation email arrives (30 sec later)

Final:
  Database status changes to: completed
  Customer is happy! 😊
```

---

## Troubleshooting Decision Tree

```
START: Email not received
   │
   ├─→ Did test email send? (Section 6)
   │   │
   │   ├─→ NO → Check Section 4 (SMTP Debug Log)
   │   │        └─→ Look for ✗ errors
   │   │           └─→ SSL/Auth/Connection?
   │   │              └─→ Fix SMTP credentials
   │   │
   │   └─→ YES → Continue to next
   │
   ├─→ Did enrollment save? (Section 2)
   │   │
   │   ├─→ NO → Form validation failed
   │   │        └─→ Check F12 Console errors
   │   │
   │   └─→ YES → Continue to next
   │
   ├─→ Did PayPal process? (Check PayPal account)
   │   │
   │   ├─→ NO → PayPal payment failed
   │   │        └─→ User needs to retry payment
   │   │
   │   └─→ YES → Continue to next
   │
   ├─→ Did IPN trigger? (Section 5)
   │   │
   │   ├─→ NO → PayPal IPN not configured
   │   │        └─→ Add IPN webhook in PayPal
   │   │        └─→ Test notify_url connection
   │   │
   │   └─→ YES → Continue to next
   │
   ├─→ Check email SPAM/JUNK folder
   │   │
   │   ├─→ Found there? → Whitelist domain
   │   │
   │   └─→ Not found? → Check SMTP & IPN logs

  END: Issue identified → Fix accordingly
```

---

## Quick Status Checklist

```
Before Testing:
  ☐ All files uploaded to server
  ☐ diagnostic-email.php is accessible
  ☐ PHPMailer folder exists at /PHPMailer/

During Testing:
  ☐ Section 4 shows ✓ EMAIL SENT (not ✗ errors)
  ☐ Database shows test enrollment with "pending" status
  ☐ PayPal payment completes successfully
  ☐ Section 5 shows ✓ Enrollment FOUND
  ☐ Section 5 shows 📧 Sending confirmation emails

After Testing:
  ☐ Confirmation email received (or in SPAM)
  ☐ Database shows enrollment with "completed" status
  ☐ Real customer receives confirmation after real payment

If all checked: ✅ SUCCESS! System is working!
```

---

**Ready to test? Go to:** https://adeptskil.com/diagnostic-email.php
