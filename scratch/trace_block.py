import re

def trace_breakdown_block(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        lines = f.readlines()
    
    # Trace lines 553 to 773 (0-indexed: 552 to 772)
    stack = []
    for line_idx in range(552, 773):
        line_no = line_idx + 1
        line_content = lines[line_idx]
        
        # Clean blade comments and php tags
        line_content = re.sub(r'{{--.*?--}}', '', line_content, flags=re.DOTALL)
        line_content = re.sub(r'<\?php.*?\?>', '', line_content, flags=re.DOTALL)
        
        tags = re.findall(r'<div\b[^>]*>|</div>', line_content, re.IGNORECASE)
        for tag in tags:
            if tag.startswith('</'):
                if stack:
                    popped_line, popped_tag = stack.pop()
                    print(f"[{line_no}] Closed tag from line {popped_line}")
                else:
                    print(f"[{line_no}] Error: Extra closing tag {tag}")
            else:
                stack.append((line_no, tag))
                print(f"[{line_no}] Opened tag: {tag[:60]}")
                
    print(f"End of block. Remaining unclosed divs: {len(stack)}")
    for line_no, tag in stack:
        print(f"Line {line_no}: {tag[:80]}")

trace_breakdown_block(r'C:\Users\prise\Desktop\Projects\SmarTern-Production\resources\views\head-supervisor\office-matching.blade.php')
