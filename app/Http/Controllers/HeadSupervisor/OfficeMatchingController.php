<?php

namespace App\Http\Controllers\HeadSupervisor;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\HeadSupervisor\FinalizeMatchBatchRequest;
use App\Http\Requests\HeadSupervisor\StoreEvaluationRequest;
use App\Http\Requests\HeadSupervisor\UploadAssessmentRequest;
use App\Http\Requests\HeadSupervisor\UndoFinalizeRequest;
use App\Mail\OfficeAssignmentFinalizedMail;
use App\Models\ApplicantEvaluation;
use Illuminate\Support\Facades\Mail;
use App\Models\AssessmentItem;
use App\Models\MatchResult;
use App\Models\Office;
use App\Models\OfficeRule;
use App\Models\OnboardingApplicant;
use App\Services\SecurityAuditService;
use App\Services\SmartMatchingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Illuminate\Support\Str;

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
        // Sorted by Pending first, then Finalized (Confirmed matches), and both sorted newest first
        $applicants = OnboardingApplicant::with('evaluation')
            ->orderByRaw('(SELECT COUNT(*) FROM match_results WHERE match_results.applicant_name = onboarding_applicants.full_name AND match_results.is_confirmed = 1) ASC')
            ->orderBy('onboarding_applicants.created_at', 'desc')
            ->paginate(10);

        // Map MatchResult scores for these applicants
        $results = MatchResult::with('office')
            ->whereIn('applicant_name', $applicants->pluck('full_name'))
            ->get()
            ->groupBy('applicant_name');

        $totalApplicants = OnboardingApplicant::count();
        $confirmedApplicants = MatchResult::where('is_confirmed', true)->distinct('applicant_name')->count();

        // Get emails of already registered users to check if account is created
        $existingEmails = \App\Models\User::whereIn('email', $applicants->pluck('email'))
            ->pluck('email')
            ->toArray();

        $stats = [
            'total' => $totalApplicants,
            'average' => MatchResult::avg('score') ?? 0,
            'pending' => $totalApplicants - $confirmedApplicants,
        ];

        return view('head-supervisor.office-matching', compact('results', 'applicants', 'stats', 'offices', 'existingEmails'));
    }

    public function storeEvaluation(StoreEvaluationRequest $request, OnboardingApplicant $applicant)
    {
        if ($request->hasFile('resume_pdf')) {
            $resumePath = $request->file('resume_pdf')->store('resumes', 'public');
            $applicant->update(['resume_path' => $resumePath]);
        }

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
        $handle = fopen($file->getRealPath(), 'r');
        
        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return back()->withErrors(['error' => 'The uploaded CSV file is empty.']);
        }
        
        $headerMap = array_flip(array_map('trim', $headers));

        $rules = OfficeRule::all()->groupBy('office_id');
        $activeAssessments = AssessmentItem::active()->get();
        
        $competencies = [];
        foreach ($activeAssessments as $item) {
            $competencies[trim($item->competency_text)] = $item->category;
        }

        $allStudentData = [];
        $preassignments = [];

        while (($data = fgetcsv($handle)) !== false) {
            // Safeguard against empty or corrupt rows
            if (empty($data) || count($data) < 2) {
                continue;
            }

            // Find columns dynamically based on headers
            $nameHeader = isset($headerMap['student_name']) ? 'student_name' : (isset($headerMap['full_name']) ? 'full_name' : null);
            if (!$nameHeader || !isset($data[$headerMap[$nameHeader]]) || empty(trim($data[$headerMap[$nameHeader]]))) {
                continue;
            }
            $applicantName = trim($data[$headerMap[$nameHeader]]);

            $preassignedOfficeId = isset($headerMap['office_id']) && isset($data[$headerMap['office_id']]) && ! empty(trim($data[$headerMap['office_id']])) ? (int) $data[$headerMap['office_id']] : null;

            if ($preassignedOfficeId) {
                $preassignments[$applicantName] = $preassignedOfficeId;
            } else {
                if (MatchResult::where('applicant_name', $applicantName)->where('is_confirmed', true)->exists()) {
                    continue;
                }
            }

            // Check if we have this applicant in our DB (Smart Matching)
            $applicant = OnboardingApplicant::where('full_name', $applicantName)->first();

            $techSkillsFormatted = [];
            $softSkillsFormatted = [];

            // 1. Check if skills columns exist individually as columns in the CSV
            foreach ($competencies as $text => $category) {
                if (isset($headerMap[$text]) && isset($data[$headerMap[$text]])) {
                    $score = is_numeric($data[$headerMap[$text]]) ? (int)$data[$headerMap[$text]] : 3;
                    if (str_contains(strtolower($category), 'technical')) {
                        $techSkillsFormatted[$text] = $score;
                    } else {
                        $softSkillsFormatted[$text] = $score;
                    }
                }
            }

            // 2. Fallback to legacy semicolon-separated format if legacy columns are present
            if (empty($techSkillsFormatted) && isset($headerMap['tech_skills']) && isset($data[$headerMap['tech_skills']])) {
                $rawTech = ! empty($data[$headerMap['tech_skills']]) ? explode(';', $data[$headerMap['tech_skills']]) : [];
                foreach ($rawTech as $pair) {
                    if (str_contains($pair, ':')) {
                        [$skill, $score] = explode(':', $pair);
                        $techSkillsFormatted[trim($skill)] = (int) $score;
                    }
                }
            }

            if (empty($softSkillsFormatted) && isset($headerMap['soft_skills']) && isset($data[$headerMap['soft_skills']])) {
                $rawSoft = ! empty($data[$headerMap['soft_skills']]) ? explode(';', $data[$headerMap['soft_skills']]) : [];
                foreach ($rawSoft as $pair) {
                    if (str_contains($pair, ':')) {
                        [$skill, $score] = explode(':', $pair);
                        $softSkillsFormatted[trim($skill)] = (int) $score;
                    }
                }
            }

            // 3. Fill in any active assessment items missing from both formats
            foreach ($activeAssessments as $item) {
                $compText = trim($item->competency_text);
                if (str_contains(strtolower($item->category), 'technical')) {
                    if (! isset($techSkillsFormatted[$compText])) {
                        $techSkillsFormatted[$compText] = 3;
                    }
                } else {
                    if (! isset($softSkillsFormatted[$compText])) {
                        $softSkillsFormatted[$compText] = 3;
                    }
                }
            }

            $emailVal = isset($headerMap['email']) && isset($data[$headerMap['email']]) ? trim($data[$headerMap['email']]) : ('pending_' . strtolower(Str::random(5)) . '@smarttern.com');
            $universityVal = isset($headerMap['university']) && isset($data[$headerMap['university']]) ? trim($data[$headerMap['university']]) : 'N/A';

            if (! $applicant) {
                $applicantId = 'APP-' . strtoupper(Str::random(8));
                while (OnboardingApplicant::where('applicant_id', $applicantId)->exists()) {
                    $applicantId = 'APP-' . strtoupper(Str::random(8));
                }

                $parts = explode(' ', $applicantName);
                $firstName = $parts[0] ?? 'Intern';
                $middleName = '';
                $lastName = 'Account';
                if (count($parts) >= 3) {
                    $firstName = $parts[0] . ' ' . $parts[1];
                    $lastName = end($parts);
                } elseif (count($parts) == 2) {
                    $firstName = $parts[0];
                    $lastName = $parts[1];
                }

                $genderVal = isset($headerMap['gender']) && isset($data[$headerMap['gender']]) ? trim($data[$headerMap['gender']]) : 'male';
                $phoneVal = isset($headerMap['phone_number']) && isset($data[$headerMap['phone_number']]) ? trim($data[$headerMap['phone_number']]) : '09000000000';
                $emergencyVal = isset($headerMap['emergency_contact']) && isset($data[$headerMap['emergency_contact']]) ? trim($data[$headerMap['emergency_contact']]) : 'N/A';

                $applicant = OnboardingApplicant::create([
                    'applicant_id' => $applicantId,
                    'full_name' => $applicantName,
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'email' => $emailVal,
                    'university' => $universityVal,
                    'tech_skills' => $techSkillsFormatted,
                    'soft_skills' => $softSkillsFormatted,
                    'gender' => $genderVal,
                    'phone_number' => $phoneVal,
                    'emergency_contact' => $emergencyVal,
                    'emergency_contact_number' => 'N/A',
                    'privacy_acknowledged_at' => now(),
                ]);
            } else {
                // Ensure existing applicant also gets updated to contain all items if missing
                $existingTech = is_array($applicant->tech_skills) ? $applicant->tech_skills : [];
                $existingSoft = is_array($applicant->soft_skills) ? $applicant->soft_skills : [];
                $updated = false;

                foreach ($activeAssessments as $item) {
                    $compText = trim($item->competency_text);
                    if (str_contains(strtolower($item->category), 'technical')) {
                        if (! isset($existingTech[$compText])) {
                            $existingTech[$compText] = 3;
                            $updated = true;
                        }
                    } else {
                        if (! isset($existingSoft[$compText])) {
                            $existingSoft[$compText] = 3;
                            $updated = true;
                        }
                    }
                }

                if ($updated) {
                    $applicant->update([
                        'tech_skills' => $existingTech,
                        'soft_skills' => $existingSoft,
                    ]);
                }

                // Synchronize variables to pass correct values to processMatchingBatch
                $techSkillsFormatted = $applicant->tech_skills;
                $softSkillsFormatted = $applicant->soft_skills;
            }

            $allSkills = array_merge($techSkillsFormatted, $softSkillsFormatted);

            // Smart Matching: Extract Resume Text if available
            $resumeText = '';
            if ($applicant && $applicant->resume_path) {
                $resumeText = $this->extractResumeText($applicant->resume_path);
            }

            $allStudentData[] = [
                'student_name' => $applicantName,
                'university' => $universityVal,
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

        // Apply preassigned office finalizations
        foreach ($preassignments as $name => $officeId) {
            $existingRecord = MatchResult::where('applicant_name', $name)->first();
            
            // If the matching engine created the match, make it confirmed
            $updated = MatchResult::where('applicant_name', $name)
                ->where('office_id', $officeId)
                ->update(['is_confirmed' => true]);
                
            // If it didn't exist, create it manually confirmed
            if (! $updated) {
                MatchResult::updateOrCreate(
                    [
                        'applicant_name' => $name,
                        'office_id' => $officeId,
                    ],
                    [
                        'school' => $existingRecord ? $existingRecord->school : 'N/A',
                        'score' => 0,
                        'status' => 'manual_override',
                        'is_confirmed' => true,
                    ]
                );
            }
        }

        return redirect()->route('head-supervisor.matching')
            ->with('success', 'Smart analysis updated.');
    }

    public function finalizeBatch(FinalizeMatchBatchRequest $request)
    {
        $office = Office::findOrFail($request->office_id);
        $internLimit = $office->intern_limit;

        // Count confirmed matches in this office, excluding current applicants being moved
        $currentConfirmed = MatchResult::where('office_id', $office->id)
            ->where('is_confirmed', true)
            ->whereNotIn('applicant_name', $request->applicant_names)
            ->count();

        // Count active interns already assigned to this office
        $activeInterns = $office->internDetails()->count();
        $occupiedSlots = $currentConfirmed + $activeInterns;
        $requestedCount = count($request->applicant_names);

        if ($occupiedSlots + $requestedCount > $internLimit) {
            return back()->with('error', "Cannot finalize assignment. {$office->name} has already reached its maximum limit of {$internLimit} interns.");
        }

        MatchResult::whereIn('applicant_name', $request->applicant_names)
            ->update(['is_confirmed' => false]);

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

            // Trigger email to the applicant once finalized
            $applicant = OnboardingApplicant::where('full_name', $name)->first();
            if ($applicant) {
                try {
                    Mail::to($applicant->email)->send(new OfficeAssignmentFinalizedMail($applicant, $office));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Failed to send office assignment finalized email to {$applicant->email}: " . $e->getMessage());
                }
            }
        }

        return back()->with('success', 'Assignments finalized.');
    }

    public function unfinalize(UndoFinalizeRequest $request)
    {
        $name = $request->applicant_name;

        // Block undo if an account has already been created for the applicant
        $applicant = OnboardingApplicant::where('full_name', $name)->first();
        if ($applicant && \App\Models\User::where('email', $applicant->email)->exists()) {
            return back()->with('error', "Cannot undo placement. User account has already been created for {$name}.");
        }

        // Find confirmed matches for this applicant name
        $matches = MatchResult::where('applicant_name', $name)
            ->where('is_confirmed', true)
            ->get();

        if ($matches->isEmpty()) {
            return back()->with('error', "No finalized office placement found for {$name}.");
        }

        foreach ($matches as $match) {
            $match->update(['is_confirmed' => false]);
            $officeName = $match->office ? $match->office->name : "Unknown Office";
            $this->auditService->log('Match Unfinalized', "Undid office placement for {$name} from {$officeName}");
        }

        return back()->with('success', "Placement undone for {$name}.");
    }

    public function exportCsv()
    {
        $applicants = OnboardingApplicant::all();
        $activeAssessments = AssessmentItem::active()->ordered()->get();

        if ($applicants->isEmpty()) {
            return back()->with('error', 'No onboarding applicants found to export.');
        }

        $filename = 'SmarTern_Matching_Backup_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($applicants, $activeAssessments) {
            $file = fopen('php://output', 'w');

            // Build headers dynamically
            $headerRow = ['student_id', 'student_name', 'university', 'email', 'office_id'];
            foreach ($activeAssessments as $item) {
                $headerRow[] = $item->competency_text;
            }
            fputcsv($file, $headerRow);

            foreach ($applicants as $app) {
                $confirmedMatch = MatchResult::where('applicant_name', $app->full_name)
                    ->where('is_confirmed', true)
                    ->first();
                $officeId = $confirmedMatch ? $confirmedMatch->office_id : '';

                $row = [
                    $app->applicant_id,
                    $app->full_name,
                    $app->university,
                    $app->email,
                    $officeId
                ];

                foreach ($activeAssessments as $item) {
                    $compText = $item->competency_text;
                    $score = 3;
                    if (str_contains(strtolower($item->category), 'technical')) {
                        $score = is_array($app->tech_skills) && isset($app->tech_skills[$compText]) ? $app->tech_skills[$compText] : 3;
                    } else {
                        $score = is_array($app->soft_skills) && isset($app->soft_skills[$compText]) ? $app->soft_skills[$compText] : 3;
                    }
                    $row[] = $score;
                }

                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadTemplate()
    {
        $activeAssessments = AssessmentItem::active()->ordered()->get();
        $headers = ['student_id', 'student_name', 'university', 'email', 'office_id'];
        foreach ($activeAssessments as $item) {
            $headers[] = $item->competency_text;
        }
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

        return Storage::disk('public')->response($applicant->resume_path, $applicant->full_name.'_Resume.pdf', [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$applicant->full_name.'_Resume.pdf"',
        ]);
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
            \Log::error("Failed to extract resume text: " . $e->getMessage());
            return '';
        }
    }
}
