# Automated Office Placement (Rete Matching)

This section covers the architecture of the Rete Pattern Matching placement engine, detailing how rules, facts, and the background Python script are structured.

---

## 1. Rete Algorithmic Graph & Script Integration

When a Head Supervisor executes placement matches, SmarTern delegates pattern matching to a Python module (`scripts/rete_match.py`). 

```
   Intern Profile Facts (Resume, Skills, Major, Evaluation)
                           │
                           ▼
                  ┌─────────────────┐
                  │   Alpha Nodes   │  <-- Single-attribute filters (e.g. Major == 'BSIT')
                  └────────┬────────┘
                           ▼
                  ┌─────────────────┐
                  │   Beta Joins    │  <-- Multi-condition evaluation (e.g. Major + Skill)
                  └────────┬────────┘
                           ▼
                  ┌─────────────────┐
                  │ Conflict Resolv │  <-- Sort matches by confidence & preference
                  └────────┬────────┘
                           ▼
                     Match Results
```

### Fact Ingestion Structure
The Laravel `SmartMatchingService` serializes student parameters into facts:
```json
{
  "student_name": "John Doe",
  "university": "SDO QC Academy",
  "skills": ["PHP", "JavaScript", "SQL", "Technical Writing"],
  "resume_text": "Experienced web developer focused on backend systems and database indexing...",
  "interview_notes": "Demonstrates strong logic skills and interest in database administration.",
  "verified_skills": ["PHP", "SQL"],
  "behavioral_score": 5,
  "is_recommended": true
}
```

### Rules Compiler
Office criteria are fetched from `OfficeRule` records and grouped. Rules map skills to specific office IDs:
```json
{
  "office_id": 1,
  "office_name": "ICT Office",
  "required_skills": ["PHP", "SQL", "HTML"],
  "preferred_majors": ["BSIT", "BSCS"],
  "min_behavioral_score": 4
}
```

---

## 2. Python Script Architecture (`scripts/rete_match.py`)

The script uses a rule-based matching pattern.

### Process Invocation
The PHP service invokes the Python binary asynchronously using the Symfony Process utility:
```php
$process = new Process([$pythonPath, base_path('scripts/rete_match.py')]);
$process->setInput(json_encode(['students' => $studentData, 'rules' => $rules]));
$process->run();
```

### Python Rete Node Network Example
Within `rete_match.py`, rules are evaluated dynamically using a multi-layered Fuzzy TOPSIS (Technique for Order of Preference by Similarity to Ideal Solution) algorithm:

1. **Filter & Alpha Memory**: Triangulates student skills against requirements. The effective score `x_ij` for a skill is calculated by multiplying its base rating (1 to 5) by a source modifier:
   - Self-Reported Skill: `weight = 1.0`
   - Resume Keyword Match: `weight = 1.2`
   - Interview Notes Match: `weight = 1.3`
   - Verified Skill: `weight = 1.5`

2. **Beta Memory (Fuzzy TOPSIS Step)**:
   - Computes the weighted distance to the Positive Ideal Solution (PIS, ideal skill rating = `7.5`):
     ```
     d_plus = √[ Σ ( priority * (7.5 - x_ij) )² ]
     ```
   - Computes the weighted distance to the Negative Ideal Solution (NIS, worst rating = `0.0`):
     ```
     d_minus = √[ Σ ( priority * (x_ij - 0.0) )² ]
     ```
   - Computes the baseline Skill Percentage:
     ```
     skill_percentage = [ d_minus / (d_plus + d_minus) ] * 100
     ```

3. **Behavioral Integration & Penalty (Final Score)**:
   - Integrates the 5-point behavioral evaluation score (20% share of final score):
     ```
     behavioral_percentage = (behavioral_score / 5.0) * 100
     final_score = (skill_percentage * 0.8) + (behavioral_percentage * 0.2)
     ```
   - **Recommendation Penalty**: If the applicant is not recommended, a penalty is applied to halve the final matching score:
     ```
     final_score = final_score * 0.5
     ```
   - The result is bounded between `0` and `100`.

---

## 3. Conflict Resolution & Score Finalization

If a student qualifies for multiple departments, the Conflict Resolver sorts them:
- **Priority 1**: Highest calculated percentage score.
- **Priority 2**: Alignment with student's rank preferences.
- **Priority 3**: Available office slots (quota).

### MatchResult Generation
Matches are written to the database with a confidence indicator:
- **`High`**: Score >= 85% (skills match, recommended major, strong evaluation).
- **`Medium`**: Score 70% - 84% (meets basic skills, major differs).
- **`Low`**: Score < 70% (partial skill matching only).
- **`is_confirmed`**: Stays `false` until the Head Supervisor clicks "Confirm Placement" on the console. At that point, the status changes to `matched`, a new User account is created, and the assignment notification email is dispatched.
