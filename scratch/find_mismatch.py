with open(r'C:\Users\prise\Desktop\Projects\SmarTern-Production\resources\views\head-supervisor\office-matching.blade.php', 'r', encoding='utf-8') as f:
    content = f.read()

# Character offsets from mismatch output:
# Opened <div> at 39458
pos = 39458
line_no = content[:pos].count('\n') + 1
print(f"Line number for position {pos}: {line_no}")
print("Context around that position:")
print(content[pos-200:pos+300])
