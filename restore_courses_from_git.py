#!/usr/bin/env python3
import subprocess
import os

os.chdir(r'c:\Users\MANJUNATH B G\adeptskil')

# Get list of commits
try:
    result = subprocess.run(['git', 'log', '--oneline', '-20', 'courses.html'], 
                          capture_output=True, text=True)
    print("Recent commits for courses.html:")
    print(result.stdout)
    
    # Try to get content from a previous commit (go back 15 commits)
    result2 = subprocess.run(['git', 'show', 'HEAD~15:courses.html'], 
                           capture_output=True, text=True)
    if result2.returncode == 0:
        print(f"\n✓ Found earlier version (HEAD~15)")
        # Save it
        with open('courses.html', 'w', encoding='utf-8') as f:
            f.write(result2.stdout)
        print("✓ Restored!")
    else:
        print(f"Error getting HEAD~15: {result2.stderr}")
        
except Exception as e:
    print(f"Error: {e}")
