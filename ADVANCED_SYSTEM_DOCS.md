# Advanced Customer Interaction System - Documentation

## Overview
This is a complete advanced customer interaction system for Adeptskil that enables:
- **Enhanced contact forms** with multiple field types and subject categories
- **Dual email notifications** (customer confirmation + admin notification)
- **Real-time admin dashboard** with message management
- **Automatic message tracking** with unique message IDs
- **Professional email templates** with formatting and branding

---

## System Components

### 1. **Enhanced Contact Form** (`contact.html`)
**Features:**
- Full Name field
- Email Address field
- Phone Number field (optional)
- Country field (optional)
- Subject dropdown with 8 categories
- Message textarea
- Real-time form validation
- Success/error notifications with request IDs
- AJAX submission for smooth UX

**Subject Categories:**
- Course Inquiry
- Corporate Training
- Partnership Opportunity
- Technical Support
- General Inquiry
- Enrollment Help
- Feedback
- Other

**Form Actions:**
- Client-side validation (email format, min/max lengths)
- AJAX submission to `process_contact.php`
- Real-time status messages
- Form reset on successful submission
- Request ID display for reference

---

### 2. **Advanced Backend Processor** (`process_contact.php`)
**Functionality:**

#### Input Processing
- Sanitizes and validates all inputs
- Removes potentially harmful code
- Validates email format
- Checks minimum/maximum field lengths
- Prevents empty submissions

#### Dual Email System

**Email 1: Customer Confirmation Email**
- Sent to customer's email address
- Includes:
  - Personalized greeting with customer name
  - Request ID and timestamp
  - Confirmation message
  - Expected response timeline (24 hours)
  - Quick action links to browse courses
  - Contact information for urgent matters
  - Professional footer with Adeptskil branding

**Email 2: Admin Notification Email**
- Sent to `info@adeptskil.com`
- Includes:
  - Formatted message with clear sections
  - Customer details (name, email, phone, country)
  - Message ID for tracking
  - Full message content
  - Reply-to field set to customer email
  - Quick action buttons for immediate response

#### Logging System
Creates two backup logs:
1. **contact_submissions.log** - Summary log with success/failure status
2. **messages_backup.txt** - Detailed backup with full message content

---

### 3. **Admin Dashboard** (`admin-messages.php`)
**Access:**
- URL: `http://localhost:8000/admin-messages.php`
- Password-protected login (default: `adeptskil123`)
- **⚠️ CHANGE THIS PASSWORD IN PRODUCTION!**

**Features:**
- Real-time message display
- Auto-refresh every 10 seconds
- Message filtering by subject
- Statistics (total messages, today's messages, unique subjects)
- Quick copy-to-clipboard for email addresses
- Time-ago formatting for message timestamps
- Color-coded subject badges
- Hover effects and professional UI

**Dashboard Elements:**
- Message count statistics
- Filter by subject type
- Formatted message display
- Customer contact information
- Message ID for reference
- Refresh button for manual updates

---

### 4. **Messages API** (`get-messages.php`)
**Purpose:**
- Provides JSON data for the admin dashboard
- Parses message backup files
- Calculates statistics
- Returns messages sorted by date (newest first)

**Response Format:**
```json
{
  "messages": [
    {
      "timestamp": "2024-01-15 10:30:45",
      "message_id": "MSG-1234567890-1234",
      "name": "John Doe",
      "email": "john@example.com",
      "phone": "+1234567890",
      "subject": "Course Inquiry",
      "country": "USA",
      "message": "I would like to know more about your leadership courses...",
      "time_ago": "2 hours ago"
    }
  ],
  "total": 45,
  "today": 8,
  "subjects": 5
}
```

---

## Installation & Setup

### Step 1: Update Files
All files have been updated with the new system:
- ✅ `contact.html` - Enhanced form with new fields and AJAX
- ✅ `process_contact.php` - Dual email system
- ✅ `admin-messages.php` - Dashboard
- ✅ `get-messages.php` - API

### Step 2: Verify Email Configuration
Check the email recipient in `process_contact.php`:
```php
$business_email = 'info@adeptskil.com'; // Change if needed
```

### Step 3: Create Log Directory (if needed)
The system automatically creates log files in the root directory:
- `contact_submissions.log`
- `messages_backup.txt`
- `contact_errors.log`

### Step 4: Change Admin Password
**IMPORTANT:** In `admin-messages.php`, change the default password:
```php
$admin_password = 'adeptskil123'; // Change to secure password
```

---

## How It Works

### Customer Journey
1. **Customer visits contact form** → `contact.html`
2. **Fills out form** with name, email, subject, message
3. **Clicks "Send Message"** → AJAX submits to `process_contact.php`
4. **Receives confirmation email** with Request ID
5. **Sees success message** on page with Request ID

### Admin Workflow
1. **Admin accesses dashboard** → `admin-messages.php`
2. **Logs in** with password
3. **Sees real-time messages** auto-refreshing every 10 seconds
4. **Can filter by subject** to find specific types of inquiries
5. **Copies customer email** and replies directly
6. **Tracks message count** with statistics

---

## Email Examples

### Customer Confirmation Email
```
Hello [Name],

Thank you for reaching out to Adeptskil! We're excited to connect with you.

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✓ MESSAGE RECEIVED

Request ID: MSG-1234567890-1234
Subject: Course Inquiry
Received: 2024-01-15 10:30:45

What happens next:
1. Our team will review your message within 24 hours
2. You'll receive a personal response from our specialist
3. We may follow up by phone if you provided a number

Best regards,
The Adeptskil Team
```

### Admin Notification Email
```
NEW CUSTOMER INQUIRY

Message ID: MSG-1234567890-1234
Received: 2024-01-15 10:30:45

CUSTOMER DETAILS:
Name: John Doe
Email: john@example.com
Phone: +1234567890
Subject: Course Inquiry
Country: USA

MESSAGE:
[Full customer message content]

QUICK ACTIONS:
• Reply to: john@example.com
• Phone: +1234567890
• Request ID: MSG-1234567890-1234
```

---

## Security Features

✅ **Input Sanitization**
- All inputs are sanitized with `strip_tags()` to prevent code injection
- Email validation using PHP's `filter_var()`

✅ **CSRF Protection**
- Form uses POST method only
- Server validates request method

✅ **Log Protection**
- Error reporting disabled to prevent exposure
- Sensitive data logged securely
- Logs are server-side only (not publicly accessible)

✅ **Password Protection**
- Admin dashboard requires password authentication
- Session-based authentication

⚠️ **Production Checklist:**
- [ ] Change admin password to something secure
- [ ] Use HTTPS (not HTTP) for form submission
- [ ] Configure proper email headers to prevent spoofing
- [ ] Set proper file permissions on log files (644)
- [ ] Consider moving logs outside web root
- [ ] Implement rate limiting to prevent spam
- [ ] Add CSRF tokens for production

---

## Testing

### Test the Form
1. Open `http://localhost:8000/contact.html`
2. Fill out all fields
3. Submit the form
4. Check for success message with Request ID

### Test Email Delivery
1. Check that customer receives confirmation email
2. Check that admin (`info@adeptskil.com`) receives notification

### Test Admin Dashboard
1. Go to `http://localhost:8000/admin-messages.php`
2. Enter password: `adeptskil123`
3. See all submitted messages
4. Test filtering by subject
5. Test copying email addresses

### Test Logging
1. Check `contact_submissions.log` for submission records
2. Check `messages_backup.txt` for detailed backups

---

## Customization

### Change Email Recipient
In `process_contact.php`, line ~29:
```php
$business_email = 'your-email@adeptskil.com';
```

### Change Site Name in Emails
In `process_contact.php`, line ~30:
```php
$site_name = 'Adeptskil';
```

### Add More Subject Categories
In `contact.html`, add to the select dropdown:
```html
<option value="New Category">New Category</option>
```

### Customize Email Templates
Edit the `$customer_body` and `$business_body` variables in `process_contact.php`

### Change Auto-Refresh Rate
In `admin-messages.php`, line ~250:
```javascript
setInterval(loadMessages, 10000); // Change 10000 to milliseconds
```

---

## Troubleshooting

### Emails Not Sending
- Check if PHP `mail()` function is enabled on server
- Verify `info@adeptskil.com` is correct
- Check error log: `contact_errors.log`
- Verify server has mail() configured with proper SMTP

### Form Submission Fails
- Check console for JavaScript errors
- Verify `process_contact.php` is in correct location
- Check if form fields have correct `name` attributes
- Verify AJAX request is going to correct PHP file

### Admin Dashboard Shows "No Messages"
- Wait for at least one form submission
- Check if `messages_backup.txt` file exists
- Verify file permissions allow PHP to read the file
- Check browser console for JavaScript errors

### Can't Login to Dashboard
- Password is case-sensitive: `adeptskil123`
- Check if session support is enabled in PHP
- Clear browser cookies and try again

---

## File Structure
```
/adeptskil/
├── contact.html              ✅ Enhanced form
├── process_contact.php       ✅ Email processor
├── admin-messages.php        ✅ Admin dashboard
├── get-messages.php          ✅ Messages API
├── contact_submissions.log   (auto-created)
├── messages_backup.txt       (auto-created)
└── contact_errors.log        (auto-created)
```

---

## API Endpoints

### `process_contact.php` (POST)
**Request:**
```
POST /process_contact.php
Content-Type: application/x-www-form-urlencoded

name=John&email=john@example.com&phone=+1234567890&subject=Course Inquiry&message=Hello...
```

**Response (Success):**
```json
{
  "success": true,
  "message": "Perfect! Your message has been sent successfully.",
  "details": "We've sent a confirmation to john@example.com.",
  "request_id": "MSG-1234567890-1234"
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Valid email is required; Message is required (minimum 10 characters)"
}
```

### `get-messages.php` (GET)
**Request:**
```
GET /get-messages.php
```

**Response:**
```json
{
  "messages": [...],
  "total": 45,
  "today": 8,
  "subjects": 5
}
```

---

## Next Steps

### Recommended Enhancements
1. **SMS/WhatsApp Notifications** - Integrate Twilio API
2. **Message Database** - Store in MySQL instead of log files
3. **Email Templates** - Use HTML templates instead of plain text
4. **Multi-language Support** - Translate emails to customer's language
5. **File Attachments** - Allow customers to upload files
6. **Auto-responder Delay** - Send custom delayed responses
7. **Analytics** - Track response times, customer satisfaction
8. **Integration** - Connect with Slack, Teams, or other platforms

---

## Support
For questions or issues, contact the development team or review the system components above.

**Last Updated:** 2024-01-15
**Version:** 1.0 - Advanced Customer Interaction System
