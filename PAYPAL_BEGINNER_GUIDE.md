# PayPal Integration - Step-by-Step Beginner Guide

**Goal:** Add secure PayPal payments to your Adeptskil course enrollment form.

---

## PHASE 1: SETUP YOUR PAYPAL ACCOUNT (Takes 10 minutes)

### Step 1.1: Create PayPal Account
1. Go to https://www.paypal.com
2. Click **"Sign Up"** (top right)
3. Select **"Business Account"**
4. Fill in your details:
   - Business name: "Adeptskil"
   - Email: your-email@example.com
   - Password: Create strong password
5. Complete verification (check your email)

**✅ You now have a PayPal Business Account**

---

### Step 1.2: Get Developer Access
1. Go to https://developer.paypal.com
2. Click **"Log In"** (use your PayPal account created above)
3. You'll see your Dashboard
4. Click **"Apps & Credentials"** (left menu)

**✅ You're now in PayPal Developer Dashboard**

---

### Step 1.3: Create Sandbox Test Account
This is a FAKE testing environment. Very important for testing WITHOUT real money!

1. In Dashboard, click **"Sandbox"** tab (at top)
2. You'll see two test accounts already created:
   - **Personal (Buyer)** - for testing purchases
   - **Business (Merchant)** - for receiving payments
3. Look for "Business" account
4. Copy the email (looks like: `sb-xyz123@personal.example.com`)
5. **SAVE THIS EMAIL** - you'll need it soon

**✅ You have a test merchant account**

---

## PHASE 2: CONFIGURE YOUR WEBSITE (Takes 5 minutes)

### Step 2.1: Update PayPal Merchant Email
1. Open your project folder: `c:\Users\MANJUNATH B G\adeptskil`
2. Find file: `enrollment.html`
3. Open it with a text editor (Notepad or VS Code)
4. Search for: `sb-mgbzi49161372@personal.example.com`
5. Replace it with your sandbox merchant email (from Step 1.3)

**Example:**
```html
<!-- Before -->
<input type="hidden" name="business" value="sb-mgbzi49161372@personal.example.com">

<!-- After (if your email is different) -->
<input type="hidden" name="business" value="sb-YOUR-EMAIL-HERE@personal.example.com">
```

6. Save the file (Ctrl+S)

**✅ Your website now knows which PayPal account to send payments to**

---

### Step 2.2: Add PayPal Client ID (OPTIONAL - For later)
This is used by advanced PayPal features. For now, skip this.

---

## PHASE 3: TEST THE PAYMENT FLOW (Takes 10 minutes)

### Step 3.1: Start Your Local Server
1. Open PowerShell
2. Navigate to your project folder:
   ```powershell
   cd "c:\Users\MANJUNATH B G\adeptskil"
   ```
3. Start the server:
   ```powershell
   python -m http.server 8000
   ```
4. Open browser: http://localhost:8000

**✅ Server is running**

---

### Step 3.2: Test Enrollment Form
1. Click on **"Courses"** menu
2. Find any course (e.g., "Account Management")
3. Click **"Enroll Now"**
4. Fill the form:
   - **Name:** John Smith (or your name)
   - **Email:** john@example.com
   - **Phone:** 1234567890
   - **City:** New York
   - **Company:** (optional)
   - **Message:** (optional)
5. Click **"Continue to Payment"**

**✅ Form submitted**

---

### Step 3.3: Complete Test Payment
1. You should see: **"Pay with PayPal"** button
2. Click it
3. You'll be redirected to **PayPal Sandbox** (https://www.sandbox.paypal.com)

**IMPORTANT: You're now in SANDBOX (test mode) - NOT real money!**

4. Login to PayPal Sandbox:
   - Option A: Login with your **PayPal Developer Account** (the one you created in Step 1.1)
   - Option B: Click "Pay with Credit/Debit Card" and use this TEST CARD:
     - **Card Number:** `4111 1111 1111 1111`
     - **Expiry:** `12/25` (or any future date)
     - **CVV:** `123`

5. Click **"Complete Purchase"** (or similar button)
6. You should see success message
7. You'll be sent back to your website

**✅ Test payment complete!**

---

### Step 3.4: Verify Payment Was Recorded
1. Check your project folder for file: `enrollments.json`
2. Open it to see if your test enrollment was saved

**✅ Everything is working!**

---

## PHASE 4: SECURITY CHECKLIST (Important!)

Before going LIVE with real money, verify:

- [ ] You're using **Sandbox** for testing (NOT live PayPal)
- [ ] No real payment data is stored in your files
- [ ] You have an HTTPS certificate (for production)
- [ ] Phone number validation is working
- [ ] Email addresses are validated
- [ ] Form input is sanitized
- [ ] Only authenticated users can process payments

**Status:** ✅ **SECURE for testing**

---

## PHASE 5: GO LIVE (When ready)

### Step 5.1: Get Live Credentials
1. Go to https://developer.paypal.com (logged in)
2. Click **"Apps & Credentials"**
3. Click **"Live"** tab (instead of Sandbox)
4. Copy your **Live Business Email** (your real PayPal account)

---

### Step 5.2: Update Your Website for Live
1. Open `enrollment.html`
2. Find this line:
   ```html
   action="https://www.sandbox.paypal.com/cgi-bin/webscr"
   ```
3. Replace with:
   ```html
   action="https://www.paypal.com/cgi-bin/webscr"
   ```

4. Find the business email and update to your LIVE PayPal email:
   ```html
   <input type="hidden" name="business" value="your-real-email@example.com">
   ```

5. Save the file

---

### Step 5.3: Test with Live (Optional)
- Try a small real payment ($1-2) to verify it works
- You'll see real money in your PayPal account

---

## TROUBLESHOOTING

**Problem:** "Pay with PayPal" button doesn't appear
- **Solution:** Check browser console (F12 → Console) for errors
- Make sure `enrollment.html` is saved

**Problem:** PayPal says "Payment to this merchant not possible"
- **Solution:** Your merchant email is incorrect or not verified
- Go back to Step 2.1 and verify the email

**Problem:** I filled the form but button didn't appear
- **Solution:** Refresh the page and try again
- Make sure all required fields are filled (Name, Email, Phone, City)

**Problem:** Payment didn't go through
- **Solution:** Check you're using SANDBOX credentials for testing
- Use the test credit card: `4111 1111 1111 1111`

---

## QUICK REFERENCE

| Environment | URL | Email Type | For |
|-------------|-----|-----------|-----|
| **Sandbox** | https://www.sandbox.paypal.com | `sb-xxx@personal.example.com` | Testing |
| **Live** | https://www.paypal.com | `your-real-email@paypal.com` | Real Payments |

---

## NEXT STEPS

1. ✅ Complete Phase 1 (Setup PayPal Account)
2. ✅ Complete Phase 2 (Configure Website)
3. ✅ Complete Phase 3 (Test Payment)
4. ⏳ When ready: Complete Phase 5 (Go Live)

**Your website will process payments securely once you complete these steps!**

---

## SECURITY NOTES

✅ All payments go through PayPal (you never see credit card details)  
✅ Sandbox is completely safe for testing  
✅ Real data is never exposed  
✅ Your server validates everything  
✅ Enrollment data is saved locally in JSON format  

**You're protected by PayPal's security, not just yours!**
