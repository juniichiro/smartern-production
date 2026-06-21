<?php

namespace App\Services;

use App\Models\MatchResult;
use App\Models\OfficeRule;
use App\Models\OnboardingApplicant;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class SmartMatchingService
{
    /**
     * Run matching for a single applicant against all offices.
     */
    public function runMatchingForApplicant(OnboardingApplicant $applicant)
    {
        $rules = OfficeRule::all()->groupBy('office_id');
        $resumeText = $this->extractResumeText($applicant->resume_path);

        $evaluation = $applicant->evaluation;

        $studentData = [
            [
                'student_name' => $applicant->full_name,
                'university' => $applicant->university,
                'skills' => array_merge($applicant->tech_skills, $applicant->soft_skills),
                'resume_text' => $resumeText,
                'interview_notes' => $evaluation?->interview_notes ?? '',
                'verified_skills' => $evaluation?->verified_skills ?? [],
                'behavioral_score' => $evaluation?->behavioral_score ?? 3,
                'is_recommended' => $evaluation?->is_recommended ?? true,
            ],
        ];

        return $this->processMatchingBatch($studentData, $rules);
    }

    /**
     * Internal batch processor for Rete script execution.
     */
    public function processMatchingBatch(array $allStudentData, $rules)
    {
        \Log::info('Processing matching batch for '.count($allStudentData).' students.');
        try {
            $pythonResults = $this->executeBatchReteScript($allStudentData, $rules);

            if (is_array($pythonResults)) {
                \Log::info('Python results received: '.json_encode($pythonResults));
                foreach ($allStudentData as $student) {
                    $name = $student['student_name'];
                    if (isset($pythonResults[$name])) {
                        // Clear old non-confirmed results for this student
                        MatchResult::where('applicant_name', $name)->where('is_confirmed', false)->delete();

                        foreach ($pythonResults[$name] as $match) {
                            MatchResult::create([
                                'office_id' => $match['office_id'],
                                'applicant_name' => $name,
                                'school' => $student['university'],
                                'score' => $match['match_score'],
                                'confidence' => $match['confidence'] ?? 'Low',
                                'status' => $match['match_score'] >= 80 ? 'matched' : 'pending',
                                'is_confirmed' => false,
                                'breakdown' => isset($match['breakdown']) ? json_encode($match['breakdown']) : null,
                            ]);
                        }
                    } else {
                        \Log::warning("No python results found for student name: $name");
                    }
                }

                return true;
            } else {
                \Log::error('Python output is not an array: '.json_encode($pythonResults));
            }
        } catch (\Exception $e) {
            \Log::error('SmartMatchingService Error: '.$e->getMessage());
        }

        return false;
    }

    private function executeBatchReteScript($students, $rules)
    {
        $process = new Process(['python', base_path('scripts/rete_match.py')]);
        $process->setInput(json_encode(['students' => $students, 'rules' => $rules]));
        $process->run();

        if (! $process->isSuccessful()) {
            \Log::error('Rete Python Error: '.$process->getErrorOutput());

            return [];
        }

        $output = $process->getOutput();
        \Log::info('Rete Python Output: '.$output);

        return json_decode($output, true);
    }

    private function extractResumeText($path)
    {
        if (! $path) {
            return '';
        }

        $fullPath = Storage::disk('public')->path($path);
        if (! file_exists($fullPath)) {
            return '';
        }

        $process = new Process(['python', base_path('scripts/pdf_parser.py'), $fullPath]);
        $process->run();

        if (! $process->isSuccessful()) {
            return '';
        }

        $output = json_decode($process->getOutput(), true);

        return $output['text'] ?? '';
    }
}
