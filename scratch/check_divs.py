import re

def check_divs(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # We want to trace the tag hierarchy.
    # Let's clean up PHP comments and Blade directives that might confuse a simple parser, 
    # but keep html tags.
    # Replace comments
    content = re.sub(r'{{--.*?--}}', '', content, flags=re.DOTALL)
    
    # Let's find all <div ...> and </div>
    tags = re.findall(r'<div\b[^>]*>|</div>', content, re.IGNORECASE)
    
    stack = []
    x_data_stack_indices = []
    
    for i, tag in enumerate(tags):
        if tag.startswith('</'):
            if stack:
                popped = stack.pop()
                if popped.startswith('<div x-data'):
                    print(f"x-data closed at tag index {i}")
            else:
                print(f"Extra closing div tag at index {i}")
        else:
            stack.append(tag)
            if 'x-data' in tag:
                x_data_stack_indices.append(len(stack) - 1)
                print(f"x-data opened at tag index {i}: {tag[:50]}...")
                
    print(f"End of file. Remaining stack depth: {len(stack)}")
    for j in stack:
        print(f"Unclosed: {j[:50]}...")

check_divs(r'C:\Users\prise\Desktop\Projects\SmarTern-Production\resources\views\head-supervisor\office-matching.blade.php')
