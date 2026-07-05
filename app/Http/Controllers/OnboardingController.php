<?php

namespace App\Http\Controllers;

use App\Http\Requests\ParseResumeRequest;
use App\Http\Requests\StoreOnboardingRequest;
use App\Mail\OnboardingApplicationReceivedMail;
use App\Models\AssessmentItem;
use App\Models\OnboardingApplicant;
use App\Models\Role;
use App\Models\User;
use App\Services\SmartMatchingService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class OnboardingController extends Controller
{
    private $csvPath = 'onboarding_applicants.csv';

    protected $matchingService;

    public function __construct(SmartMatchingService $matchingService)
    {
        $this->matchingService = $matchingService;
    }

    public function create()
    {
        $universities = config('universities');
        sort($universities);

        $assessmentItems = AssessmentItem::active()->ordered()->get()->groupBy('category');

        return view('onboarding.form', compact('universities', 'assessmentItems'));
    }

    public function privacyPolicy()
    {
        return view('onboarding.privacy-policy');
    }

    public function store(StoreOnboardingRequest $request)
    {
        $resumePath = null;

        // Prepare Skill Data
        $techSkills = $this->formatSkills($request->tech_skills ?? [], $request->tech_scores ?? []);
        $softSkills = $this->formatSkills($request->soft_skills ?? [], $request->soft_scores ?? []);

        // Logic check: Ensure at least ONE skill is provided (Tech OR Soft)
        if (empty($techSkills) && empty($softSkills)) {
            return back()->withErrors(['tech_skills' => 'Please provide at least one technical or soft skill.'])->withInput();
        }

        // Handle resume PDF
        if ($request->hasFile('resume_pdf')) {
            $resumePath = $request->file('resume_pdf')->store('resumes', 'public');
            \Log::info('Resume stored successfully at: '.$resumePath);
        }

        $picturePath = null;
        if ($request->hasFile('profile_picture')) {
            $picturePath = $request->file('profile_picture')->store('profile-pictures', 'public');
        }

        $fullName = trim($request->first_name.' '.($request->middle_name ? $request->middle_name.' ' : '').$request->last_name);
        $applicantId = 'APP-'.rand(1000, 9999);

        // Save to Database
        $applicant = OnboardingApplicant::create([
            'applicant_id' => $applicantId,
            'full_name' => $fullName,
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'university' => $request->school,
            'tech_skills' => $techSkills,
            'soft_skills' => $softSkills,
            'gender' => $request->gender,
            'gender_other_specify' => $request->gender_other_specify,
            'phone_number' => $request->phone_number,
            'profile_picture' => $picturePath,
            'emergency_contact' => $request->emergency_contact,
            'emergency_contact_number' => $request->emergency_contact_number,
            'resume_path' => $resumePath,
            'privacy_acknowledged_at' => now(),
        ]);

        // Send email feedback to the applicant
        try {
            Mail::to($applicant->email)
                ->send(new OnboardingApplicationReceivedMail($applicant));
        } catch (\Exception $e) {
            \Log::error("Failed to send onboarding confirmation email to {$applicant->email}: ".$e->getMessage());
        }

        // Automated Background Matching
        \Log::info('Triggering background matching for applicant: '.$fullName);
        $this->matchingService->runMatchingForApplicant($applicant);

        // Keep Legacy CSV Update for compatibility
        $techSerialized = [];
        foreach ($techSkills as $skill => $score) {
            $techSerialized[] = "$skill:$score";
        }
        $softSerialized = [];
        foreach ($softSkills as $skill => $score) {
            $softSerialized[] = "$skill:$score";
        }

        $csvData = [
            $applicantId,
            $fullName,
            $request->school,
            $request->email,
            implode(';', $techSerialized),
            implode(';', $softSerialized),
            $request->first_name,
            $request->middle_name ?? '',
            $request->last_name,
            $request->gender,
            $request->gender_other_specify ?? '',
            $request->phone_number,
            $picturePath ?? '',
            $request->emergency_contact,
            $request->emergency_contact_number,
        ];
        try {
            $this->appendToCsv($csvData);
        } catch (\Exception $e) {
            \Log::error('Failed to update legacy onboarding CSV: '.$e->getMessage());
        }

        // Notify Head Supervisors
        try {
            $headSupervisorRole = Role::where('name', 'head_supervisor')->first();
            if ($headSupervisorRole) {
                $headSupervisors = User::where('role_id', $headSupervisorRole->id)->get();
                foreach ($headSupervisors as $supervisor) {
                    NotificationController::send(
                        $supervisor->id,
                        'New Applicant',
                        'system',
                        "Intern {$fullName} has submitted a new application and resume for your review.",
                        route('head-supervisor.onboarding.index')
                    );
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send notification to head supervisors: '.$e->getMessage());
        }

        return back()->with('success', 'Application finalized & submitted.');
    }

    public function parseResume(ParseResumeRequest $request)
    {
        $file = $request->file('resume_pdf');
        $path = $file->store('temp_resumes', 'local');
        $fullPath = Storage::disk('local')->path($path);

        $pythonPath = config('services.python.path') ?: ((strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'python' : 'python3');
        $process = new Process([$pythonPath, base_path('scripts/pdf_parser.py'), $fullPath]);
        $process->run();

        if (! $process->isSuccessful()) {
            \Log::error('PDF Parser Process failed. Exit code: ' . $process->getExitCode() . '. Error output: ' . $process->getErrorOutput() . '. Command: ' . $process->getCommandLine());
            return response()->json(['error' => 'Failed to parse PDF.'], 500);
        }

        $output = json_decode($process->getOutput(), true);
        
        if (isset($output['error'])) {
            \Log::error('PDF Parser Script failed: ' . $output['error']);
            return response()->json(['error' => 'Failed to parse PDF: ' . $output['error']], 500);
        }

        $text = $output['text'] ?? '';
        $possibleName = $output['name'] ?? '';
        $email = $output['email'] ?? '';
        $phone = $output['phone'] ?? '';

        // Strict Heuristic Validation: Is this actually a resume?
        $resumeMarkers = [
            'experience', 'work history', 'employment', 'education', 'academic background',
            'skills', 'proficiencies', 'technical tools', 'objective', 'summary', 'certifications',
            'professional profile', 'key achievements', 'career objective',
        ];

        // Anti-Markers: Common in research papers, books, or generic docs
        $antiMarkers = [
            'abstract', 'methodology', 'introduction', 'results', 'discussion', 'conclusion',
            'references', 'doi:', 'keywords', 'appendix', 'acknowledgments', 'table of contents',
            'figure', 'table 1', 'table 2',
        ];

        $foundResumeMarkers = 0;
        $foundAntiMarkers = 0;
        $lowerText = strtolower($text);

        foreach ($resumeMarkers as $marker) {
            if (str_contains($lowerText, $marker)) {
                $foundResumeMarkers++;
            }
        }

        foreach ($antiMarkers as $marker) {
            if (str_contains($lowerText, $marker)) {
                $foundAntiMarkers++;
            }
        }

        // Logic:
        // 1. Must have at least 3 distinct resume sections
        // 2. Resume markers must outnumber academic anti-markers
        // 3. Must have a detectable identity (Name AND (Email OR Phone))
        $hasIdentity = ! empty($possibleName) && (! empty($email) || ! empty($phone));
        $isLikelyResume = ($foundResumeMarkers >= 3) && ($foundResumeMarkers > $foundAntiMarkers) && $hasIdentity;

        if (empty(trim($text))) {
            return response()->json(['error' => 'The PDF appears to be empty or an image. Please upload a text-based PDF resume.'], 422);
        }

        if (! $isLikelyResume) {
            $reason = ($foundAntiMarkers > 0 && $foundAntiMarkers >= $foundResumeMarkers)
                ? 'This document appears to be an academic paper or journal.'
                : 'The document does not contain enough professional sections (Experience, Skills, Education).';

            return response()->json(['error' => "Validation failed: {$reason} Please upload a standard professional resume."], 422);
        }

        // Sanity check on extracted data formats
        if ($phone && ! preg_match('/^(09|\+639)\d{9}$/', str_replace([' ', '-', '+63'], ['', '', '09'], $phone))) {
            $phone = ''; // Clear invalid phone noise (like academic identifiers)
        }

        if ($email && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email = '';
        }

        $firstName = '';
        $lastName = '';
        if ($possibleName) {
            $parts = explode(' ', $possibleName);
            if (count($parts) >= 3) {
                // If 3+ words, assume first two are first name (e.g. "John Paul Doe")
                $firstName = $parts[0].' '.$parts[1];
                $lastName = end($parts);
            } elseif (count($parts) == 2) {
                $firstName = $parts[0];
                $lastName = $parts[1];
            } else {
                $firstName = $possibleName;
            }
        }

        // Cleanup
        Storage::disk('local')->delete($path);

        return response()->json([
            'first_name' => mb_convert_case($firstName, MB_CASE_TITLE, 'UTF-8'),
            'last_name' => mb_convert_case($lastName, MB_CASE_TITLE, 'UTF-8'),
            'university' => mb_convert_case($output['university'] ?? '', MB_CASE_TITLE, 'UTF-8'),
            'email' => strtolower($output['email'] ?? ''),
            'phone_number' => $output['phone'] ?? '',
        ]);

    }

    public function getUniversities()
    {
        try {
            $response = Http::get('http://universities.hipolabs.com/search', [
                'country' => 'philippines',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $universities = collect($data)->pluck('name')->unique()->sort()->values();

                return response()->json($universities);
            }

            throw new \Exception('API Error');
        } catch (\Exception $e) {
            \Log::error('University Proxy Error: '.$e->getMessage());

            // Fallback list
            return response()->json([
                'University of the Philippines', 'Ateneo de Manila University',
                'De La Salle University', 'University of Santo Tomas',
                'Far Eastern University', 'Mapua University',
                'Polytechnic University of the Philippines', 'Adamson University',
            ]);
        }
    }

    private function appendToCsv(array $data)
    {
        $path = Storage::path($this->csvPath);
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $exists = Storage::exists($this->csvPath);

        $handle = fopen($path, 'a');

        if (! $exists) {
            fputcsv($handle, [
                'student_id',
                'full_name',
                'university',
                'email',
                'tech_skills',
                'soft_skills',
                'first_name',
                'middle_name',
                'last_name',
                'gender',
                'gender_other_specify',
                'phone_number',
                'profile_picture',
                'emergency_contact',
                'emergency_contact_number',
            ]);
        }

        fputcsv($handle, $data);
        fclose($handle);
    }

    /**
     * Process skills from the request into a structured array.
     */
    private function formatSkills(array $skills, array $scores): array
    {
        $formatted = [];
        foreach ($skills as $index => $skill) {
            if (! empty(trim($skill))) {
                $formatted[trim($skill)] = (int) ($scores[$index] ?? 3);
            }
        }

        return $formatted;
    }
}
