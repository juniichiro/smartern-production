<?php

namespace App\Http\Controllers\HeadSupervisor;

use App\Http\Controllers\Controller;
use App\Models\AssessmentItem;
use App\Models\Office;
use App\Models\OfficeRule;
use App\Models\OnboardingApplicant;
use Illuminate\Support\Facades\Storage;

class OnboardingManagementController extends Controller
{
    private $csvPath = 'onboarding_applicants.csv';

    public function index()
    {
        $applicants = OnboardingApplicant::orderByRaw('(SELECT COUNT(*) FROM match_results WHERE match_results.applicant_name = onboarding_applicants.full_name AND match_results.is_confirmed = 1) ASC')
            ->orderBy('onboarding_applicants.created_at', 'desc')
            ->paginate(10);
 
        // Fetch Confirmed applicant names once to optimize database query
        $confirmedNames = \App\Models\MatchResult::where('is_confirmed', 1)
            ->pluck('applicant_name')
            ->toArray();

        $mappedApplicants = $applicants->getCollection()->map(function ($app) use ($confirmedNames) {
            $techList = [];
            foreach ($app->tech_skills as $skill => $score) {
                $techList[] = "$skill ($score)";
            }
 
            $softList = [];
            foreach ($app->soft_skills as $skill => $score) {
                $softList[] = "$skill ($score)";
            }
 
            return [
                'db_id' => $app->id,
                'route_key' => $app->getRouteKey(),
                'id' => $app->applicant_id,
                'name' => $app->full_name,
                'school' => $app->university,
                'email' => $app->email,
                'phone' => $app->phone_number,
                'emergency' => $app->emergency_contact,
                'tech_list' => $techList,
                'soft_list' => $softList,
                'resume_path' => $app->resume_path,
                'is_pending' => !in_array($app->full_name, $confirmedNames),
            ];
        });

        $applicants->setCollection($mappedApplicants);

        // Fetch Offices
        $offices = Office::withCount(['internDetails', 'supervisorDetails'])
            ->with(['supervisorDetails.user'])
            ->get()
            ->map(function ($office) {
                $office->rules_count = OfficeRule::where('office_id', $office->id)->count();

                return $office;
            });

        // Fetch Documented Rejections
        $rejectedApplicants = \App\Models\RejectedApplicant::latest()->paginate(10, ['*'], 'rejections_page');
 
        return view('head-supervisor.onboarding.index', compact('applicants', 'offices', 'rejectedApplicants'));
    }

    public function exportForMatching()
    {
        // Re-generate CSV from DB to ensure it's up to date
        $applicants = OnboardingApplicant::all();
        $activeAssessments = AssessmentItem::active()->ordered()->get();

        if ($applicants->isEmpty()) {
            return back()->with('error', 'No applicants found to export.');
        }

        $fileName = 'Applicants_For_Matching_'.now()->format('Y-m-d_His').'.csv';
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($applicants, $activeAssessments) {
            $file = fopen('php://output', 'w');

            // Build headers dynamically
            $headerRow = [
                'student_id', 'full_name', 'university', 'email', 'office_id',
                'first_name', 'middle_name', 'last_name', 'gender', 'gender_other_specify',
                'phone_number', 'profile_picture', 'emergency_contact',
            ];
            foreach ($activeAssessments as $item) {
                $headerRow[] = $item->competency_text;
            }
            fputcsv($file, $headerRow);

            foreach ($applicants as $app) {
                $row = [
                    $app->applicant_id,
                    $app->full_name,
                    $app->university,
                    $app->email,
                    '', // office_id placeholder
                    $app->first_name,
                    $app->middle_name,
                    $app->last_name,
                    $app->gender,
                    $app->gender_other_specify,
                    $app->phone_number,
                    $app->profile_picture,
                    $app->emergency_contact,
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

    public function reject(\Illuminate\Http\Request $request, OnboardingApplicant $applicant)
    {
        // 1. Confirm applicant is pending
        $isConfirmed = \App\Models\MatchResult::where('applicant_name', $applicant->full_name)
            ->where('is_confirmed', 1)
            ->exists();
 
        if ($isConfirmed) {
            return back()->with('error', 'Cannot reject an applicant who has already been matched and confirmed.');
        }
 
        // 2. Document the rejection
        \App\Models\RejectedApplicant::create([
            'full_name' => $applicant->full_name,
            'email' => $applicant->email,
            'phone_number' => $applicant->phone_number,
            'rejected_at' => now(),
            'reason' => $request->input('reason', 'Rejected by Head Supervisor'),
        ]);
 
        // 3. Send rejection email notification
        try {
            \Illuminate\Support\Facades\Mail::to($applicant->email)->send(
                new \App\Mail\OnboardingApplicationRejectedMail($applicant->full_name, $request->input('reason', 'Rejected during live onboarding intake review'))
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send onboarding rejection email: '.$e->getMessage());
        }
 
        // 4. Delete from onboarding queue
        $applicant->delete();
 
        // 5. Regenerate legacy CSV for compatibility
        try {
            $this->regenerateCsv();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to update legacy onboarding CSV on rejection: '.$e->getMessage());
        }
 
        return back()->with('success', 'Applicant has been rejected and documented.');
    }

    private function regenerateCsv()
    {
        $path = Storage::path($this->csvPath);
        $dir = dirname($path);

        if (! is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $applicants = OnboardingApplicant::all();

        if ($applicants->isEmpty()) {
            if (Storage::exists($this->csvPath)) {
                Storage::delete($this->csvPath);
            }
            return;
        }

        $handle = fopen($path, 'w');
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

        foreach ($applicants as $app) {
            fputcsv($handle, [
                $app->applicant_id,
                $app->full_name,
                $app->university,
                $app->email,
                is_array($app->tech_skills) ? json_encode($app->tech_skills) : $app->tech_skills,
                is_array($app->soft_skills) ? json_encode($app->soft_skills) : $app->soft_skills,
                $app->first_name,
                $app->middle_name,
                $app->last_name,
                $app->gender,
                $app->gender_other_specify,
                $app->phone_number,
                $app->profile_picture,
                $app->emergency_contact,
                $app->emergency_contact_number,
            ]);
        }
        fclose($handle);
    }
}
