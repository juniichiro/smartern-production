<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\NotificationController;
use App\Models\AssessmentItem;
use App\Models\InternDetail;
use App\Models\Role;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficeManagementController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $supervisor = $user->supervisorDetails;

        if (! $supervisor) {
            return view('supervisor.office-management', [
                'interns' => collect([]),
                'totalInterns' => 0,
            ]);
        }

        $query = InternDetail::query();
        $officeId = $supervisor->office_id;
        $query->where('office_id', $officeId);

        // Eager load 'certificate' and filter out those already published
        $query->whereDoesntHave('certificate', function ($query) {
            $query->where('status', 'published');
        })
            ->with(['user', 'certificate']);

        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('first_name', 'like', '%'.$request->search.'%')
                    ->orWhere('last_name', 'like', '%'.$request->search.'%')
                    ->orWhere('user_id', 'like', '%'.$request->search.'%');
            });
        }

        $interns = $query->paginate(10)->withQueryString();

        // Update count to reflect only those currently active in this office
        $totalInterns = InternDetail::where('office_id', $officeId)
            ->whereDoesntHave('certificate', function ($q) {
                $q->where('status', 'published');
            })->count();

        return view('supervisor.office-management', compact('interns', 'totalInterns'));
    }

    public function show(User $user, AttendanceService $attendanceService)
    {
        // Pull the intern with corrected relationship names
        $intern = InternDetail::where('user_id', $user->id)
            ->with(['user', 'tasks', 'attendances', 'office.supervisorDetails', 'certificate.evaluation'])
            ->firstOrFail();

        // Fetch complete merged logs
        $allLogs = $attendanceService->getMergedLogs($intern->user);

        // Metric calculations from Merged Logs (source of truth)
        $tasks = $intern->tasks ?? collect();
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();

        $absents = $allLogs->where('status', 'absent')->count();
        $lates = $allLogs->where('status', 'late')->count();
        $excused = $allLogs->where('status', 'excused')->count();

        // Get latest entries for preview
        $recentTasks = $intern->tasks()->latest()->take(5)->get();
        $recentAttendance = $allLogs->take(5);

        // Fetch school schedule for the timeline
        $schoolSchedule = null;
        if ($intern->school) {
            $schoolSchedule = Schedule::where('type', 'school_default')
                ->where('school_name', $intern->school)
                ->first();
        }

        // Get today's effective schedule
        $effectiveSchedule = $attendanceService->getEffectiveSchedule($intern->user, now());

        // Calculate Projected End Date
        $requiredHours = $intern->required_hours ?? 480;
        $renderedHours = $intern->hours_rendered ?? 0;
        $hoursLeft = max(0, $requiredHours - $renderedHours);
        
        $projectedEndDate = null;
        if ($hoursLeft > 0) {
            $currentDate = now()->startOfDay();
            $scheduleDays = $intern->schedule_assignment ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
            $dailyHours = $schoolSchedule ? $schoolSchedule->daily_hours ?? 8 : 8;
            
            $holidaysAndSuspensions = \App\Models\Schedule::where('school_name', $intern->school)
                ->whereIn('type', ['holiday', 'suspension'])
                ->pluck('date')
                ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
                ->toArray();
                
            $safetyCounter = 0;
            $maxIterations = 365 * 5;
            
            while ($hoursLeft > 0 && $safetyCounter < $maxIterations) {
                $safetyCounter++;
                $currentDayName = $currentDate->format('l');
                $dateStr = $currentDate->format('Y-m-d');
                
                $isReportingDay = in_array($currentDayName, $scheduleDays);
                $isNonWorkingDay = in_array($dateStr, $holidaysAndSuspensions);
                
                if ($isReportingDay && !$isNonWorkingDay) {
                    $hoursLeft -= $dailyHours;
                }
                
                if ($hoursLeft > 0) {
                    $currentDate->addDay();
                }
            }
            $projectedEndDate = $currentDate;
        } else {
            $projectedEndDate = now();
        }

        return view('supervisor.intern-profile', compact(
            'intern',
            'totalTasks',
            'completedTasks',
            'absents',
            'lates',
            'excused',
            'recentTasks',
            'recentAttendance',
            'schoolSchedule',
            'effectiveSchedule',
            'projectedEndDate'
        ));
    }

    /**
     * Update the intern limit/slots for the supervisor's office and notify head supervisors
     */
    public function updateRequirements(Request $request)
    {
        $request->validate([
            'intern_limit' => 'required|integer|min:0',
        ]);

        $user = Auth::user();
        $supervisor = $user->supervisorDetails;

        if (! $supervisor || ! $supervisor->office) {
            return redirect()->back()->with('error', 'Unauthorized or office not associated.');
        }

        $office = $supervisor->office;
        $office->update([
            'intern_limit' => $request->intern_limit,
        ]);

        // Find Head Supervisors (Ms. Mal-in) and notify them
        $headSupervisorRole = Role::where('name', 'head_supervisor')->first();
        if ($headSupervisorRole) {
            $headSupervisors = User::where('role_id', $headSupervisorRole->id)->get();
            foreach ($headSupervisors as $headSupervisor) {
                NotificationController::send(
                    $headSupervisor->id,
                    'Intern Requirements Update',
                    'system',
                    "Office {$office->name} has updated their intern limit to {$request->intern_limit} slots.",
                    route('head-supervisor.offices.index')
                );
            }
        }

        return redirect()->back()->with('success', 'Intern requirements updated successfully.');
    }

    /**
     * Show the matching rules configuration form for the supervisor's office
     */
    public function editRules()
    {
        $user = Auth::user();
        $supervisor = $user->supervisorDetails;

        if (! $supervisor || ! $supervisor->office) {
            return redirect()->back()->with('error', 'Unauthorized or office not associated.');
        }

        $office = $supervisor->office;
        $rules = \App\Models\OfficeRule::where('office_id', $office->id)->get();
        $assessmentItems = AssessmentItem::active()->ordered()->get()->groupBy('category');
        $categories = AssessmentItem::active()->select('category')->distinct()->pluck('category');

        return view('supervisor.edit-rules', compact('office', 'rules', 'assessmentItems', 'categories'));
    }

    /**
     * Update the matching rules & intern limit for the supervisor's office
     */
    public function updateRules(Request $request)
    {
        $request->validate([
            'intern_limit' => 'required|integer|min:0',
        ]);

        $user = Auth::user();
        $supervisor = $user->supervisorDetails;

        if (! $supervisor || ! $supervisor->office) {
            return redirect()->back()->with('error', 'Unauthorized or office not associated.');
        }

        $office = $supervisor->office;

        DB::transaction(function () use ($request, $office) {
            // Update Office Limit
            $office->update(['intern_limit' => $request->intern_limit]);

            // Clear existing rules
            \App\Models\OfficeRule::where('office_id', $office->id)->delete();

            // Insert new rules
            if ($request->has('rules')) {
                foreach ($request->rules as $ruleData) {
                    if (isset($ruleData['priority']) && $ruleData['priority'] > 1) {
                        \App\Models\OfficeRule::create([
                            'office_id' => $office->id,
                            'require_rule' => $ruleData['require_rule'],
                            'rule_value' => $ruleData['rule_value'],
                            'priority' => $ruleData['priority'],
                        ]);
                    }
                }
            }
        });

        // Find Head Supervisors (Ms. Mal-in) and notify them
        $headSupervisorRole = Role::where('name', 'head_supervisor')->first();
        if ($headSupervisorRole) {
            $headSupervisors = User::where('role_id', $headSupervisorRole->id)->get();
            foreach ($headSupervisors as $headSupervisor) {
                NotificationController::send(
                    $headSupervisor->id,
                    'Intern Requirements Update',
                    'system',
                    "Office {$office->name} has updated their intern limit and competency rules.",
                    route('head-supervisor.offices.index')
                );
            }
        }

        return redirect()->route('supervisor.interns')
            ->with('success', "Requirements and rules for {$office->name} updated successfully.");
    }

    /**
     * Add a custom competency item to the assessment form and automatically set its rule priority
     */
    public function storeAssessmentItem(Request $request)
    {
        $request->validate([
            'category' => 'required|string|max:100',
            'competency_text' => 'required|string|max:500',
            'priority' => 'required|integer|min:1|max:5',
        ]);

        $user = Auth::user();
        $supervisor = $user->supervisorDetails;

        if (! $supervisor || ! $supervisor->office) {
            return redirect()->back()->with('error', 'Unauthorized or office not associated.');
        }

        $office = $supervisor->office;

        DB::transaction(function () use ($request, $office) {
            // Create assessment item
            $item = AssessmentItem::create([
                'category' => $request->category,
                'competency_text' => $request->competency_text,
                'order_index' => AssessmentItem::where('category', $request->category)->count(),
                'is_active' => true,
            ]);

            // Add as requirement rule to their office
            if ($request->priority > 1) {
                $categoryName = str_contains($request->category, '.') 
                    ? trim(explode('.', $request->category, 2)[1]) 
                    : $request->category;

                \App\Models\OfficeRule::create([
                    'office_id' => $office->id,
                    'require_rule' => $categoryName,
                    'rule_value' => $request->competency_text,
                    'priority' => $request->priority,
                ]);
            }
        });

        // Find Head Supervisors and notify them
        $headSupervisorRole = Role::where('name', 'head_supervisor')->first();
        if ($headSupervisorRole) {
            $headSupervisors = User::where('role_id', $headSupervisorRole->id)->get();
            foreach ($headSupervisors as $headSupervisor) {
                NotificationController::send(
                    $headSupervisor->id,
                    'New Assessment Competency Added',
                    'system',
                    "Office {$office->name} added a new assessment form competency: '{$request->competency_text}'",
                    route('head-supervisor.offices.index')
                );
            }
        }

        return redirect()->back()->with('success', 'Custom competency added and linked as requirement rule.');
    }

    public function requestExtension(Request $request, User $user)
    {
        $request->validate([
            'new_end_date' => 'required|date|after:today',
            'reason' => 'required|string|max:255',
        ]);

        $intern = InternDetail::where('user_id', $user->id)->firstOrFail();

        $existing = \App\Models\InternExtension::where('intern_detail_id', $intern->id)
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->with('error', 'There is already a pending extension request for this intern.');
        }

        $hasApproved = \App\Models\InternExtension::where('intern_detail_id', $intern->id)
            ->where('status', 'approved')
            ->exists();

        if ($hasApproved) {
            return back()->with('error', 'This intern has already been granted a date extension. Further extensions are not allowed.');
        }

        \App\Models\InternExtension::create([
            'intern_detail_id' => $intern->id,
            'previous_end_date' => $intern->end_date ?? $intern->start_date ?? now(),
            'new_end_date' => $request->new_end_date,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        // Find Head Supervisors and notify them
        $headSupervisorRole = Role::where('name', 'head_supervisor')->first();
        if ($headSupervisorRole) {
            $headSupervisors = User::where('role_id', $headSupervisorRole->id)->get();
            $supervisor = auth()->user()->supervisorDetails;
            foreach ($headSupervisors as $hs) {
                NotificationController::send(
                    $hs->id,
                    'Date Extension Requested',
                    'system',
                    "The {$supervisor->office->name} Supervisor has requested a date extension for {$intern->first_name} {$intern->last_name} to " . \Carbon\Carbon::parse($request->new_end_date)->format('M d, Y') . ".",
                    route('head-supervisor.interns.show', $intern->user->getRouteKey())
                );
            }
        }

        return back()->with('success', 'Date extension request submitted to Head Supervisor.');
    }
}
