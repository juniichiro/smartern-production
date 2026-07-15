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
     * Run matching for a single applicant against all offices
     */
    public function runMatchingForApplicant(OnboardingApplicant $applicant)
    {
        $rules = OfficeRule::with('office')->get()->groupBy('office_id');
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
     * Internal batch processor for Rete script execution
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
                        // Keep track of which offices were requested by the supervisor for this student
                        $requestedOffices = MatchResult::where('applicant_name', $name)
                            ->where('requested_by_supervisor', true)
                            ->pluck('office_id')
                            ->toArray();

                        MatchResult::where('applicant_name', $name)->where('is_confirmed', false)->delete();

                        foreach ($pythonResults[$name] as $match) {
                            $isRequested = in_array($match['office_id'], $requestedOffices);
                            MatchResult::create([
                                'office_id' => $match['office_id'],
                                'applicant_name' => $name,
                                'school' => $student['university'],
                                'score' => $match['match_score'],
                                'confidence' => $match['confidence'] ?? 'Low',
                                'status' => $match['match_score'] >= 80 ? 'matched' : 'pending',
                                'is_confirmed' => false,
                                'breakdown' => isset($match['breakdown']) ? json_encode($match['breakdown']) : null,
                                'requested_by_supervisor' => $isRequested,
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
        $pythonPath = config('services.python.path') ?: ((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'python' : 'python3');
        $process = new Process([$pythonPath, base_path('scripts/rete_match.py')]);
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

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            return '';
        }

        $tempFile = null;
        if (config('filesystems.disks.public.driver') === 's3') {
            $tempFile = tempnam(sys_get_temp_dir(), 'resume_');
            file_put_contents($tempFile, $disk->get($path));
            $fullPath = $tempFile;
        } else {
            $fullPath = $disk->path($path);
        }

        try {
            $pythonPath = config('services.python.path') ?: ((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'python' : 'python3');
            $process = new Process([$pythonPath, base_path('scripts/pdf_parser.py'), $fullPath]);
            $process->run();

            if ($tempFile && file_exists($tempFile)) {
                unlink($tempFile);
            }

            if (! $process->isSuccessful()) {
                return '';
            }

            $output = json_decode($process->getOutput(), true);

            return $output['text'] ?? '';
        } catch (\Exception $e) {
            if ($tempFile && file_exists($tempFile)) {
                unlink($tempFile);
            }
            \Log::error("Failed to extract resume text in SmartMatchingService: " . $e->getMessage());
            return '';
        }
    }
}
