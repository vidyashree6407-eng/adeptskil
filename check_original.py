#!/usr/bin/env python3
import urllib.request
import json

url = 'https://raw.githubusercontent.com/vidyashree6407-eng/adeptskil/main/courses.html'
try:
    with urllib.request.urlopen(url) as response:
        original = response.read().decode('utf-8')
    
    # Save first 2000 chars to check styling
    with open('courses_original_check.txt', 'w') as f:
        f.write(original[:3000])
    
    print(f"✓ Downloaded original ({len(original)} bytes)")
    print("Checking styling structure...")
    
    # Check for key elements
    if 'grid-template-columns: repeat(4, 1fr)' in original:
        print("✓ Found: 4-column grid layout")
    if 'course-card' in original:
        print("✓ Found: course-card styling")
    if 'data-category' in original:
        print("✓ Found: data-category attribute")
    
except Exception as e:
    print(f"Error: {e}")
