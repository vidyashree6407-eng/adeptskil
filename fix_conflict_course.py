#!/usr/bin/env python3
import re

# Read the file
with open('courses.html', 'r', encoding='utf-8') as f:
    content = f.read()

# Replace all occurrences of "Conflict Management Training" with "Conflict Management"
content = content.replace('Conflict Management Training', 'Conflict Management')

# Write back
with open('courses.html', 'w', encoding='utf-8') as f:
    f.write(content)

print("✓ Fixed all 'Conflict Management Training' → 'Conflict Management'")
