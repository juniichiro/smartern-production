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
    
    # Extract content within parentheses (e.g., MS Word, Google Docs)
    parentheses_matches = re.findall(r'\((.*?)\)', skill_lower)
    for match in parentheses_matches:
        parts = [p.strip() for p in match.split(',')]
        for p in parts:
            # Clean etc. and non-alphanumeric noise
            p_clean = re.sub(r'\betc\.?\b', '', p).strip()
            if p_clean and len(p_clean) > 1:
                keywords.add(p_clean)

    # Heuristic mapping for common competency themes
    if 'spreadsheet' in skill_lower: keywords.add('excel')
    if 'word processing' in skill_lower: keywords.add('word')
    if 'presentation' in skill_lower: keywords.add('powerpoint'); keywords.add('canva')
    if 'troubleshooting' in skill_lower: keywords.add('hardware'); keywords.add('repair')
    if 'programming' in skill_lower: keywords.add('coding'); keywords.add('developer')
    if 'data encoding' in skill_lower: keywords.add('data entry')
    if 'correspondence' in skill_lower: keywords.add('letter'); keywords.add('memos')
    
    return list(keywords)

def evaluate_resume_factors(resume_text, office_name):
    """
    Heuristically scores degree alignment, work experience, certifications, and awards
    based on the office name domain and key indicators in the resume text.
    """
    resume_lower = resume_text.lower()
    office_lower = office_name.lower()
    
    # 1. Determine Office Domain
    domain = 'admin'
    if any(k in office_lower for k in ['it', 'ict', 'tech', 'computer', 'system', 'developer', 'network']):
        domain = 'ict'
    elif any(k in office_lower for k in ['hr', 'human', 'resource', 'psych']):
        domain = 'hr'
    elif any(k in office_lower for k in ['finance', 'accounting', 'budget', 'audit', 'treasury']):
        domain = 'finance'
        
    # 2. Degree Alignment (Da) - 15% weight
    degree_alignment = 0.0
    
    ict_degrees_strong = ['information technology', 'computer science', 'computer engineering', 'wma', 'information systems', 'software engineering']
    ict_degrees_partial = ['engineering', 'mathematics', 'statistics', 'science']
    
    hr_degrees_strong = ['human resource', 'psychology', 'bsba-hr', 'behavioral science', 'office administration']
    hr_degrees_partial = ['communications', 'sociology', 'social work', 'education']
    
    finance_degrees_strong = ['accountancy', 'accounting', 'finance', 'financial management', 'bsba-fm']
    finance_degrees_partial = ['economics', 'mathematics', 'statistics', 'banking']
    
    admin_degrees_strong = ['office administration', 'public administration', 'business administration', 'management', 'secretarial']
    admin_degrees_partial = ['communications', 'english', 'humanities']
    
    if domain == 'ict':
        if any(d in resume_lower for d in ict_degrees_strong):
            degree_alignment = 100.0
        elif any(d in resume_lower for d in ict_degrees_partial):
            degree_alignment = 50.0
    elif domain == 'hr':
        if any(d in resume_lower for d in hr_degrees_strong):
            degree_alignment = 100.0
        elif any(d in resume_lower for d in hr_degrees_partial):
            degree_alignment = 50.0
    elif domain == 'finance':
        if any(d in resume_lower for d in finance_degrees_strong):
            degree_alignment = 100.0
        elif any(d in resume_lower for d in finance_degrees_partial):
            degree_alignment = 50.0
    else:  # admin
        if any(d in resume_lower for d in admin_degrees_strong):
            degree_alignment = 100.0
        elif any(d in resume_lower for d in admin_degrees_partial):
            degree_alignment = 50.0
            
    # 3. Work Experience Alignment (Ea) - 15% weight
    experience_score = 0.0
    has_experience_section = any(k in resume_lower for k in ['work experience', 'professional experience', 'employment history', 'job history', 'past experience', 'experience'])
    
    if has_experience_section or any(k in resume_lower for k in ['internship', 'web developer intern', 'work history', 'intern']):
        experience_score = 50.0  # Baseline for having any experience
        
        ict_exp_keywords = ['developer', 'programmer', 'software', 'code', 'coding', 'web', 'mobile', 'database', 'sql', 'sysadmin', 'support', 'helpdesk']
        hr_exp_keywords = ['recruitment', 'hiring', 'interview', 'training', 'payroll', 'personnel', 'human resource']
        finance_exp_keywords = ['accounting', 'bookkeeping', 'audit', 'tax', 'billing', 'ledger', 'invoice', 'cashier']
        admin_exp_keywords = ['administrative', 'clerk', 'secretary', 'filing', 'archive', 'record', 'document', 'typing']
        
        if domain == 'ict' and any(w in resume_lower for w in ict_exp_keywords):
            experience_score = 100.0
        elif domain == 'hr' and any(w in resume_lower for w in hr_exp_keywords):
            experience_score = 100.0
        elif domain == 'finance' and any(w in resume_lower for w in finance_exp_keywords):
            experience_score = 100.0
        elif domain == 'admin' and any(w in resume_lower for w in admin_exp_keywords):
            experience_score = 100.0

    # 4. Trainings & Certifications (Tc) - 10% weight
    certification_score = 0.0
    has_cert_section = any(k in resume_lower for k in ['certifications', 'training', 'courses', 'credentials', 'certified user'])
    
    if has_cert_section or any(k in resume_lower for k in ['certified', 'certificate', 'course']):
        certification_score = 50.0  # Baseline
        
        ict_cert_keywords = ['microsoft', 'aws', 'azure', 'cisco', 'oracle', 'python', 'java', 'kotlin', 'ccna', 'itil', 'scrum']
        hr_admin_cert_keywords = ['six sigma', 'pmp', 'project management', 'ms word', 'ms office', 'sap']
        finance_cert_keywords = ['cpa', 'cma', 'bookkeeping', 'sap', 'quickbooks', 'ms excel']
        
        if domain == 'ict' and any(w in resume_lower for w in ict_cert_keywords):
            certification_score = 100.0
        elif domain in ['hr', 'admin'] and any(w in resume_lower for w in hr_admin_cert_keywords):
            certification_score = 100.0
        elif domain == 'finance' and any(w in resume_lower for w in finance_cert_keywords):
            certification_score = 100.0

    # 5. Awards & Recognition (Ar) - 10% weight
    awards_score = 0.0
    has_awards = any(k in resume_lower for k in ['awards', 'recognition', 'honors', 'achievements', 'accomplishments', 'scholar'])
    
    if has_awards or any(k in resume_lower for k in ['champion', 'winner', 'first place', 'outstanding', 'dean\'s list', 'honor']):
        awards_score = 50.0  # Baseline
        
        relevant_keywords = []
        if domain == 'ict':
            relevant_keywords = ['hackathon', 'quiz bee', 'it', 'programming', 'tech', 'science']
        elif domain == 'finance':
            relevant_keywords = ['accounting', 'finance', 'business', 'math']
        elif domain == 'hr':
            relevant_keywords = ['outstanding', 'leadership', 'organization']
        elif domain == 'admin':
            relevant_keywords = ['outstanding', 'academic', 'service']
            
        if any(w in resume_lower for w in relevant_keywords):
            awards_score = 100.0

    return {
        'degree_alignment': degree_alignment,
        'experience_score': experience_score,
        'certification_score': certification_score,
        'awards_score': awards_score
    }

def run_rete_matching(students, rules_by_office):
    # Ensure rules_by_office is a dictionary
    if isinstance(rules_by_office, list):
        rules_by_office = {}

    # Compile Rete Network: Extract unique skill requirements across all rules (Alpha nodes)
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

        # Alpha Memory Evaluation
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

        # Beta Memory Evaluation (Composite Join & Sorter)
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
            
            # Retrieve Office Name to identify domain for factor scoring
            office_name = ""
            if office_rules:
                first_rule = office_rules[0]
                office_obj = ensure_dict(first_rule.get('office', {}))
                office_name = get_safe_str(office_obj.get('name', ''))
            
            # Fallback to general domains if office name not provided
            if not office_name:
                if office_id_int == 1: office_name = "ict department"
                elif office_id_int == 2: office_name = "human resources"
                elif office_id_int == 3: office_name = "finance and accounting"
                else: office_name = "administrative services"

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

            # Fuzzy TOPSIS Calculation Layer
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

            # Evaluate multi-factor resume components
            factors = evaluate_resume_factors(resume_text, office_name)
            degree_alignment = factors['degree_alignment']
            experience_score = factors['experience_score']
            certification_score = factors['certification_score']
            awards_score = factors['awards_score']

            # Calculate composite competency index (Ci)
            # Weights: Skills (50%), Degree (15%), Experience (15%), Certifications (10%), Awards (10%)
            ci = (skill_percentage * 0.50) + (degree_alignment * 0.15) + (experience_score * 0.15) + (certification_score * 0.10) + (awards_score * 0.10)

            # Integrate Behavioral Score (20% share)
            behavioral_percentage = (behavioral_score / 5.0) * 100.0
            final_score = (ci * 0.8) + (behavioral_percentage * 0.2)

            # Recommendation Penalty
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
                    'degree_alignment': round(degree_alignment, 2),
                    'experience_score': round(experience_score, 2),
                    'certification_score': round(certification_score, 2),
                    'awards_score': round(awards_score, 2),
                    'competency_index': round(ci, 2),
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
        rules = input_data.get('rules', {})
        
        batch_results = run_rete_matching(students, rules)
        print(json.dumps(batch_results))

    except Exception as e:
        print(json.dumps({"error": str(e)}))
