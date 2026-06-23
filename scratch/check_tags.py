import re

def check_all_tags(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Strip blade comments
    content = re.sub(r'{{--.*?--}}', '', content, flags=re.DOTALL)
    # Strip PHP tags
    content = re.sub(r'<\?php.*?\?>', '', content, flags=re.DOTALL)
    
    # We want to do a proper tag matching by finding tokens
    interesting_tags = ['div', 'form', 'table', 'tbody', 'thead', 'tr', 'td', 'template', 'button', 'span', 'label', 'select']
    
    all_tokens = []
    for m in re.finditer(r'<(/?)(' + '|'.join(interesting_tags) + r')\b[^>]*>', content, re.IGNORECASE):
        is_closing = bool(m.group(1))
        tag_name = m.group(2).lower()
        all_tokens.append((tag_name, is_closing, m.start()))

    stack = []
    for tag_name, is_closing, pos in all_tokens:
        line_no = content[:pos].count('\n') + 1
        if is_closing:
            if not stack:
                print(f"Error: Closing tag </{tag_name}> has no matching opening tag at line {line_no}")
            else:
                popped_name, popped_pos = stack.pop()
                popped_line = content[:popped_pos].count('\n') + 1
                if popped_name != tag_name:
                    print(f"Error: Mismatched tags: Opened <{popped_name}> at line {popped_line}, but closed with </{tag_name}> at line {line_no}")
                    # Push it back to keep tracking other mismatches
                    stack.append((popped_name, popped_pos))
        else:
            stack.append((tag_name, pos))

    print(f"Analysis complete. Remaining unclosed tags: {len(stack)}")
    for name, pos in stack:
        line_no = content[:pos].count('\n') + 1
        print(f"  Unclosed <{name}> on line {line_no}")

check_all_tags(r'C:\Users\prise\Desktop\Projects\SmarTern-Production\resources\views\head-supervisor\office-matching.blade.php')
