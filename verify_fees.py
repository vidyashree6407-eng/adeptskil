import json

# Verify the course_fees.json
data = json.load(open('course_fees.json'))
print(f"Total courses: {data['metadata']['total_courses']}")
print(f"Sample course: {data['courses'][0]['name']}")
print("\nFirst course details:")
for key, value in data['courses'][0].items():
    print(f"  {key}: {value}")
