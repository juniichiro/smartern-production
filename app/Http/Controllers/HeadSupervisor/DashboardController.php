<?php

namespace App\Http\Controllers\HeadSupervisor;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\InternDetail;
use App\Models\MatchResult;
use App\Models\Office;
use App\Models\OnboardingApplicant;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Division-wide Stats Calculation
        $totalApplicants = InternDetail::count();

        $matchedCount = MatchResult::distinct('applicant_name')->count();

        /**
         * UPDATED: Active Interns
         * Interns are active if they are assigned to an office AND
         * do not have a PUBLISHED certificate yet
         */
        $activeInterns = InternDetail::whereNotNull('office_id')
            ->whereDoesntHave('certificate', function ($query) {
                $query->where('status', 'published');
            })
            ->count();

        // Pending Certificates
        $pendingCertificates = Certificate::where('status', 'pending')->count();

        // Department-specific Stats & Data (If acting as Supervisor)
        $supervisorDetail = Auth::user()->supervisorDetails;
        $deptStats = null;
        $deptInterns = collect();
        $upcomingDeadlines = collect();
        $activeAttendance = collect();
        $submissionsNeedingReview = collect();

        if ($supervisorDetail && $supervisorDetail->office_id) {
            $officeId = $supervisorDetail->office_id;

            // Fetch active dept interns
            $deptInterns = InternDetail::where('office_id', $officeId)
                ->whereDoesntHave('certificate', function ($query) {
                    $query->where('status', 'published');
                })
                ->with(['user', 'tasks', 'attendances'])
                ->get();

            $deptInternIds = $deptInterns->pluck('user_id');

            // Stats
            $deptStats = [
                'office_name' => $supervisorDetail->office->name ?? 'Assigned Department',
                'active_interns' => $deptInterns->count(),
                'pending_tasks' => Task::whereIn('intern_id', $deptInternIds)->where('status', 'pending')->count(),
                'attendance_issues' => Attendance::whereIn('intern_id', $deptInternIds)
                    ->whereIn('status', ['late', 'absent'])
                    ->whereDate('created_at', now()->today())
                    ->count(),
            ];

            // Upcoming Deadlines
            $upcomingDeadlines = Task::whereIn('intern_id', $deptInternIds)
                ->with('internDetails')
                ->where('status', 'pending')
                ->where('due_date', '>=', now()->startOfDay())
                ->orderBy('due_date', 'asc')
                ->take(3)
                ->get();

            // Active Attendance (Clocked in but not out today)
            $activeAttendance = Attendance::whereIn('intern_id', $deptInternIds)
                ->whereDate('clock_in', now()->today())
                ->whereNull('clock_out')
                ->with('internDetails')
                ->get();

            // Submissions Needing Review
            $submissionsNeedingReview = Task::whereIn('intern_id', $deptInternIds)
                ->where('status', 'pending')
                ->has('submissions')
                ->with(['internDetails', 'submissions' => function ($q) {
                    $q->latest();
                }])
                ->latest('updated_at')
                ->take(5)
                ->get();
        }

        // Recent Applications with Match Scores (Matched but not finalized/assigned yet)
        $recentApplications = OnboardingApplicant::whereIn('full_name', function ($query) {
            $query->select('applicant_name')
                ->from('match_results');
        })
            ->whereNotIn('full_name', function ($query) {
                $query->select('applicant_name')
                    ->from('match_results')
                    ->where('is_confirmed', true);
            })
            ->latest('id')
            ->limit(3)
            ->get()
            ->each(function ($intern) {
                $intern->school = $intern->university;
                $bestMatch = MatchResult::where('applicant_name', $intern->full_name)
                    ->with('office')
                    ->orderBy('score', 'desc')
                    ->first();
                $intern->match_score = $bestMatch ? $bestMatch->score : 0;
                $intern->matched_office = $bestMatch && $bestMatch->office ? $bestMatch->office->name : 'Unassigned';
            });

        // Department Load Overview
        $officeLoad = Office::withCount('internDetails')
            ->orderBy('intern_details_count', 'desc')
            ->get();

        // Pending Certificates Details
        $pendingCertificateDetails = Certificate::where('status', 'pending')
            ->with('intern')
            ->latest()
            ->take(5)
            ->get();

        // Endorsement Queue: Interns endorsed by Supervisor (status 'pending') but not yet finalized
        $endorsementQueue = InternDetail::whereHas('certificate', function ($query) {
            $query->where('status', 'pending');
        })
            ->with(['office', 'certificate'])
            ->take(5)
            ->get();

        return view('head-supervisor.dashboard', compact(
            'totalApplicants',
            'matchedCount',
            'activeInterns',
            'pendingCertificates',
            'recentApplications',
            'officeLoad',
            'pendingCertificateDetails',
            'deptStats',
            'deptInterns',
            'upcomingDeadlines',
            'activeAttendance',
            'submissionsNeedingReview',
            'endorsementQueue'
        ));
    }
}
