#!/usr/bin/env python3
import os
import shutil
from urllib.request import urlopen

# File path
file_path = r'c:\Users\MANJUNATH B G\adeptskil\courses.html'

# Download from GitHub
url = 'https://raw.githubusercontent.com/vidyashree6407-eng/adeptskil/main/courses.html'

try:
    print("Downloading courses.html from GitHub...")
    with urlopen(url) as response:
        content = response.read().decode('utf-8')
    
    # Replace all "Conflict Management Training" with "Conflict Management"
    content = content.replace('Conflict Management Training', 'Conflict Management')
    
    # Write to file
    with open(file_path, 'w', encoding='utf-8') as f:
        f.write(content)
    
    print(f"✓ Successfully restored and fixed courses.html ({len(content)} bytes)")
    print("✓ Changed: 'Conflict Management Training' → 'Conflict Management'")
    
except Exception as e:
    print(f"Error: {e}")
    print("\nFallback: Trying git restore...")
    os.system(f'cd "{os.path.dirname(file_path)}" && git checkout HEAD -- courses.html')
