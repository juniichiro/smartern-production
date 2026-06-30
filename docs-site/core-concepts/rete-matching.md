# Automated Office Placement (Rete Matching)

SmarTern incorporates an automated office placement module based on the **Rete Pattern Matching Algorithm** to match student skills, interests, and academic background to office roles.

---

## What is the Rete Algorithm?

The Rete matching algorithm is an efficient pattern-matching algorithm for production rule systems. Instead of evaluating every rule against every student sequentially (which is an $O(R \times S)$ operation), Rete constructs a directed acyclic graph (network) of rules:
- **Alpha Network**: Filters incoming facts based on simple conditional attributes (e.g., `Course == 'BSIT'`, `Skills contains 'PHP'`).
- **Beta Network**: Evaluates joins between different conditional nodes (e.g., matching a student's technical level with an office's developer requirement level).
- **Conflict Resolver**: Prioritizes rules and outputs optimal matches.

This structure allows high-performance batch processing where hundreds of student profiles can be matched to available departments in seconds.

---

## Matching Criteria & Facts

Placement logic parses facts extracted from the student's profile and assessment form:
1. **Academic Background**: Major/Specialization, GPA, and school.
2. **Skills Profile**: Keywords extracted from resumes (e.g. Programming, Graphic Design, Customer Service, Bookkeeping).
3. **Student Preferences**: Department rankings chosen by the intern.
4. **Office Requirements**: Rules configured by administrators (e.g., "ICT Department requires programming skills", "HR requires communication skills").

---

## Scoring System

The matching service returns multiple options for each student, complete with a percentage match score:
- **Primary Match**: Highest matching percentage based on core requirements.
- **Alternative Match**: Secondary options that fit minor skills or secondary preferences.

Heads and supervisors can review these matches and export reports in PDF or spreadsheet formats before final deployment.
