import sys
import json
from scripts.rete_match import run_rete_matching

def test_matching_logic():
    print("--- STARTING SMARTERN MATCHING ENGINE TEST ---")

    # Mock Rules (Set by Head Supervisor for Office ID 1: ICT Department)
    rules = {
        "1": [  # Office ID 1
            {
                "rule_value": "Using spreadsheet software for organizing, computing, and analyzing data (MS Excel, Google Sheets, etc.)", 
                "priority": 5,
                "office": {"name": "ICT Department"}
            },
            {
                "rule_value": "Using video conferencing or online collaboration tools (Zoom, Google Meet, MS Teams, etc.)", 
                "priority": 3,
                "office": {"name": "ICT Department"}
            }
        ]
    }

    # Test Case 1: Baseline Bob (Has degree, but no experience/certs/awards)
    # Expected: Skills matching + Degree alignment.
    student_1 = {
        "student_name": "Baseline Bob",
        "skills": {
            "Using spreadsheet software for organizing, computing, and analyzing data (MS Excel, Google Sheets, etc.)": 4,
            "Using video conferencing or online collaboration tools (Zoom, Google Meet, MS Teams, etc.)": 4
        },
        "resume_text": "Bachelor of Science in Information Technology. Educated at FEU Tech.",
        "interview_notes": "",
        "behavioral_score": 3,
        "is_recommended": True
    }

    # Test Case 2: Resume Rose (Has degree + Experience + Certs)
    # Expected: Higher score due to experience and certifications.
    student_2 = {
        "student_name": "Resume Rose",
        "skills": {
            "Using spreadsheet software for organizing, computing, and analyzing data (MS Excel, Google Sheets, etc.)": 4,
            "Using video conferencing or online collaboration tools (Zoom, Google Meet, MS Teams, etc.)": 4
        },
        "resume_text": (
            "Bachelor of Science in Information Technology.\n"
            "Work Experience: Web Developer Intern at Tech Solutions Inc. (June 2024 - August 2024)\n"
            "Developed responsive user interface components using HTML, CSS, JavaScript, and Excel.\n"
            "Certifications: Microsoft Certified Professional."
        ),
        "interview_notes": "",
        "behavioral_score": 3,
        "is_recommended": True
    }

    # Test Case 3: Interview Ian (Has degree + Experience + Certs + Awards + Interview Boost)
    # Expected: Highest score because of all factors + interview boost.
    student_3 = {
        "student_name": "Interview Ian",
        "skills": {
            "Using spreadsheet software for organizing, computing, and analyzing data (MS Excel, Google Sheets, etc.)": 4,
            "Using video conferencing or online collaboration tools (Zoom, Google Meet, MS Teams, etc.)": 4
        },
        "resume_text": (
            "Bachelor of Science in Information Technology.\n"
            "Work Experience: Web Developer Intern.\n"
            "Certifications: Microsoft Certified Professional.\n"
            "Awards: Hackathon Champion 2024."
        ),
        "interview_notes": "The candidate mentioned they use Zoom daily for school projects.",
        "behavioral_score": 3,
        "is_recommended": True
    }

    # Test Case 4: Penalty Pete (Needs Training Penalty)
    # Expected: Low score despite credentials due to Recommendation Penalty (50% reduction).
    student_4 = {
        "student_name": "Penalty Pete",
        "skills": {
            "Using spreadsheet software for organizing, computing, and analyzing data (MS Excel, Google Sheets, etc.)": 5,
            "Using video conferencing or online collaboration tools (Zoom, Google Meet, MS Teams, etc.)": 5
        },
        "resume_text": "Bachelor of Science in Information Technology. Work Experience: Web Developer Intern.",
        "interview_notes": "",
        "behavioral_score": 2,
        "is_recommended": False
    }

    results = run_rete_matching([student_1, student_2, student_3, student_4], rules)
    
    for name, res in results.items():
        score = res[0]['match_score']
        confidence = res[0]['confidence']
        bk = res[0]['breakdown']
        print(f"Student: {name:15} | Match Score: {score:3}% | Confidence: {confidence:10}")
        print(f"  - Competency Index: {bk['competency_index']}%")
        print(f"    * Skills (TOPSIS): {bk['skill_percentage']}%")
        print(f"    * Degree Align: {bk['degree_alignment']}%")
        print(f"    * Experience: {bk['experience_score']}%")
        print(f"    * Certifications: {bk['certification_score']}%")
        print(f"    * Awards: {bk['awards_score']}%")
        
        # Skill-specific validation log
        for skill in bk['skills_breakdown']:
            status = "None"
            if skill['resume_match']: status = "Resume (1.2x)"
            if skill['interview_match']: status = "Interview (1.3x)"
            print(f"    * Skill: {skill['skill'][:30]}... | Boost: {status}")

if __name__ == "__main__":
    test_matching_logic()
