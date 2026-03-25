# Python Backend Setup - Quick Start Guide

## ✅ What You Need to Know

Your website now runs on **Python instead of PHP**:
- No PHP installation needed
- Same database functionality
- Simpler to configure
- Faster to start

---

## 🚀 Two Ways to Start

### **Option 1: Double-Click (Easiest)**

1. Find the file: **`start-server.bat`** in your project folder
2. **Double-click** it
3. A window will open and the server will start automatically
4. Open browser to: **http://localhost:8000**

Done! ✓

---

### **Option 2: Manual Command (If Option 1 Doesn't Work)

1. **Open Command Prompt** (press `Win + R`, type `cmd`, press Enter)
2. Go to your project folder:
   ```
   cd C:\Users\MANJUNATH B G\adeptskil
   ```
3. Install required packages first (one-time only):
   ```
   pip install flask flask-cors
   ```
4. Start the server:
   ```
   python app.py
   ```
5. You should see:
   ```
   Adeptskil Python Backend Server
   Starting server...
   Open browser: http://localhost:8000
   ```
6. Open browser to: **http://localhost:8000**

---

## ❓ Do I Have Python Installed?

Check by opening Command Prompt and typing:
```
python --version
```

**If you see a version number (like "Python 3.9.0"), you're good!**

**If not, download from:** https://www.python.org/downloads/
- Download Python 3.9+
- **Important:** Check "Add Python to Path" during installation
- Restart Command Prompt after installation

---

## 📝 Important Notes

- **Keep the server window open** while using the website
- Your database (`enrollments.db`) auto-creates on first payment
- All customer data is saved to the database
- You can view all enrollments at: `http://localhost:8000/view_enrollments.html`
- To stop the server, press **Ctrl+C** in the Command Prompt window

---

## 🔍 What Changed?

| What | Old (PHP) | New (Python) |
|------|-----------|-------------|
| Backend | process_enrollment.php | app.py |
| Start command | `php -S localhost:8000` | `python app.py` |
| Database | Same SQLite | Same SQLite |
| API endpoints | get_enrollments.php | /api/get_enrollments |
| Easy start | Hard (need PATH setup) | Easy (start-server.bat) |

---

## ⚠️ Troubleshooting

**Problem:** "python: command not found"
- **Solution:** Python not installed or not in PATH. [Download here](https://www.python.org/downloads/) and check "Add to PATH"

**Problem:** "ModuleNotFoundError: No module named 'flask'"
- **Solution:** Run this command once:
  ```
  pip install flask flask-cors
  ```

**Problem:** "Address already in use" or "Port 8000 is busy"
- **Solution:** Another server is using port 8000. Try:
  - Close other servers
  - Or use different port: `python app.py --port 8001` (then visit http://localhost:8001)

**Problem:** Payment data not saving/no error message
- **Solution:** 
  1. Check if server is running (Command Prompt should show "Open browser: http://localhost:8000")
  2. Check browser console (F12 → Console tab) for errors
  3. Make sure you're visiting http://localhost:8000 (not a different port)

---

## 📊 Next Steps

1. **Start the server** (use Option 1 or 2 above)
2. **Test a payment flow:**
   - Go to http://localhost:8000/courses.html
   - Click "Enroll Now" on any course
   - Fill the form and select a payment method
   - Complete the payment (use test credentials if needed)
3. **Check database:**
   - Go to http://localhost:8000/view_enrollments.html
   - Your enrollment should appear in the list
   - You can export as CSV or download the database file

---

## 📞 Need Help?

- Any errors? Share the Command Prompt output/error message
- Server not starting? Check if Python is installed and in PATH
- Database not appearing? Check if server started successfully

All system files are ready. Just run the server and you're done! ✅
