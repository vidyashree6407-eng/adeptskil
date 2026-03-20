# 4-Tier Pricing System - Complete Implementation Guide

## Overview

The new pricing system displays all 4 fee options directly from the Excel file (COURSE FEES.xlsx):
1. **Standard Fee** - Regular in-person or live-online training
2. **Early Bird Fee** - Early enrollment discount (~22% off)
3. **Live Virtual Standard Fee** - Virtual instructor-led training
4. **Live Virtual Early Bird Fee** - Virtual with early discount (~36% off)

## Architecture

### Files Created

1. **course_fees.json** (285 courses)
   - Extracted from `videos/COURSE FEES.xlsx`
   - Contains all 4 pricing tiers per course
   - Structure: `course → pricing → {standard, early_bird, virtual_standard, virtual_early_bird}`

2. **course_fees_api.php**
   - REST API endpoint for pricing data
   - Actions: `all`, `search`, `get_course`, `get_pricing_options`, `calculate_discount`
   - Returns JSON with course data and pricing

3. **enrollment_with_fees.html**
   - Professional enrollment form
   - Shows 4 pricing options side-by-side
   - Each card displays: name, description, price, badge, select button
   - Real-time price updates and summary display
   - Beautiful gradient UI with animations

4. **courses_pricing_catalog.html**
   - Complete course directory (285 courses)
   - Search functionality
   - Displays all 4 pricing tiers for each course
   - "Enroll Now" buttons linking to enrollment form

## How It Works

### User Flow

```
1. User visits courses_pricing_catalog.html
   ↓
2. System loads all 285 courses from course_fees_api.php
   ↓
3. User searches for a course or browses
   ↓
4. User clicks "Enroll Now" on a course
   ↓
5. Redirects to enrollment_with_fees.html?course=CourseNameURL
   ↓
6. enrollment_with_fees.html fetches course pricing from API
   ↓
7. Shows 4 pricing cards side-by-side
   ↓
8. User selects desired pricing option
   ↓
9. Form fills with selection summary
   ↓
10. User enters personal info and payment method
    ↓
11. Submission → process_enrollment.php → Payment/Success
```

## Pricing Data Structure

### Example Course Object (from course_fees.json)

```json
{
  "id": 1,
  "name": "10 Soft Skills You Need",
  "duration": 1,
  "pricing": {
    "standard": {
      "name": "Standard Fee",
      "price": 695.0,
      "description": "IN-PERSON or LIVE-ONLINE training at standard rates"
    },
    "early_bird": {
      "name": "Early Bird Fee",
      "price": 545.0,
      "description": "Early enrollment discount - Act now!"
    },
    "virtual_standard": {
      "name": "Live Virtual Standard Fee",
      "price": 545.0,
      "description": "Virtual instructor-led training"
    },
    "virtual_early_bird": {
      "name": "Live Virtual Early Bird Fee",
      "price": 445.0,
      "description": "Virtual early enrollment discount"
    }
  }
}
```

## API Endpoints

### 1. Get All Courses
```
GET course_fees_api.php?action=all
```
Returns all 285 courses with pricing

### 2. Search Courses
```
GET course_fees_api.php?action=search&course=SearchTerm
```
Find courses by name

### 3. Get Specific Course
```
GET course_fees_api.php?action=get_course&course=CourseFullName
```
Get exact course pricing

### 4. Get Pricing Options Info
```
GET course_fees_api.php?action=get_pricing_options
```
Get all 4 option definitions with badges and colors

### 5. Calculate Discounts
```
GET course_fees_api.php?action=calculate_discount&course=CourseName
```
Get savings amounts and percentages for each option

## Enrollment Form Features

### Dynamic Pricing Display
- Each course shows 4 cards with different pricing
- Automatic badge generation (e.g., "SAVE 22%", "Save 36%", "Online")
- Color coding: Blue (standard), Green (early bird), Purple (virtual), Orange (virtual early)

### Form Sections
1. **Personal Information**
   - Full Name, Email, Phone, Company, Job Title
   
2. **Your Selection**
   - Course name (auto-filled)
   - Selected pricing option (auto-filled)
   - Amount (auto-filled)
   - Payment method (PayPal, Credit Card, Bank Transfer)
   - Additional notes

### Selection Summary
- Shows chosen course, format, duration, and savings
- Displays total investment prominently
- Updates in real-time as form fills

## Integration with Existing System

### Linking to Enrollment
From ANY page, link to enrollment form:
```html
<a href="enrollment_with_fees.html?course=Course%20Name">Enroll Now</a>
```

### Linking to Catalog
From homepage or navigation:
```html
<a href="courses_pricing_catalog.html">All Courses & Pricing</a>
```

## Data Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│         COURSE FEES.xlsx (Source)                           │
│    (285 courses × 4 pricing tiers)                         │
└──────────────────┬──────────────────────────────────────────┘
                   │
                   ▼ (Python extraction)
┌─────────────────────────────────────────────────────────────┐
│         course_fees.json (Database)                         │
│    (285 courses with structured pricing)                   │
└──────────────────┬──────────────────────────────────────────┘
                   │
              ┌────┴────┐
              │          │
              ▼          ▼
    ┌──────────────┐  ┌──────────────────────┐
    │course_fees_  │  │enrollment_with_fees  │
    │api.php       │  │.html                 │
    │(REST API)    │  │(Form UI)             │
    └──────────────┘  └──────────────────────┘
              │              │
              └──────┬───────┘
                     ▼
            ┌───────────────────┐
            │ process_enrollment│
            │.php (Handler)     │
            └─────────┬─────────┘
                      ▼
            ┌───────────────────┐
            │ Success/Payment   │
            └───────────────────┘
```

## Pricing Tiers Explanation

### Standard Fee
- **Use Case**: In-person or live online classes
- **Typically**: Regular pricing
- **Badge**: "Most Popular"
- **Example**: $695.00

### Early Bird Fee
- **Use Case**: Early enrollment discount
- **Savings**: ~22-30% off standard
- **Ideal For**: Planning ahead
- **Badge**: "SAVE 22-30%"
- **Example**: $545.00

### Live Virtual Standard Fee
- **Use Case**: Online instructor-led training
- **Advantage**: Flexibility, no travel
- **Typically**: 20% discount vs standard
- **Badge**: "Online"
- **Example**: $545.00

### Live Virtual Early Bird Fee
- **Use Case**: Virtual + early enrollment
- **Savings**: ~30-36% off standard
- **Best For**: Budget-conscious online learners
- **Badge**: "SAVE 36%"
- **Example**: $445.00

## Testing Endpoints

### Test in Browser

1. **View all courses:**
   ```
   http://localhost:8000/course_fees_api.php?action=all
   ```

2. **Search for course:**
   ```
   http://localhost:8000/course_fees_api.php?action=search&course=leadership
   ```

3. **Get specific course:**
   ```
   http://localhost:8000/course_fees_api.php?action=get_course&course=Agile%20Scrum%20Master
   ```

4. **Get pricing options:**
   ```
   http://localhost:8000/course_fees_api.php?action=get_pricing_options
   ```

## Usage Instructions

### For Users
1. Visit `courses_pricing_catalog.html`
2. Search or browse courses
3. View all 4 pricing options for each course
4. Click "Enroll Now"
5. Select your preferred option
6. Complete enrollment form
7. Proceed to payment

### For Developers
1. Extract new pricing: Run `python extract_course_fees.py`
2. Deploy `course_fees.json` and `course_fees_api.php`
3. Link to `enrollment_with_fees.html` or `courses_pricing_catalog.html`
4. Test API endpoints before launch

## Key Features

✅ **4 Pricing Tiers** - All from Excel file, no hardcoding  
✅ **Professional Design** - Attractive cards with badges and colors  
✅ **Real-time Summary** - Shows selection with total price  
✅ **Search & Filter** - Find courses easily  
✅ **Responsive Design** - Works on all devices  
✅ **Dynamic Calculations** - Auto-calculates savings  
✅ **Secure API** - JSON endpoints with validation  
✅ **Form Validation** - Ensures all required fields  

## File Locations

```
/
├── course_fees.json                 ← Database (285 courses)
├── course_fees_api.php              ← REST API
├── enrollment_with_fees.html        ← Enrollment form  
├── courses_pricing_catalog.html     ← Course directory
├── extract_course_fees.py           ← Extraction tool
├── videos/
│   └── COURSE FEES.xlsx             ← Source Excel
└── process_enrollment.php           ← Existing handler
```

## Troubleshooting

### API returns "Course not found"
- Check exact course name capitalization
- Ensure URL encoding: `Agile%20Scrum` for "Agile Scrum"

### Prices not showing
- Verify `course_fees.json` exists and is readable
- Check API endpoint returns data
- Clear browser cache

### Form not submitting
- Ensure payment method is selected
- Verify pricing option is selected
- Check browser console for errors

## Migration Notes

### From Old System (if applicable)
Old pricing system used formula: $50 + ($99 × days)  
**New system**: Uses exact prices from Excel per tier

This provides more flexibility and control over pricing tiers.

---

**Last Updated**: March 2026  
**Total Courses**: 285  
**Pricing Options**: 4 per course  
**Status**: Production Ready
