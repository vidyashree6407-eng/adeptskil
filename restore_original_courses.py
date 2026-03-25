#!/usr/bin/env python3
from urllib.request import urlopen

url = 'https://raw.githubusercontent.com/vidyashree6407-eng/adeptskil/main/courses.html'

try:
    with urlopen(url) as response:
        original = response.read().decode('utf-8')
    
    # Save the original
    with open(r'c:\Users\MANJUNATH B G\adeptskil\courses.html', 'w', encoding='utf-8') as f:
        f.write(original)
    
    print(f"✓ Restored original courses.html from GitHub ({len(original)} bytes)")
    print("✓ Original styling and structure preserved")
    
except Exception as e:
    print(f"❌ Error: {e}")
