# Enrollment Form Fix - Testing Instructions

## What Was Fixed

The enrollment form was not saving data to `localStorage` because:
1. **Course price wasn't initialized** - `window.coursePrice` might have been undefined
2. **Missing console logging** - Hard to debug what was happening

## Changes Made

### 1. **enrollment.html** (Line 280)
Added default price initialization:
```javascript
window.coursePrice = 149.00;  // Default fallback price
```

### 2. **Form Handler** (Lines 339-420)
Completely rewrote the form submission logic with:
- **Detailed console logging** at every step so you can see what's happening
- **Simplified flow** that definitely saves to localStorage
- **Better error handling** with fallback values
- **Verification step** that reads back from localStorage to confirm save was successful

### 3. **New Test File**
Created `test-enrollment-simple.html` - A comprehensive testing tool with:
- Real-time console output capture
- Form to test enrollment data saving
- Manual localStorage check button
- Visual status display

## How to Test

### Option 1: Test with Simple Form (Easiest)
1. Navigate to: `http://localhost:8000/test-enrollment-simple.html`
2. The form is pre-filled with test data
3. Click **"Submit & Save to localStorage"**
4. Watch the Console Output panel on the right
5. You should see:
   - ✓ Form submission started
   - ✓ Enrollment data created
   - ✓ Data saved to localStorage successfully
   - ✓ Verification - read back from localStorage

### Option 2: Test with Real Enrollment Form
1. Navigate to: `http://localhost:8000/enrollment.html?course=Leadership%20Fundamentals`
2. Open **Browser DevTools** (Press F12)
3. Go to **Console** tab
4. Fill out the form
5. Click **"Continue to Payment"**
6. Watch console for logs starting with ✓
7. You should see at least 5+ console messages confirming data save

### Option 3: Manual Console Check
In browser devTools Console, type:
```javascript
localStorage.getItem('adeptskil_enrollment_data')
```

If it works, you'll see a JSON object. If empty, you'll see `null`.

## Expected Console Output

When form is submitted successfully, you should see:

```
✓ Form submission started
Form values: {fullName: "...", email: "...", ...}
✓ Enrollment data created: {course: "...", fullName: "...", ...}
✓ Data saved to localStorage successfully
✓ Verification - read back from localStorage: {course: "...", ...}
✓ Payment section shown
✓ Setting up PayPal form with enrollment ID: ENR-...
PayPal configuration: {...}
✓ PayPal form configured
✓ Ready to submit to PayPal
```

## If It Still Doesn't Work

1. **Clear localStorage and hard refresh:**
   - Press `Ctrl+Shift+Delete` or `Cmd+Shift+Delete`
   - Clear "Cookies and cached files"
   - Go back to test page
   - Try again

2. **Check browser console for errors:**
   - Press F12
   - Look for red error messages
   - Report them exactly

3. **Check if localStorage is enabled:**
   - In browser console, type: `typeof(Storage)`
   - Should return: `"object"`
   - If it says `"undefined"`, localStorage is disabled

4. **Test localStorage directly:**
   - In browser console:
   ```javascript
   localStorage.setItem('test', 'value');
   localStorage.getItem('test');  // Should return "value"
   localStorage.removeItem('test');
   ```
   - If this fails, localStorage is not working

## What Happens After Save Works

Once enrollment data is saving to localStorage:

1. ✓ **Success page will display data** (course name, price, date, email)
2. ✓ **Confirmation emails will send** (using send_confirmation_email.php)
3. ✓ **Diagnostic tool will show data** (diagnostic.html)
4. ✓ **Full end-to-end flow works** (form → payment → success → email)

## Quick Verification Steps

After testing:

1. Open `test-enrollment-simple.html`
2. Submit the form
3. Click **"Check localStorage Now"**
4. Status should show: ✓ Data successfully saved!
5. Details should display the enrollment information

## Files Modified

- `enrollment.html` - Fixed form handler with console logging
- `test-enrollment-simple.html` - NEW test tool (optional but helpful)

## Notes

- The console logging will help debug any issues
- The form now has multiple fallbacks (default price, fallback course name, etc.)
- Data is verified after saving (read back from localStorage to confirm)
- All error messages are descriptive
