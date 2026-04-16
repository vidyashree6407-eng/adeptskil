# Adeptskil Website

## 📧 EMAIL CONFIRMATION FIX (April 16, 2026)

**Status:** ✅ All fixes implemented, ready for testing

### 🎯 If you're here to TEST the email system:

**Choose a guide below and start testing in 15 minutes:**

- **⚡ Ultra-Quick (3 steps):** [`DO_THIS_NOW.md`](DO_THIS_NOW.md)
- **📋 Full Guide (4 phases):** [`STEP_BY_STEP_GUIDE.md`](STEP_BY_STEP_GUIDE.md)
- **⚙️ Quick Reference:** [`QUICK_REFERENCE.md`](QUICK_REFERENCE.md)
- **📊 Visual Diagrams:** [`VISUAL_GUIDES.md`](VISUAL_GUIDES.md)

**Main testing tool:** https://adeptskil.com/diagnostic-email.php

---

## 🔧 WHAT WAS FIXED

Users weren't receiving confirmation emails after PayPal payment. **Root causes:**
- ❌ PayPal form missing `notify_url` parameter
- ❌ Enrollment not saved to database before payment
- ❌ Poor error logging for debugging
- ❌ Fallback to unreliable `mail()` function

**Fixes applied:**
- ✅ Added `notify_url` to PayPal form
- ✅ Added automatic database storage before payment
- ✅ Enhanced SMTP + IPN logging
- ✅ Switched to **SMTP + PHPMailer ONLY** (no mail() fallback)

**Files modified:**
- `enrollment.html` - notify_url + database storage
- `config.php` - SMTP-only sending
- `ipn_handler.php` - Better logging
- `diagnostic-email.php` - New testing dashboard

---

## 📁 Course Management (Original Documentation)

Adeptskil website

- Canonical courses page: `courses.html` — this repository uses `courses.html` as the single source of truth for the course catalog.
- `courses_fixed.html` was removed to avoid duplication; if you need a backup, use git history.

Notes:
- Course cards use icon placeholders (graduation-cap) for a consistent lightweight layout.
- If you want images restored, update `courses.html` and add image URLs inside `.course-image`.
- To preview locally: run `python -m http.server 8000` in the project root and open `http://localhost:8000/courses.html`.
