# 💰 PRICING SYSTEM INTEGRATION GUIDE

## Overview
Complete pricing and offers system for Adeptskil website with 134+ courses, 4 customer offers, and integration with payment processing.

---

## 📊 System Components

### 1. **course_pricing_manager.php** - Backend Pricing Engine
- Handles all pricing calculations
- Manages 4 discount offers
- Provides API endpoints
- Tracks pricing history

**Base Formula:**
```
Price = $50 (Setup Fee) + ($99 × Duration in Days)

Examples:
- 1-day course = $149
- 2-day course = $248
- 3-day course = $347
```

**Endpoints:**
```
GET/POST /course_pricing_manager.php?action=get_all_courses
GET/POST /course_pricing_manager.php?action=get_course_price&course=CourseName
GET/POST /course_pricing_manager.php?action=get_offers
GET/POST /course_pricing_manager.php?action=apply_offer&course=CourseName&code=WELCOME15
GET/POST /course_pricing_manager.php?action=calculate_prices
```

### 2. **course_pricing.json** - Pricing Database
- Contains all 134 courses
- Organized by 11 categories
- Pre-calculated prices
- Ready for API consumption

**Structure:**
```json
{
  "total_courses": 134,
  "categories": {
    "Leadership & Managerial Excellence": {
      "course_count": 21,
      "courses": [
        {
          "name": "Account Management",
          "duration": 1,
          "base_price": 149,
          "category": "Leadership & Managerial Excellence"
        }
      ]
    }
  }
}
```

### 3. **4 CUSTOMER OFFERS** - Discount System

| Offer | Code | Discount | Eligibility | Best For |
|-------|------|----------|------------|----------|
| **Welcome Offer** | WELCOME15 | 15% | All courses, new customers | First-time learners |
| **Team Training** | TEAM20 | 20% | All courses, 5+ participants | Organizations, group training |
| **Extended Training** | EXTENDED25 | 25% | 3+ day courses | Intensive, mastery-level training |
| **Corporate Annual** | CORPORATE30 | 30% off + Unlimited | All courses for 1 year | Enterprise, unlimited access |

**Offer Application Logic:**
```php
// Only ONE offer can be applied per enrollment
// System automatically selects best eligible offer
// Eligibility rules strictly enforced
```

---

## 🎯 Integration Points

### On Course Display Pages (courses.html)

Add pricing to each course card:

```html
<div class="course-card">
  <!-- Existing content -->
  
  <!-- ADD: Pricing Display -->
  <div class="course-price">
    <div class="price-amount">$149</div>
    <div class="price-duration">(1 day)</div>
  </div>
  
  <button onclick="enrollCourse('Course Name')">Enroll Now</button>
</div>

<style>
  .course-price {
    background: #f0f4ff;
    padding: 1rem;
    border-radius: 8px;
    margin: 1rem 0;
    text-align: center;
  }
  .price-amount {
    font-size: 1.8rem;
    color: #667eea;
    font-weight: 700;
  }
</style>
```

### On Enrollment Page (enrollment_pricing.html)

Features:
- ✅ Real-time price calculation
- ✅ Participant count updates
- ✅ Live offer preview
- ✅ Discount tracking
- ✅ Savings display

**Usage:**
```html
<!-- Link from course cards -->
<a href="enrollment_pricing.html?course=CourseNameHere">Enroll Now</a>
```

### Payment Integration (PayPal, Stripe, etc.)

When processing payments:
```php
// Include exact pricing in payment request
$paymentData = [
    'course_name' => $course,
    'base_price' => $basePrice,
    'discount_code' => $offerCode,
    'discount_amount' => $discountAmount,
    'final_amount' => $finalPrice,
    'participants' => $participantCount,
    'currency' => 'USD'
];

// Send to payment gateway with all details
```

### Admin Management (admin_pricing.html)

Dashboard shows:
- 📊 Total courses and pricing stats
- 💰 Average, min, max prices
- 🎁 Active offers and details
- 📚 All courses with pricing
- 🏷️ Courses by category

Access: `/admin_pricing.html`

---

## 📝 File Structure

```
/adeptskil/
├── course_pricing.json                 # All course data + pricing
├── course_pricing_manager.php         # Pricing engine & API
├── enrollment_with_pricing.php        # Enrollment processor
├── enrollment_pricing.html            # Enrollment form with pricing
├── pricing-offers.html                # Pricing/offers section
├── admin_pricing.html                 # Admin dashboard
├── extract_pricing.py                 # Excel to JSON converter
└── enrollment_logs/                   # Enrollment records
    └── enrollment_ENR-*.json
```

---

## 🚀 Implementation Steps

### Step 1: Backend Setup
```bash
# Verify these files exist:
- course_pricing.json
- course_pricing_manager.php
- enrollment_with_pricing.php
```

### Step 2: Frontend Integration

**In courses.html** - Add pricing display:
```javascript
// Fetch and display price for each course
async function displayCoursePrice(courseName) {
    const response = await fetch(
        `course_pricing_manager.php?action=get_course_price&course=${encodeURIComponent(courseName)}`
    );
    const data = await response.json();
    return data.data ? data.data.base_price : 'Contact for pricing';
}
```

**In index.html** - Add pricing section:
```html
<!-- Include pricing-offers.html content in index.html -->
<section id="pricing">
    <!-- Pricing boxes, offers, FAQ -->
</section>
```

### Step 3: Enrollment Flow Update

**Current Flow:**
```
Course Card → enrollCourse() → enrollment.html
```

**New Flow:**
```
Course Card → enrollCourse() → enrollment_pricing.html
    ↓
Real-time pricing & offers
    ↓
Participant selection
    ↓
Offer selection
    ↓
Final price calculation
    ↓
Payment Gateway (PayPal/Stripe)
    ↓
success.html
```

### Step 4: Admin Dashboard Setup

Access at: `http://localhost:8000/admin_pricing.html`

Features:
- View all 134 courses with pricing
- Monitor offer usage
- Filter by category
- Search functionality

---

## 💡 JavaScript Usage Examples

### Get Course Price
```javascript
// Fetch price for specific course
fetch('course_pricing_manager.php?action=get_course_price&course=Account%20Management')
    .then(res => res.json())
    .then(data => console.log(data.data.base_price)); // $149
```

### Apply Offer to Course
```javascript
// Calculate final price with offer
fetch('course_pricing_manager.php?action=apply_offer&course=Account%20Management&code=WELCOME15')
    .then(res => res.json())
    .then(data => {
        console.log('Final Price:', data.final_price); // $126.65
        console.log('Savings:', data.discount_amount);  // $22.35
    });
```

### Get All Offers
```javascript
// Display all available offers
fetch('course_pricing_manager.php?action=get_offers')
    .then(res => res.json())
    .then(data => {
        data.offers.forEach(offer => {
            console.log(`${offer.name}: ${offer.discount_percent}% off`);
        });
    });
```

### Calculate All Prices
```javascript
// Get pricing summary and all courses
fetch('course_pricing_manager.php?action=calculate_prices')
    .then(res => res.json())
    .then(data => {
        console.log('Total Courses:', data.total_courses);
        console.log('Average Price:', data.price_range.average);
        console.log('Price Range:', data.price_range.min, '-', data.price_range.max);
    });
```

---

## 🎯 Price Point Examples

### By Duration

| Duration | Price | Formula |
|----------|-------|---------|
| 1 day | $149 | $50 + ($99 × 1) |
| 2 days | $248 | $50 + ($99 × 2) |
| 3 days | $347 | $50 + ($99 × 3) |
| 4 days | $446 | $50 + ($99 × 4) |
| 5 days | $545 | $50 + ($99 × 5) |

### With Offers Applied

**Example: 3-day course ($347 base)**

| Offer | Code | Discount | Final Price | Savings |
|-------|------|----------|-------------|---------|
| None | - | 0% | $347.00 | $0.00 |
| Welcome | WELCOME15 | 15% | $294.95 | $52.05 |
| Team (5+) | TEAM20 | 20% | $277.60 | $69.40 |
| Extended | EXTENDED25 | 25% | $260.25 | $86.75 |
| Corporate | CORPORATE30 | 30% | $242.90 | $104.10 |

---

## 📋 All 134 Courses

### Categories (11 total):
1. **Agile Practices & Leadership** - 7 courses
2. **Business Growth & Strategic Marketing** - 15 courses
3. **Software Innovation & Emerging Technology** - 3 courses
4. **Cloud Computing & DevOps** - 7 courses
5. **IT Security** - 7 courses
6. **Project Management** - 10 courses
7. **Data Management & Power BI** - 9 courses
8. **Leadership & Managerial Excellence** - 21 courses
9. **Career Development Skills** - 23 courses
10. **Soft Skills** - 24 courses
11. **Microsoft Azure** - 13 courses
12. **Construction & Quality Management** - 2 courses

---

## 🔐 Security Considerations

1. **Offer Validation**
   - All offers server-side validated
   - No client-side manipulation possible
   - Eligibility rules strictly enforced

2. **Price Protection**
   - Base prices stored securely
   - Pricing logic protected
   - No unauthorized modifications

3. **Payment Integration**
   - Use HTTPS always
   - Validate all payment data
   - Implement PCI compliance
   - Secure payment gateway keys

---

## 🧪 Testing Checklist

- [ ] All 134 courses display correct pricing
- [ ] Price formula: $50 + ($99 × days)
- [ ] All 4 offers calculate correctly
- [ ] Offer eligibility rules work
- [ ] Real-time price updates on enrollment form
- [ ] Participant count affects pricing
- [ ] Discount percentages correct
- [ ] Payment receives correct final amount
- [ ] Admin dashboard loads all courses
- [ ] Search/filter functionality works
- [ ] Mobile responsive on all pages
- [ ] No pricing display on logged-out pages

---

## 📞 Support & Troubleshooting

### Issues

**Q: Course price not showing**
- A: Check course_pricing.json exists
- Verify course_pricing_manager.php accessible
- Clear browser cache

**Q: Offer not applying**
- A: Check eligibility criteria met
- Verify offer code correct
- Check participant count

**Q: Payment amount incorrect**
- A: Verify discount applied server-side
- Check final_price in payment request
- Review enrollment log

---

## 📊 Analytics to Track

1. Most expensive courses
2. Average enrollment value per offer
3. Offer redemption rates
4. Revenue by category
5. Participant count patterns

---

## 🔄 Future Enhancements

1. Dynamic pricing based on demand
2. Seasonal discounts
3. Group bulk pricing
4. Loyalty rewards program
5. A/B testing different offers
6. Pricing history & audit logs
7. Revenue forecasting

---

**Last Updated:** March 20, 2026
**Status:** ✅ Ready for Production
**Total Courses:** 134
**Total Categories:** 11
**Active Offers:** 4
