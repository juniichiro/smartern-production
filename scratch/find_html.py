import json

def find_html_in_transcript():
    filepath = r'C:\Users\prise\.gemini\antigravity-cli\brain\ea660c39-0894-4405-b1f9-1f58cbb62716\.system_generated\logs\transcript_full.jsonl'
    with open(filepath, 'r', encoding='utf-8') as f:
        for line in f:
            try:
                data = json.loads(line)
                if data.get('type') == 'USER_INPUT':
                    content = data.get('content', '')
                    if 'Reset Pending' in content:
                        print("Found User Input with HTML!")
                        # Let's find lines with 'Reset Pending' in the HTML content
                        html_lines = content.split('\n')
                        for idx, hl in enumerate(html_lines):
                            if 'Reset Pending' in hl or 'showResetModal' in hl:
                                print(f"Line {idx}: {hl}")
            except Exception as e:
                pass

find_html_in_transcript()
