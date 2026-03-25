#!/usr/bin/env python3
"""
Generate the authentic courses.html with all 285 hardcoded course cards
"""
import json

# Load course data
with open('course_fees.json', 'r') as f:
    data = json.load(f)

# Helper function to get category from course
def get_category(course_name):
    """Map course names to appropriate categories"""
    name_lower = course_name.lower()
    
    # Leadership category
    if any(word in name_lower for word in ['leadership', 'management', 'director', 'executive', 'strategic']):
        return 'Leadership'
    # Management
    elif any(word in name_lower for word in ['manager', 'supervisor', 'team', 'project management', 'performance', 'coaching']):
        return 'Management'
    # Communication
    elif any(word in name_lower for word in ['communication', 'presentation', 'conflict', 'negotiation', 'writing', 'writing', 'speak']):
        return 'Communication'
    # Technical
    elif any(word in name_lower for word in ['excel', 'access', 'sql', 'database', 'power', 'technical', 'it', 'coding', 'programming']):
        return 'Technical'
    # HR
    elif any(word in name_lower for word in ['recruitment', 'recruitment', 'hr', 'human resource', 'employee', 'onboarding']):
        return 'HR & Recruitment'
    # Sales & Marketing
    elif any(word in name_lower for word in ['sales', 'marketing', 'brand', 'customer acquisition', 'social media']):
        return 'Sales & Marketing'
    # Finance
    elif any(word in name_lower for word in ['finance', 'accounting', 'budget', 'financial', 'payroll', 'tax']):
        return 'Finance & Accounting'
    # Operations
    elif any(word in name_lower for word in ['operations', 'logistics', 'supply', 'process', 'efficiency']):
        return 'Operations'
    # Compliance
    elif any(word in name_lower for word in ['compliance', 'legal', 'regulation', 'risk', 'audit']):
        return 'Compliance & Legal'
    # Customer Service
    elif any(word in name_lower for word in ['customer', 'service', 'support']):
        return 'Customer Service'
    # Health & Safety
    elif any(word in name_lower for word in ['safety', 'health', 'wellness', 'first aid']):
        return 'Health & Safety'
    # Personal Development
    else:
        return 'Personal Development'

# Build categories map
categories_map = {}
for course in data['courses']:
    category = get_category(course['name'])
    if category not in categories_map:
        categories_map[category] = []
    categories_map[category].append(course)

# Start building HTML
html_parts = []

html_parts.append('''<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse our comprehensive collection of professional training courses across 12 categories at Adeptskil.">
    <meta name="keywords" content="training courses, corporate education, professional development">
    <meta property="og:title" content="Courses - Adeptskil">
    <meta property="og:description" content="Explore 200+ training courses designed for professional growth.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://www.adeptskil.com/courses.html">
    <title>Courses - Adeptskil</title>
    <link rel="icon" type="image/jpeg" href="images/FAVICON.jpg">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Navigation handled by script.js -->
    
    <main class="courses-section">
        <div class="container">
            <div class="section-header">
                <h1>Our Professional Training Courses</h1>
                <p>Discover 280+ comprehensive courses designed to enhance your skills and career growth</p>
            </div>

            <!-- Courses Grid -->
            <div class="courses-grid">
''')

# Add all 285 courses as hardcoded cards
for course in data['courses']:
    category = get_category(course['name'])
    category_slug = category.lower().replace(' ', '-').replace('&', '').strip()
    course_name_escaped = course['name'].replace("'", "\\'")
    
    html_parts.append(f'''                <div class="course-card" data-category="{category_slug}">
                    <div class="course-image">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="course-content">
                        <span class="course-category">{category}</span>
                        <h3>{course['name']}</h3>
                        <p>Professional training course</p>
                    </div>
                    <div class="course-footer">
                        <button class="enroll-btn" onclick="enrollCourse('{course_name_escaped}')">Enroll Now</button>
                    </div>
                </div>
''')

html_parts.append('''            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Adeptskil</h3>
                    <p>Professional training and corporate education platform.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.html">Home</a></li>
                        <li><a href="courses.html">Courses</a></li>
                        <li><a href="contact.html">Contact</a></li>
                        <li><a href="about.html">About Us</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Legal</h3>
                    <ul>
                        <li><a href="privacy-policy.html">Privacy Policy</a></li>
                        <li><a href="terms-of-service.html">Terms of Service</a></li>
                        <li><a href="cookie-policy.html">Cookie Policy</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>Contact</h3>
                    <p>Email: <a href="mailto:info@adeptskil.com">info@adeptskil.com</a></p>
                    <p>Phone: +1 (800) 123-4567</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Adeptskil. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Enrollment Function -->
    <script>
        function enrollCourse(courseName) {
            if (!courseName || courseName.trim() === '') {
                alert('Invalid course name');
                return;
            }
            window.location.href = `enrollment_with_fees.html?course=${encodeURIComponent(courseName)}`;
        }
    </script>

    <!-- External Scripts -->
    <script src="script.js"></script>
</body>
</html>
''')

# Write to file
html_content = ''.join(html_parts)
with open('courses.html', 'w', encoding='utf-8') as f:
    f.write(html_content)

print(f"✓ Generated authentic courses.html with {len(data['courses'])} hardcoded course cards")
print(f"✓ File size: {len(html_content) / 1024:.2f} KB")
print(f"✓ Total lines: {len(html_content.splitlines())}")
