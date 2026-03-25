import json
import sys

# Load and verify course_fees.json
try:
    with open('course_fees.json', 'r') as f:
        data = json.load(f)
    
    courses = data.get('courses', [])
    print(f"Total courses: {len(courses)}")
    
    if courses:
        first = courses[0]
        print(f"First course: {first['name']}")
        if 'pricing' in first:
            pricing = first['pricing']
            print(f"Pricing keys: {list(pricing.keys())}")
            for key in list(pricing.keys())[:2]:
                price = pricing[key].get('price', 'N/A')
                name = pricing[key].get('name', 'N/A')
                print(f"  {key}: {name} = ${price}")
    else:
        print("ERROR: No courses in database")
        
except Exception as e:
    print(f"ERROR: {str(e)}")
    sys.exit(1)
