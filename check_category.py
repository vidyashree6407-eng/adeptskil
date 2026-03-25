#!/usr/bin/env python3
import json

with open(r'c:\Users\MANJUNATH B G\adeptskil\course_fees.json') as f:
    data = json.load(f)

# Check for missing category fields
missing = [c for c in data['courses'] if 'category' not in c or not c.get('category')]
print(f'Total courses: {len(data["courses"])}')
print(f'Missing category: {len(missing)}')
if missing:
    print(f'First missing: {missing[0]}')
    print(f'Keys: {list(missing[0].keys())}')
