import os

def list_logs():
    path = r'C:\Users\prise\.gemini\antigravity-cli\brain\ea660c39-0894-4405-b1f9-1f58cbb62716\.system_generated\logs'
    if os.path.exists(path):
        print(os.listdir(path))
    else:
        print("Path doesn't exist:", path)

list_logs()
