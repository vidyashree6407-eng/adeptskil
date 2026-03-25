import json

courses = json.load(open('course_fees.json'))['courses']

print('=' * 80)
print('TESTING ENROLLMENT_WITH_FEES.HTML - COURSE LOOKUP')
print('=' * 80)
print(f'\nTotal courses in database: {len(courses)}\n')

print('Sample course names for testing:\n')
for i, c in enumerate(courses[:10], 1):
    print(f'{i}. {c["name"]}')
    
print('\n' + '=' * 80)
print('TEST SCENARIO: User navigates to:')
print(f'enrollment_with_fees.html?course={courses[0]["name"].replace(" ", "%20")}')
print('=' * 80)
print(f'\nExpected API response:')
print(f'  Course: {courses[0]["name"]}')
print(f'  Standard: ${courses[0]["pricing"]["standard"]["price"]}')
print(f'  Early Bird: ${courses[0]["pricing"]["early_bird"]["price"]}')
print(f'  Virtual Std: ${courses[0]["pricing"]["virtual_standard"]["price"]}')
print(f'  Virtual EB: ${courses[0]["pricing"]["virtual_early_bird"]["price"]}')
print(f'\nExpected: 4 pricing cards displayed side-by-side')
