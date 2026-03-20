import json
import pandas as pd

# Read Excel file
df = pd.read_excel('Courses & Categories - Final.xlsx', sheet_name='Sheet1')

# Extract all courses with their categories and durations
courses_data = {}
current_category = None

for idx, row in df.iterrows():
    # Check if this is a category header (where course name column has text but duration is NaN)
    if pd.isna(row.iloc[1]) and pd.notna(row.iloc[0]):
        # This is a category
        category_name = str(row.iloc[0]).strip()
        if category_name and 'NaN' not in category_name:
            current_category = category_name
            if current_category not in courses_data:
                courses_data[current_category] = []
    
    # Check if this is a course entry
    elif pd.notna(row.iloc[0]) and pd.notna(row.iloc[1]):
        course_name = str(row.iloc[0]).strip()
        duration = int(float(row.iloc[1]))
        
        if course_name and current_category and 'NaN' not in course_name:
            # Base pricing: $99 per day + $50 base fee
            base_price = 50 + (duration * 99)
            
            courses_data[current_category].append({
                'name': course_name,
                'duration': duration,
                'base_price': base_price,
                'category': current_category
            })

# Create pricing output
pricing_output = {
    'total_courses': sum(len(v) for v in courses_data.values()),
    'categories': {}
}

for category, courses in courses_data.items():
    pricing_output['categories'][category] = {
        'course_count': len(courses),
        'courses': courses
    }

# Save as JSON
with open('course_pricing.json', 'w') as f:
    json.dump(pricing_output, f, indent=2)

print(f"✅ Extracted {pricing_output['total_courses']} courses from {len(pricing_output['categories'])} categories")
print(f"✅ Pricing file saved: course_pricing.json")
print("\nCategories:")
for cat in pricing_output['categories']:
    count = pricing_output['categories'][cat]['course_count']
    print(f"  - {cat}: {count} courses")
