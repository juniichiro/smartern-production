import fitz  # PyMuPDF
import sys
import json
import re

def extract_smart_data(pdf_path):
    try:
        doc = fitz.open(pdf_path)
        
        spans = []
        full_text_list = []

        for page in doc:
            page_dict = page.get_text("dict")
            for block in page_dict["blocks"]:
                if block["type"] == 0: # text block
                    for line in block["lines"]:
                        for span in line["spans"]:
                            text = span["text"].strip()
                            if text:
                                spans.append({
                                    "text": text,
                                    "size": span["size"],
                                    "flags": span["flags"], # bit four is bold
                                    "y": span["origin"][1],
                                    "x": span["origin"][0],
                                    "bbox": span["bbox"]
                                })
                                full_text_list.append(text)

        raw_text = " ".join(full_text_list)
        
        # VISUAL-FIRST NAME EXTRACTION
        forbidden_keywords = {
            'resume', 'cv', 'curriculum', 'vitae', 'profile', 'contact', 'experience', 
            'education', 'skills', 'summary', 'about', 'objective', 'certification',
            'references', 'language', 'hobbies', 'personal', 'projects', 'information',
            'address', 'birth', 'birthplace', 'nationality', 'religion', 'status', 'gender',
            'sex', 'age', 'date', 'place', 'location', 'residence', 'province', 'city', 'street',
            'university', 'college', 'school', 'academy', 'institute', 'polytechnic', 'foundation',
            'affiliation', 'affiliations', 'membership', 'memberships', 'organizations',
            'awards', 'publications', 'interests', 'career', 'tech stack', 'my stack'
        }
        
        disqualifiers = {
            'intern', 'engineer', 'developer', 'manager', 'specialist', 'analyst', 
            'student', 'graduate', 'candidate', 'associate', 'assistant', 'senior', 
            'junior', 'lead', 'head', 'officer', 'coordinator', 'supervisor', 
            'consultant', 'freelancer', 'professional', 'expert', 'practitioner',
            'philippines', 'manila', 'city', 'province', 'barangay', 'brgy', 'st.', 'ave', 'road',
            'university', 'college', 'school', 'academy', 'institute', 'polytechnic', 'foundation',
            'elementary', 'secondary', 'tertiary', 'bachelor', 'degree', 'master', 'doctor', 'phd'
        }
        
        name_candidates = []
        for i, span in enumerate(spans[:60]):
            txt = span["text"].strip()
            clean_txt = re.sub(r'^(Name|Full Name|Applicant|Name of Student):\s*', '', txt, flags=re.IGNORECASE).strip()
            
            # Comma Split
            if ',' in clean_txt:
                parts = clean_txt.split(',')
                if len(parts[0].split()) >= 1:
                    clean_txt = parts[0].strip()

            lower_txt = clean_txt.lower()
            
            if (not any(char.isdigit() for char in clean_txt) and 
                all(key not in lower_txt for key in ['@', '.com', 'http', '/', '(', ')', '|', '\\']) and
                lower_txt not in forbidden_keywords and
                not any(d in lower_txt.split() for d in disqualifiers) and
                len(clean_txt) < 50):
                
                score = span["size"] * 12
                if span["flags"] & 2**4: score += 60
                if span["y"] < 150: score += 40
                score -= (span["y"] / 10)

                # Multi-line
                final_combined = clean_txt
                if i + 1 < len(spans):
                    next_s = spans[i+1]
                    y_diff = next_s["y"] - span["y"]
                    if 0 < y_diff < (span["size"] * 2.8) and abs(next_s["size"] - span["size"]) < 3:
                        next_t = next_s["text"].strip()
                        if next_t.lower() not in forbidden_keywords and not any(d in next_t.lower().split() for d in disqualifiers):
                            final_combined = clean_txt + " " + next_t
                            score += 50

                if len(final_combined.split()) >= 2:
                    name_candidates.append((final_combined, score))

        name_candidates.sort(key=lambda x: x[1], reverse=True)
        final_name = name_candidates[0][0] if name_candidates else ""

        # UNIVERSITY EXTRACTION
        candidate_uni = ""
        uni_keywords = {'university', 'college', 'school', 'academy', 'institute', 'polytechnic', 'foundation'}
        for span in spans[:50]:
            txt = span["text"].strip()
            lower_txt = txt.lower()
            if any(key in lower_txt for key in uni_keywords) and len(txt) < 100:
                if not any(d in lower_txt for d in ['bachelor', 'degree', 'major', 'minor', 'graduate']):
                    candidate_uni = txt
                    break

        # CONTACT EXTRACTION
        email = ""
        # Stricter Email: Must not be followed by a DOI or look like a citation
        email_match = re.search(r'[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}', raw_text)
        if email_match: 
            cand_email = email_match.group(0).lower()
            # Basic block for common non-personal academic emails if needed, but usually just length check
            if len(cand_email) < 60:
                email = cand_email

        phone = ""
        # Strict Phone (PH Optimized): Must look like nine... or +six hundred and thirty-nine... or landline with area code
        # Prevents matching strings like "two thousand and twenty-four.twofourfoureighteighteightfive"
        ph_patterns = [
            r'(?:\+63|0)9\d{9}',                # Mobile: nineonesevenonetwothreefourfivesixseven or +sixthreenineonesevenonetwothreefourfivesixseven
            r'(?:\+63|0)2\d{8}',                # Manila Landline: twoeightonetwothreefourfivesixseven
            r'(?:\+63|0)[3-9]\d\d{7}',          # Provincial Landline
            r'\d{3}-\d{3}-\d{4}'                # General formatted: ninety-one-seven hundred and twelve-three thousand and four hundred and fifty-six (less common in PH but valid)
        ]
        
        for pattern in ph_patterns:
            match = re.search(pattern, raw_text.replace(" ", "").replace("-", ""))
            if match:
                phone = match.group(0)
                # Re-format to a cleaner string if needed, but raw is okay for now as long as it's valid
                break

        return {
            "name": final_name,
            "university": candidate_uni,
            "email": email,
            "phone": phone,
            "text": raw_text
        }

    except Exception as e:
        return {"error": str(e)}

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No PDF path provided"}))
        sys.exit(1)
    
    result = extract_smart_data(sys.argv[1])
    print(json.dumps(result))
