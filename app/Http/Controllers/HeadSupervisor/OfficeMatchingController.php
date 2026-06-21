<?php

namespace App\Http\Controllers\HeadSupervisor;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\HeadSupervisor\FinalizeMatchBatchRequest;
use App\Http\Requests\HeadSupervisor\StoreEvaluationRequest;
use App\Http\Requests\HeadSupervisor\UploadAssessmentRequest;
use App\Models\ApplicantEvaluation;
use App\Models\MatchResult;
use App\Models\Office;
use App\Models\OfficeRule;
use App\Models\OnboardingApplicant;
use App\Services\SecurityAuditService;
use App\Services\SmartMatchingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class OfficeMatchingController extends Controller
{
    protected $auditService;

    protected $matchingService;

    public function __construct(SecurityAuditService $auditService, SmartMatchingService $matchingService)
    {
        $this->auditService = $auditService;
        $this->matchingService = $matchingService;
    }

    public function index()
    {
        $offices = Office::all();

        // Fetch applicants from the new OnboardingApplicant table
        $applicants = OnboardingApplicant::with('evaluation')->paginate(10);

        // Map MatchResult scores for these applicants
        $results = MatchResult::with('office')
            ->whereIn('applicant_name', $applicants->pluck('full_name'))
            ->get()
            ->groupBy('applicant_name');

        $totalApplicants = OnboardingApplicant::count();
        $confirmedApplicants = MatchResult::where('is_confirmed', true)->distinct('applicant_name')->count();

        $stats = [
            'total' => $totalApplicants,
            'average' => MatchResult::avg('score') ?? 0,
            'pending' => $totalApplicants - $confirmedApplicants,
        ];

        return view('head-supervisor.office-matching', compact('results', 'applicants', 'stats', 'offices'));
    }

    public function storeEvaluation(StoreEvaluationRequest $request, OnboardingApplicant $applicant)
    {
        $evaluation = ApplicantEvaluation::updateOrCreate(
            ['applicant_id' => $applicant->id],
            [
                'head_supervisor_id' => auth()->id(),
                'interview_notes' => $request->interview_notes,
                'verified_skills' => $request->verified_skills,
                'behavioral_score' => $request->behavioral_score,
                'is_recommended' => $request->is_recommended,
            ]
        );

        $this->auditService->log('Applicant Evaluation', "Evaluated intern: {$applicant->full_name}");

        // RE-ANALYSIS via Service
        $this->matchingService->runMatchingForApplicant($applicant->load('evaluation'));

        return back()->with('success', "Evaluation refined for {$applicant->full_name}.");
    }

    public function downloadGuide()
    {
        $pdf = Pdf::loadView('exports.matching-documentation');
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('SmarTern-Matching-Algorithm-Guide.pdf');
    }

    public function upload(UploadAssessmentRequest $request)
    {
        $file = $request->file('assessment_csv');
        $fileName = 'assessment_'.time().'.csv';

        $this->auditService->log('Batch Matching Upload', "Uploaded file: {$file->getClientOriginalName()}");

        $path = $file->storeAs('uploads/assessments', $fileName);
        $fullPath = Storage::path($path);

        $handle = fopen($fullPath, 'r');
        fgetcsv($handle);

        $rules = OfficeRule::all()->groupBy('office_id');
        $allStudentData = [];

        while (($data = fgetcsv($handle)) !== false) {
            $applicantName = $data[1];

            if (MatchResult::where('applicant_name', $applicantName)->where('is_confirmed', true)->exists()) {
                continue;
            }

            // Check if we have this applicant in our DB (Smart Matching)
            $applicant = OnboardingApplicant::where('full_name', $applicantName)->first();

            // Parse dynamic skills: "Skill:Score;Skill:Score"
            $allSkills = [];
            $rawTech = ! empty($data[4]) ? explode(';', $data[4]) : [];
            $rawSoft = ! empty($data[5]) ? explode(';', $data[5]) : [];

            foreach (array_merge($rawTech, $rawSoft) as $pair) {
                if (str_contains($pair, ':')) {
                    [$skill, $score] = explode(':', $pair);
                    $allSkills[trim($skill)] = (int) $score;
                }
            }

            // Smart Matching: Extract Resume Text if available
            $resumeText = '';
            if ($applicant && $applicant->resume_path) {
                $resumeText = $this->extractResumeText($applicant->resume_path);
            }

            $allStudentData[] = [
                'student_name' => $applicantName,
                'university' => $data[2],
                'skills' => $allSkills,
                'resume_text' => $resumeText,
                'verified_skills' => $applicant ? ($applicant->evaluation?->verified_skills ?? []) : [],
                'behavioral_score' => $applicant ? ($applicant->evaluation?->behavioral_score ?? 3) : 3,
                'is_recommended' => $applicant ? ($applicant->evaluation?->is_recommended ?? true) : true,
            ];
        }
        fclose($handle);

        if (! empty($allStudentData)) {
            $this->matchingService->processMatchingBatch($allStudentData, $rules);
        }

        return redirect()->route('head-supervisor.matching')
            ->with('success', 'Smart analysis updated.');
    }

    public function finalizeBatch(FinalizeMatchBatchRequest $request)
    {
        MatchResult::whereIn('applicant_name', $request->applicant_names)
            ->update(['is_confirmed' => false]);

        $office = Office::find($request->office_id);
        $count = count($request->applicant_names);
        $this->auditService->log('Match Finalized', "Assigned {$count} applicants to {$office->name}");

        foreach ($request->applicant_names as $name) {
            $match = MatchResult::where('applicant_name', $name)
                ->where('office_id', $request->office_id)
                ->first();

            if ($match) {
                $match->update(['is_confirmed' => true]);
            } else {
                $existingRecord = MatchResult::where('applicant_name', $name)->first();

                MatchResult::create([
                    'applicant_name' => $name,
                    'school' => $existingRecord ? $existingRecord->school : 'N/A',
                    'office_id' => $request->office_id,
                    'score' => 0,
                    'status' => 'manual_override',
                    'is_confirmed' => true,
                ]);
            }
        }

        return back()->with('success', 'Assignments finalized.');
    }

    public function exportCsv()
    {
        $applicants = OnboardingApplicant::all();

        if ($applicants->isEmpty()) {
            return back()->with('error', 'No onboarding applicants found to export.');
        }

        $filename = 'SmarTern_Matching_Backup_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($applicants) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'student_id',
                'student_name',
                'university',
                'email',
                'tech_skills',
                'soft_skills'
            ]);

            foreach ($applicants as $app) {
                $techPairs = [];
                if (is_array($app->tech_skills)) {
                    foreach ($app->tech_skills as $skill => $score) {
                        $techPairs[] = "{$skill}:{$score}";
                    }
                }
                $techString = implode(';', $techPairs);

                $softPairs = [];
                if (is_array($app->soft_skills)) {
                    foreach ($app->soft_skills as $skill => $score) {
                        $softPairs[] = "{$skill}:{$score}";
                    }
                }
                $softString = implode(';', $softPairs);

                fputcsv($file, [
                    $app->applicant_id,
                    $app->full_name,
                    $app->university,
                    $app->email,
                    $techString,
                    $softString
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadTemplate()
    {
        $headers = ['student_id', 'student_name', 'university', 'email', 'tech_skills', 'soft_skills'];
        $fileName = 'SmarTern_Dynamic_Template.csv';

        $callback = function () use ($headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ]);
    }

    public function clearResults()
    {
        // Only clear results that have NOT been finalized/confirmed
        MatchResult::where('is_confirmed', false)->delete();

        $this->auditService->log('Match Results Cleared', 'Removed pending matching records (Finalized assignments were preserved)');

        return redirect()->route('head-supervisor.matching')
            ->with('success', 'Matching results cleared.');
    }

    /**
     * Serve the resume PDF securely for preview.
     */
    public function viewResume(OnboardingApplicant $applicant)
    {
        if (! $applicant->resume_path || ! Storage::disk('public')->exists($applicant->resume_path)) {
            abort(404, 'Resume file not found.');
        }

        $fullPath = Storage::disk('public')->path($applicant->resume_path);

        return response()->file($fullPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$applicant->full_name.'_Resume.pdf"',
        ]);
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

        $pythonPath = env('PYTHON_PATH') ?: ((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'python' : 'python3');
        $process = new Process([$pythonPath, base_path('scripts/pdf_parser.py'), $fullPath]);
        $process->run();

        if (! $process->isSuccessful()) {
            return '';
        }

        $output = json_decode($process->getOutput(), true);

        return $output['text'] ?? '';
    }
}
