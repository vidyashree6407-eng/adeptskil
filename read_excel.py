import pandas as pd

# Read the Excel file
file_path = r"c:\Users\MANJUNATH B G\adeptskil\Courses & Categories - Final.xlsx"

# Try to read all sheets
xls = pd.ExcelFile(file_path)
print("Sheet names:", xls.sheet_names)
print("\n" + "="*80 + "\n")

# Read each sheet and display
for sheet in xls.sheet_names:
    print(f"\n--- Sheet: {sheet} ---\n")
    df = pd.read_excel(file_path, sheet_name=sheet)
    print(df.to_string())
    print("\n")
