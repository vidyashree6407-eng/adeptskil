#!/usr/bin/env python3
"""
Extract course fees from Excel and create JSON pricing database with 4 fee options
"""

import pandas as pd
import json
import os
from pathlib import Path

def extract_course_fees():
    """Extract course data from Excel and create course_fees.json"""
    
    excel_file = r'videos/COURSE FEES.xlsx'
    output_file = 'course_fees.json'
    
    # Read Excel file
    df = pd.read_excel(excel_file)
    
    # Clean up column names
    df.columns = [col.strip().lower() for col in df.columns]
    
    print(f"Total courses in Excel: {len(df)}")
    print(f"Columns: {list(df.columns)}")
    
    # Remove rows where course name is NaN
    df = df.dropna(subset=['course name'])
    
    print(f"Courses after removing empty rows: {len(df)}")
    
    # Create pricing structure
    courses = []
    
    for idx, row in df.iterrows():
        # Parse duration - handle both "5 hrs" and "5" formats
        duration_str = str(row['duration']).strip() if pd.notna(row['duration']) else '1'
        duration_value = int(duration_str.split()[0]) if duration_str else 1
        
        course_data = {
            'id': idx + 1,
            'name': str(row['course name']).strip(),
            'duration': duration_value,
            'pricing': {
                'standard': {
                    'name': 'Standard Fee',
                    'price': float(row['standard fee']) if pd.notna(row['standard fee']) else 0,
                    'description': 'IN-PERSON or LIVE-ONLINE training at standard rates'
                },
                'early_bird': {
                    'name': 'Early Bird Fee',
                    'price': float(row['early bird fee']) if pd.notna(row['early bird fee']) else 0,
                    'description': 'Early enrollment discount - Act now!'
                },
                'virtual_standard': {
                    'name': 'Live Virtual Standard Fee',
                    'price': float(row['live virtual standard fee']) if pd.notna(row['live virtual standard fee']) else 0,
                    'description': 'Virtual instructor-led training'
                },
                'virtual_early_bird': {
                    'name': 'Live Virtual Early Bird Fee',
                    'price': float(row['live virtual early bird fee']) if pd.notna(row['live virtual early bird fee']) else 0,
                    'description': 'Virtual early enrollment discount'
                }
            }
        }
        courses.append(course_data)
    
    # Create output structure
    output_data = {
        'metadata': {
            'total_courses': len(courses),
            'extracted_from': 'videos/COURSE FEES.xlsx',
            'pricing_options': 4,
            'options': [
                'STANDARD',
                'EARLY_BIRD',
                'VIRTUAL_STANDARD',
                'VIRTUAL_EARLY_BIRD'
            ]
        },
        'courses': courses
    }
    
    # Write to JSON
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(output_data, f, indent=2, ensure_ascii=False)
    
    print(f"\n✅ Successfully created {output_file}")
    print(f"   Total courses: {len(courses)}")
    
    # Print first 3 courses as preview
    print(f"\nFirst 3 courses preview:")
    for course in courses[:3]:
        print(f"\n  {course['name']}")
        print(f"    Pricing options:")
        for key, pricing in course['pricing'].items():
            print(f"      - {pricing['name']}: ${pricing['price']}")

if __name__ == '__main__':
    extract_course_fees()
