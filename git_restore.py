#!/usr/bin/env python3
import subprocess
import sys
import os

os.chdir(r'c:\Users\MANJUNATH B G\adeptskil')

try:
    # Get git log
    result = subprocess.run(
        ['git', 'log', '--oneline', 'courses.html'],
        capture_output=True,
        text=True,
        timeout=10
    )
    
    if result.returncode == 0:
        commits = result.stdout.strip().split('\n')
        print("Git commits that modified courses.html:")
        for i, commit in enumerate(commits[:20]):
            print(f"  {i}: {commit}")
        
        # Try to show the content from 5 commits back
        if len(commits) >= 5:
            commit_hash = commits[5].split()[0]
            print(f"\nTrying to restore from commit {commit_hash}...")
            
            result2 = subprocess.run(
                ['git', 'show', f'{commit_hash}:courses.html'],
                capture_output=True,
                text=True,
                timeout=10
            )
            
            if result2.returncode == 0:
                with open(r'c:\Users\MANJUNATH B G\adeptskil\courses.html', 'w', encoding='utf-8') as f:
                    f.write(result2.stdout)
                print(f"✓ Restored courses.html from {commit_hash}")
                print(f"✓ File size: {len(result2.stdout)} bytes")
            else:
                print(f"Error: {result2.stderr}")
    else:
        print(f"Git error: {result.stderr}")
        
except Exception as e:
    print(f"Python error: {e}")
    import traceback
    traceback.print_exc()
