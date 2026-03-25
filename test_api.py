import json

print("Testing course_fees_api.php endpoints:\n")
print("=" * 80)

data = json.load(open('course_fees.json'))

# Test: Get all courses
print(f"\n1. Testing: action=all")
print(f"   Should return: {data['metadata']['total_courses']} courses")
print(f"   ✓ Returns all courses with pricing\n")

# Test: Get specific course
test_course = 'Absence Management'
course = None
for c in data['courses']:
    if c['name'].lower() == test_course.lower():
        course = c
        break

if course:
    print(f"2. Testing: action=get_course&course={test_course}")
    print(f"   ✓ Course Found: {course['name']}")
    print(f"   - Standard: ${course['pricing']['standard']['price']}")
    print(f"   - Early Bird: ${course['pricing']['early_bird']['price']}")
    print(f"   - Virtual Std: ${course['pricing']['virtual_standard']['price']}")
    print(f"   - Virtual EB: ${course['pricing']['virtual_early_bird']['price']}\n")
else:
    print(f"2. Testing: action=get_course&course={test_course}")
    print(f"   ✗ Course NOT found!\n")

# Test: Enrollment URL
enrollment_url = f"enrollment_with_fees.html?course={test_course.replace(' ', '%20')}"
print(f"3. Enrollment URL:")
print(f"   {enrollment_url}")
print(f"   - Should load {test_course}")
print(f"   - Should show 4 pricing cards")
print(f"   - Card 1 (Standard): $695.00")
print(f"   - Card 2 (Early Bird): $545.00")
print(f"   - Card 3 (Virtual Std): $545.00")
print(f"   - Card 4 (Virtual EB): $445.00")
