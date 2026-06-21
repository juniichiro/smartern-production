import sys
import json
import re
import math

def get_safe_str(val):
    return str(val if val is not None else "").strip().lower()

def get_safe_int(val):
    try:
        return int(val) if val is not None else 0
    except (ValueError, TypeError):
        return 0

def ensure_dict(val):
    if isinstance(val, dict): return val
    return {}

def extract_keywords(skill_text):
    """
    Dynamically extracts search keywords from long competency descriptions.
    Focuses on parenthetical tools and descriptive nouns.
    """
    skill_lower = skill_text.lower()
    keywords = set()
    
    # 1. Extract content within parentheses (e.g., MS Word, Google Docs)
    parentheses_matches = re.findall(r'\((.*?)\)', skill_lower)
    for match in parentheses_matches:
        parts = [p.strip() for p in match.split(',')]
        for p in parts:
            # Clean etc. and non-alphanumeric noise
            p_clean = re.sub(r'\betc\.?\b', '', p).strip()
            if p_clean and len(p_clean) > 1:
                keywords.add(p_clean)

    # 2. Heuristic mapping for common competency themes
    if 'spreadsheet' in skill_lower: keywords.add('excel')
    if 'word processing' in skill_lower: keywords.add('word')
    if 'presentation' in skill_lower: keywords.add('powerpoint'); keywords.add('canva')
    if 'troubleshooting' in skill_lower: keywords.add('hardware'); keywords.add('repair')
    if 'programming' in skill_lower: keywords.add('coding'); keywords.add('developer')
    if 'data encoding' in skill_lower: keywords.add('data entry')
    if 'correspondence' in skill_lower: keywords.add('letter'); keywords.add('memos')
    
    return list(keywords)

def run_rete_matching(students, rules_by_office):
    # Ensure rules_by_office is a dictionary
    if isinstance(rules_by_office, list):
        rules_by_office = {}

    # 1. Compile Rete Network: Extract unique skill requirements across all rules (Alpha nodes)
    unique_skills = set()
    for office_id, office_rules in rules_by_office.items():
        for rule in office_rules:
            skill = get_safe_str(rule.get('rule_value', ''))
            if skill:
                unique_skills.add(skill)

    batch_results = {}

    for student in students:
        student_name = student.get('student_name', 'Unknown')
        university = student.get('university', '')
        
        # Student data sources
        self_reported = {get_safe_str(k): get_safe_int(v) for k, v in ensure_dict(student.get('skills', {})).items()}
        verified = {get_safe_str(k): get_safe_int(v) for k, v in ensure_dict(student.get('verified_skills', {})).items()}
        resume_text = get_safe_str(student.get('resume_text', ''))
        interview_notes = get_safe_str(student.get('interview_notes', ''))
        
        # Behavioral & Recommendation parameters
        behavioral_score = get_safe_int(student.get('behavioral_score', 3))
        is_recommended = student.get('is_recommended', True)

        # 2. Alpha Memory Evaluation
        alpha_memory = {}
        resume_matched_skills = set()
        interview_matched_skills = set()
        verified_matched_skills = set()

        for skill in unique_skills:
            # Base score from self-reported or verified
            base_score = 0
            weight = 1.0
            
            if skill in verified:
                base_score = verified[skill]
                weight = 1.5
                verified_matched_skills.add(skill)
            elif skill in self_reported:
                base_score = self_reported[skill]
                weight = 1.0
            
            # Dynamic Keyword extraction for triangulation
            keywords = extract_keywords(skill)
            
            # Check Resume Triangulation
            in_resume = (skill in resume_text)
            if not in_resume:
                # Check if any keyword matches as a whole word
                for kw in keywords:
                    if re.search(r'\b' + re.escape(kw) + r'\b', resume_text):
                        in_resume = True
                        break
            
            if in_resume:
                resume_matched_skills.add(skill)
                # Resume boost (1.2x) if self-reported and not yet verified
                if skill not in verified and weight < 1.2:
                    weight = 1.2

            # Check Interview Triangulation (Independent Criteria)
            in_interview = (skill in interview_notes)
            if not in_interview:
                for kw in keywords:
                    if re.search(r'\b' + re.escape(kw) + r'\b', interview_notes):
                        in_interview = True
                        break
            
            if in_interview:
                interview_matched_skills.add(skill)
                # Interview boost (1.3x) takes priority over resume boost
                if skill not in verified and weight < 1.3:
                    weight = 1.3
            
            # Calculate final effective weighted score for this skill fact
            alpha_memory[skill] = base_score * weight

        student_results = []

        # 3. Beta Memory Evaluation (Composite Join & Sorter)
        for office_id, office_rules in rules_by_office.items():
            office_id_int = int(office_id)
            
            valid_rules = []
            for rule in office_rules:
                rule_skill = get_safe_str(rule.get('rule_value', ''))
                if rule_skill:
                    valid_rules.append({
                        'skill': rule_skill,
                        'priority': get_safe_int(rule.get('priority', 1))
                    })
            
            if not valid_rules:
                student_results.append({
                    'office_id': office_id_int,
                    'match_score': 0,
                    'confidence': 'Low',
                    'breakdown': { 'is_recommended': is_recommended, 'has_overlap': False }
                })
                continue

            # Check context overlap
            has_overlap = False
            for r in valid_rules:
                if alpha_memory.get(r['skill'], 0) > 0 or r['skill'] in resume_matched_skills or r['skill'] in interview_matched_skills:
                    has_overlap = True
                    break
            
            if not has_overlap:
                student_results.append({
                    'office_id': office_id_int,
                    'match_score': 0,
                    'confidence': 'Low',
                    'breakdown': { 'is_recommended': is_recommended, 'has_overlap': False }
                })
                continue

            # 4. Fuzzy TOPSIS Calculation Layer
            sum_sq_dist_pis = 0.0
            sum_sq_dist_nis = 0.0

            verified_count = 0
            resume_count = 0
            interview_count = 0
            skills_breakdown = []
            
            for r in valid_rules:
                skill = r['skill']
                priority = r['priority']
                x_ij = alpha_memory.get(skill, 0.0)
                
                # pis_j = 7.5 (max possible: 5 * 1.5)
                sum_sq_dist_pis += (priority * (7.5 - x_ij)) ** 2
                sum_sq_dist_nis += (priority * (x_ij - 0.0)) ** 2

                if skill in verified_matched_skills: verified_count += 1
                if skill in resume_matched_skills: resume_count += 1
                if skill in interview_matched_skills: interview_count += 1

                skills_breakdown.append({
                    'skill': skill,
                    'priority': priority,
                    'self_reported': self_reported.get(skill, 0),
                    'verified': verified.get(skill, None),
                    'resume_match': skill in resume_matched_skills,
                    'interview_match': skill in interview_matched_skills,
                    'effective_score': round(x_ij, 2)
                })

            d_plus = math.sqrt(sum_sq_dist_pis)
            d_minus = math.sqrt(sum_sq_dist_nis)

            skill_percentage = (d_minus / (d_plus + d_minus)) * 100.0 if (d_plus + d_minus) > 0 else 0.0

            # 5. Integrate Behavioral Score (20% share)
            behavioral_percentage = (behavioral_score / 5.0) * 100.0
            final_score = (skill_percentage * 0.8) + (behavioral_percentage * 0.2)

            # 6. Recommendation Penalty
            if not is_recommended or is_recommended == 'Needs More Training' or is_recommended is False:
                final_score *= 0.5

            rounded_score = min(max(round(final_score), 0), 100)

            # Confidence Level: High if verified, Medium if triangulated by resume OR interview
            confidence = "Low"
            if verified_count > 0:
                confidence = "High"
            elif resume_count > 0 or interview_count > 0:
                confidence = "Medium"

            student_results.append({
                'office_id': office_id_int,
                'match_score': rounded_score,
                'confidence': confidence,
                'breakdown': {
                    'is_recommended': is_recommended,
                    'has_overlap': True,
                    'behavioral_score': behavioral_score,
                    'behavioral_percentage': round(behavioral_percentage, 2),
                    'skills_breakdown': skills_breakdown,
                    'skill_percentage': round(skill_percentage, 2),
                    'final_score': rounded_score
                }
            })
            
        batch_results[student_name] = student_results
        
    return batch_results

if __name__ == "__main__":
    try:
        input_raw = sys.stdin.read()
        if not input_raw:
            print(json.dumps([]))
            sys.exit(0)

        input_data = json.loads(input_raw)
        students = input_data.get('students', [])
        rules_by_office = input_data.get('rules', {})
        
        batch_results = run_rete_matching(students, rules_by_office)
        print(json.dumps(batch_results))

    except Exception as e:
        print(json.dumps({"error": str(e)}))
