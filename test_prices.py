import json

data = json.load(open('course_fees.json'))
print('Sample courses from database:\n')
for i, c in enumerate(data['courses'][:15]):
    print(f"{i+1}. {c['name']}")
    print(f"   Standard: ${c['pricing']['standard']['price']}")
    print(f"   Early Bird: ${c['pricing']['early_bird']['price']}")
    print(f"   Virtual Std: ${c['pricing']['virtual_standard']['price']}")
    print(f"   Virtual EB: ${c['pricing']['virtual_early_bird']['price']}\n")
