import re

def trace_exact(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Strip blade comments and php tags
    content = re.sub(r'{{--.*?--}}', '', content, flags=re.DOTALL)
    content = re.sub(r'<\?php.*?\?>', '', content, flags=re.DOTALL)

    interesting_tags = ['div', 'form', 'table', 'tbody', 'thead', 'tr', 'td', 'template', 'button']
    
    # We want to do a proper tag matching by finding tokens
    all_tokens = []
    # Let's search with regex finding the whole tag to know if it's opening or closing
    # Since tags can span multiple lines, we match them across lines by using re.DOTALL and [\s\S]*?
    for m in re.finditer(r'<(/?)(' + '|'.join(interesting_tags) + r')\b[\s\S]*?>', content, re.IGNORECASE):
        is_closing = bool(m.group(1))
        tag_name = m.group(2).lower()
        all_tokens.append((tag_name, is_closing, m.start()))

    stack = []
    for tag_name, is_closing, pos in all_tokens:
        line_no = content[:pos].count('\n') + 1
        # Only trace after line 400 and before line 785
        if line_no < 400 or line_no > 785:
            continue
            
        if is_closing:
            if not stack:
                print(f"[{line_no}] </{tag_name}> (stack empty - error!)")
            else:
                popped_name, popped_line = stack.pop()
                if popped_name != tag_name:
                    print(f"[{line_no}] </{tag_name}> (Mismatched! Expected closing for <{popped_name}> opened on line {popped_line})")
                    # Put it back to see what else is wrong
                    stack.append((popped_name, popped_line))
                else:
                    print(f"[{line_no}] </{tag_name}> (Matches <{popped_name}> from line {popped_line})")
        else:
            stack.append((tag_name, line_no))
            print(f"[{line_no}] <{tag_name}>")

trace_exact(r'C:\Users\prise\Desktop\Projects\SmarTern-Production\resources\views\head-supervisor\office-matching.blade.php')
