<?php

namespace App\Http\Controllers\HeadSupervisor;

use App\Http\Controllers\Controller;
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

        $callback = function () use ($applicants) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'student_id', 'full_name', 'university', 'email', 'tech_skills', 'soft_skills',
                'first_name', 'middle_name', 'last_name', 'gender', 'gender_other_specify',
                'phone_number', 'profile_picture', 'emergency_contact',
            ]);

            foreach ($applicants as $app) {
                $techSerialized = [];
                foreach ($app->tech_skills as $skill => $score) {
                    $techSerialized[] = "$skill:$score";
                }

                $softSerialized = [];
                foreach ($app->soft_skills as $skill => $score) {
                    $softSerialized[] = "$skill:$score";
                }

                fputcsv($file, [
                    $app->applicant_id,
                    $app->full_name,
                    $app->university,
                    $app->email,
                    implode(';', $techSerialized),
                    implode(';', $softSerialized),
                    $app->first_name,
                    $app->middle_name,
                    $app->last_name,
                    $app->gender,
                    $app->gender_other_specify,
                    $app->phone_number,
                    $app->profile_picture,
                    $app->emergency_contact,
                ]);
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
