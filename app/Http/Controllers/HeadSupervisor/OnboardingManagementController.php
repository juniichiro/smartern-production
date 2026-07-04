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
        // 1. Fetch Applicants
        $dbApplicants = OnboardingApplicant::latest()->get();

        $applicants = $dbApplicants->map(function ($app) {
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
                'emergency' => $app->emergency_contact,
                'tech_list' => $techList,
                'soft_list' => $softList,
                'resume_path' => $app->resume_path,
            ];
        });

        // 2. Fetch Offices
        $offices = Office::withCount(['internDetails', 'supervisorDetails'])
            ->with(['supervisorDetails.user'])
            ->get()
            ->map(function ($office) {
                $office->rules_count = OfficeRule::where('office_id', $office->id)->count();

                return $office;
            });

        return view('head-supervisor.onboarding.index', compact('applicants', 'offices'));
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

    public function clear()
    {
        OnboardingApplicant::query()->delete();

        if (Storage::exists($this->csvPath)) {
            Storage::delete($this->csvPath);
        }

        return back()->with('success', 'Onboarding queue cleared.');
    }
}
