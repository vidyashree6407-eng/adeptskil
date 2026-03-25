#!/usr/bin/env python3
import json

# Load courses from database
with open(r'c:\Users\MANJUNATH B G\adeptskil\course_fees.json', 'r', encoding='utf-8') as f:
    data = json.load(f)

# Start building the HTML
html = '''<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Courses - Adeptskil</title>
    <link rel="icon" type="image/jpeg" href="images/FAVICON.jpg">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: #333;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 40px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar h1 { font-size: 1.8rem; font-weight: 700; display: flex; align-items: center; gap: 10px; }
        .navbar a { color: white; text-decoration: none; display: flex; align-items: center; gap: 8px; font-size: 1rem; }
        .navbar a:hover { opacity: 0.9; }
        .container { max-width: 1400px; margin: 0 auto; padding: 100px 20px 40px; }
        .page-header { text-align: center; margin-bottom: 50px; }
        .page-header h1 { font-size: 2.5rem; color: #667eea; margin-bottom: 10px; font-weight: 700; }
        .courses-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .course-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        .course-card:hover { transform: translateY(-8px); box-shadow: 0 8px 25px rgba(0,0,0,0.2); }
        .course-image {
            height: 120px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .course-image i { font-size: 3rem; }
        .course-info { padding: 20px; }
        .course-category { font-size: 0.75rem; color: #667eea; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }
        .course-title { font-size: 1rem; font-weight: 700; margin-bottom: 12px; color: #2c3e50; line-height: 1.4; }
        .course-actions { border-top: 1px solid #eee; padding-top: 15px; }
        .enroll-btn {
            width: 100%;
            padding: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }
        .enroll-btn:hover { opacity: 0.95; transform: scale(1.01); }
        .course-category-section { margin-bottom: 60px; }
        .category-title { font-size: 1.8rem; color: #667eea; font-weight: 700; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 3px solid #667eea; }
        @media (max-width: 1200px) { .courses-grid { grid-template-columns: repeat(3, 1fr); } }
        @media (max-width: 768px) { .courses-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 480px) { .courses-grid { grid-template-columns: 1fr; } .navbar { flex-direction: column; gap: 10px; } }
    </style>
</head>
<body>
    <div class="navbar">
        <h1><i class="fas fa-graduation-cap"></i> Adeptskil</h1>
        <a href="index.html"><i class="fas fa-home"></i> Home</a>
    </div>

    <div class="container">
        <div class="page-header">
            <h1>Our Courses</h1>
        </div>

        <div id="coursesContainer">
'''

# Group courses by category
categories = {}
for course in data['courses']:
    cat = course.get('category', 'Other')
    if cat not in categories:
        categories[cat] = []
    categories[cat].append(course)

# Generate course cards grouped by category
for category in sorted(categories.keys()):
    html += f'''
        <div class="course-category-section">
            <h2 class="category-title">{category}</h2>
            <div class="courses-grid">
'''
    for course in categories[category]:
        course_name_escaped = course['name'].replace('"', '&quot;').replace("'", "\\'")
        html += f'''                <div class="course-card" data-category="{category.lower().replace(' ', '-')}">
                    <div class="course-image">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="course-info">
                        <div class="course-category">{category}</div>
                        <h3 class="course-title">{course['name']}</h3>
                        <div class="course-actions">
                            <button class="enroll-btn" onclick="enrollCourse('{course_name_escaped}')">Enroll Now</button>
                        </div>
                    </div>
                </div>
'''
    html += '''            </div>
        </div>
'''

# Add closing HTML and script
html += '''    </div>

    <script>
        function enrollCourse(courseName) {
            window.location.href = "enrollment_with_fees.html?course=" + encodeURIComponent(courseName);
        }
    </script>
</body>
</html>
'''

# Write to file
with open(r'c:\Users\MANJUNATH B G\adeptskil\courses.html', 'w', encoding='utf-8') as f:
    f.write(html)

print(f"✓ Generated courses.html with {len(data['courses'])} courses")
print(f"✓ Organized into {len(categories)} categories")
print("✓ Original styling restored")
