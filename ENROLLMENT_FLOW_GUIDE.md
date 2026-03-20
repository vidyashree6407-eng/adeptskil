# Complete Enrollment & Payment Flow - Visual Guide

## 📱 USER PERSPECTIVE

```
┌─────────────────────────────────────────────────────────────┐
│  1. BROWSE COURSES (courses.html)                           │
│  ┌─────────────────────────────────────────────────────┐   │
│  │ [Python Programming] [Leadership] [Management] ... │   │
│  └─────────────────────────────────────────────────────┘   │
│  ↓ Click "Enroll Now" button                                │
└─────────────────────────────────────────────────────────────┘
                    ↓
        ┌───────────────────────────┐
        │ 2. ENROLLMENT FORM LOADS   │
        │ (enrollment.html)          │
        └───────────────────────────┘
                    ↓
        ╔═══════════════════════════════════════════╗
        ║     ENROLLMENT FORM - STEP 1              ║
        ║───────────────────────────────────────────║
        ║                                           ║
        ║  Selected Course: Python Programming      ║
        ║                                           ║
        ║  Full Name:        [________________]     ║
        ║  Email:            [________________]     ║
        ║  Phone:            [________________]     ║
        ║  City:             [________________]  ⭐ NEW
        ║  Company:          [________________]     ║
        ║  Message:          [________________]     ║
        ║                                           ║
        ║  [Continue to Payment →]                  ║
        ║                                           ║
        ║ (*) = Required fields                     ║
        ╚═══════════════════════════════════════════╝
                    ↓
        Form validation on submit
                    ↓
        ┌───────────────────────────┐
        │ 3. PAYMENT SECTION APPEARS │
        │                           │
        │ ✓ Form hidden/locked      │
        │ ✓ Payment section visible │
        └───────────────────────────┘
                    ↓
        ╔════════════════════════════════════════╗
        ║   PAYMENT METHOD - STEP 2              ║
        ║────────────────────────────────────────║
        ║                                        ║
        ║   💳 Payment Method                    ║
        ║                                        ║
        ║   Course Fee: $ 149.00                 ║
        ║                                        ║
        ║   ┌──────────────────────────────┐   ║
        ║   │  [PayPal Button]             │   ║
        ║   │  □ Click to Pay with PayPal  │   ║
        ║   └──────────────────────────────┘   ║
        ║                                        ║
        ║   Secure payment via PayPal            ║
        ║                                        ║
        ╚════════════════════════════════════════╝
                    ↓
        PayPal Button Clicked
                    ↓
        ╔════════════════════════════════════════╗
        ║   PAYPAL CHECKOUT (PayPal.com)        ║
        ║────────────────────────────────────────║
        ║                                        ║
        ║  Your PayPal account will popup...    ║
        ║  [Enter credentials and authorize]    ║
        ║                                        ║
        ║  Adeptskil wants to charge:            ║
        ║  $149.00 USD for course enrollment    ║
        ║                                        ║
        ║  [Approve & Pay] [Cancel]             ║
        ║                                        ║
        ╚════════════════════════════════════════╝
                    ↓
        Payment Authorized & Approved
                    ↓
        ┌───────────────────────────────┐
        │ 4. SERVER PROCESSES ENROLLMENT │
        │ (process_enrollment.php)      │
        └───────────────────────────────┘
                    ↓
        📝 DATA SAVED to enrollments.json
        │
        ├─ Student info (name, email, phone, city)
        ├─ Course details & price
        ├─ PayPal Order ID
        ├─ Payment status: completed
        └─ Timestamp
                    ↓
        ✉️  EMAILS SENT
        │
        ├─ ✅ Email to STUDENT
        │   └─ "Thank you for enrolling!"
        │
        └─ ✅ Email to ADMIN
            └─ "New course enrollment received"
                    ↓
        ┌─────────────────────────────┐
        │ 5. THANK YOU PAGE (Success) │
        │ (thank-you.html)            │
        └─────────────────────────────┘
                    ↓
        ╔═════════════════════════════════════════╗
        ║  ✅ ENROLLMENT SUCCESSFUL!              ║
        ║                                         ║
        ║  Dear John Doe,                         ║
        ║                                         ║
        ║  Thank you for enrolling in:            ║
        ║  Python Programming                     ║
        ║                                         ║
        ║  Payment Status: ✓ Completed            ║
        ║  Amount: $149.00                        ║
        ║                                         ║
        ║  Confirmation email sent to:            ║
        ║  john@example.com                       ║
        ║                                         ║
        ║  [Return to Courses]                    ║
        ╚═════════════════════════════════════════╝
                    ↓
    Enrollment Complete! 🎉
```

---

## 🔄 BACKEND FLOW (Server-Side)

```
┌──────────────────────────────────────┐
│ enrollment.html (JavaScript)         │
│ - Validates form fields              │
│ - Integrates with PayPal SDK         │
│ - Collects payment from PayPal       │
│ - POSTs data to backend              │
└──────────────────────────────────────┘
                ↓
        HTTP POST Request
        Content-Type: application/json
        Body: {
            fullName: "John Doe",
            email: "john@example.com",
            phone: "+1-555-123-4567",
            city: "New York",
            course: "Python Programming",
            company: "Tech Corp",
            message: "Excited to learn!",
            paypal_order_id: "2VR12345678901234E",
            payment_status: "completed",
            price: 149.00
        }
                ↓
┌──────────────────────────────────────┐
│ process_enrollment.php               │
│ - Receives JSON POST data            │
│ - Validates all fields               │
│ - Sanitizes input data               │
│ - Generates enrollment ID            │
└──────────────────────────────────────┘
                ↓
        ┌─ Read existing enrollments
        │  from enrollments.json
        │
        ├─ Add new enrollment to array
        │
        └─ Write back to file
                ↓
        ✅ enrollments.json updated
        (Enrollment ID: ENR-20260306143015-5432)
                ↓
        ┌─────────────────────────────┐
        │ sendEmail() - Student       │
        │ FROM: info@adeptskil.com    │
        │ TO: john@example.com        │
        │ SUBJECT: Enrollment         │
        │          Confirmation       │
        └─────────────────────────────┘
                ↓
        Email saved to:
        emails/EMAIL-20260306143015-12345.json
                ↓
        (Email also sent via PHP mail() if configured)
                ↓
        ┌─────────────────────────────┐
        │ sendEmail() - Admin         │
        │ FROM: info@adeptskil.com    │
        │ TO: info@adeptskil.com      │
        │ SUBJECT: New Enrollment     │
        └─────────────────────────────┘
                ↓
        Email saved to:
        emails/EMAIL-20260306143015-54321.json
                ↓
        (Email also sent via PHP mail() if configured)
                ↓
        ┌──────────────────────────────────────┐
        │ Return JSON Response                 │
        │ {                                    │
        │   "success": true,                   │
        │   "message": "Enrollment successful",│
        │   "enrollment_id": "ENR-...",        │
        │   "name": "John Doe"                 │
        │ }                                    │
        └──────────────────────────────────────┘
                ↓
        JavaScript receives response
                ↓
        Redirect to thank-you.html
```

---

## 💾 DATA STRUCTURE

### enrollments.json (Data Storage)
```json
[
  {
    "id": "ENR-20260306143015-5432",
    "timestamp": "2026-03-06 14:30:15",
    "fullName": "John Doe",
    "email": "john@example.com",
    "phone": "+1-555-123-4567",
    "city": "New York",
    "course": "Python Programming",
    "company": "Tech Corp",
    "message": "I'm interested in this course",
    "paypal_order_id": "2VR12345678901234E",
    "payment_status": "completed",
    "price": 149.00
  },
  {
    "id": "ENR-20260306144530-8901",
    "timestamp": "2026-03-06 14:45:30",
    "fullName": "Jane Smith",
    "email": "jane@example.com",
    "phone": "+1-555-987-6543",
    "city": "Los Angeles",
    "course": "Leadership Skills",
    "company": "Marketing Corp",
    "message": "",
    "paypal_order_id": "5DB98765432109876E",
    "payment_status": "completed",
    "price": 199.00
  }
]
```

---

## 📧 EMAIL TEMPLATES

### Email 1: Student Confirmation
```
TO: john@example.com
SUBJECT: Course Enrollment Confirmation - Python Programming

---

Dear John Doe,

Thank you for enrolling in our course and completing your payment!

==== ENROLLMENT DETAILS ====
Name: John Doe
Email: john@example.com
Phone: +1-555-123-4567
City: New York
Course: Python Programming
Course Fee: $149.00
Company: Tech Corp
Payment Status: Completed
Order ID: 2VR12345678901234E
Enrollment ID: ENR-20260306143015-5432

We have successfully received your enrollment for: Python Programming

Your payment of $149.00 has been processed successfully.

Our team will review your application and contact you shortly at +1-555-123-4567 
with the next steps and course details.

If you have any questions, feel free to reach out to us.

Best regards,
Adeptskil Training Team
Email: info@adeptskil.com
```

### Email 2: Admin Notification
```
TO: info@adeptskil.com
SUBJECT: New Course Enrollment - Python Programming [John Doe]

---

New course enrollment received!

==== STUDENT DETAILS ====
Name: John Doe
Email: john@example.com
Phone: +1-555-123-4567
City: New York
Company: Tech Corp

==== ENROLLMENT DETAILS ====
Course: Python Programming
Enrollment ID: ENR-20260306143015-5432
Enrollment Time: 2026-03-06 14:30:15
Payment Status: completed
Course Fee: $149.00
PayPal Order ID: 2VR12345678901234E

==== ADDITIONAL MESSAGE ====
I'm interested in this course

---

Please follow up with the student to confirm enrollment and provide course materials.
```

---

## 🎯 KEY FORM FIELDS & VALIDATION

| Field | Type | Required | Validation | Example |
|-------|------|----------|-----------|---------|
| Full Name | Text | ✓ Yes | Non-empty | John Doe |
| Email | Email | ✓ Yes | Valid email format | john@example.com |
| Phone | Text | ✓ Yes | Non-empty | +1-555-123-4567 |
| City | Text | ✓ Yes | Non-empty | New York |
| Company | Text | No | (optional) | Tech Corp |
| Message | Textarea | No | (optional) | Any text |
| Course | Hidden | ✓ Yes | Auto-filled | Python Programming |

---

## 🔐 PAYMENT DETAILS

| Detail | Value | Example |
|--------|-------|---------|
| Payment Provider | PayPal | PayPal.com |
| Currency | USD (configurable) | $149.00 |
| Transaction ID | PayPal Order ID | 2VR12345678901234E |
| Fee Charged | From coursePrices config | $149.00 |
| Payment Status | "completed" | completed |

---

## ✅ VERIFICATION CHECKLIST

After a test enrollment, verify:

- [ ] Enrollment appears in enrollments.json
- [ ] Student details are correct
- [ ] PayPal Order ID is recorded
- [ ] Price matches the course price
- [ ] Payment status shows "completed"
- [ ] Timestamp is accurate
- [ ] Student received confirmation email
- [ ] Admin received notification email
- [ ] Both emails contain all details

---

**Status**: Complete Ready-to-Use Implementation
**Created**: March 2026
