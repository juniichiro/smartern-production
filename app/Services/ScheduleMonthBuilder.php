<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Models\AbsenceRequest;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class ScheduleMonthBuilder
{
    public const TIMEZONE = 'Asia/Manila';

    /**
     * Validate month, view, and date query parameters.
     * Guaranteed safe fallback without exceptions.
     *
     * @return array{month: string, view: string, date: string}
     */
    public static function validateParams(?string $month, ?string $view, ?string $date, ?Carbon $now = null): array
    {
        $now = $now ? $now->copy()->timezone(self::TIMEZONE) : Carbon::now(self::TIMEZONE);
        $currentYear = $now->year;
        $fallbackMonth = $now->format('Y-m');

        // 1. Validate Month (format YYYY-MM, within 5 years either side)
        $validMonth = $fallbackMonth;
        if ($month && preg_match('/^([12]\d{3})-(0[1-9]|1[0-2])$/', $month, $matches)) {
            $year = (int) $matches[1];
            if ($year >= ($currentYear - 5) && $year <= ($currentYear + 5)) {
                $validMonth = $month;
            }
        }

        // 2. Validate View ('month' or 'agenda')
        $validView = 'month';
        if ($view && in_array(strtolower($view), ['month', 'agenda'], true)) {
            $validView = strtolower($view);
        }

        // 3. Validate Date (format YYYY-MM-DD, valid calendar day, must match validMonth)
        $validDate = null;
        if ($date && preg_match('/^([12]\d{3})-(0[1-9]|1[0-2])-(0[1-9]|[12]\d|3[01])$/', $date, $dMatches)) {
            $dYear = (int) $dMatches[1];
            $dMonth = (int) $dMatches[2];
            $dDay = (int) $dMatches[3];

            if (checkdate($dMonth, $dDay, $dYear) && substr($date, 0, 7) === $validMonth) {
                $validDate = $date;
            }
        }

        if (! $validDate) {
            // Default to today if today is within validMonth, else the 1st of validMonth
            if ($now->format('Y-m') === $validMonth) {
                $validDate = $now->toDateString();
            } else {
                $validDate = "{$validMonth}-01";
            }
        }

        return [
            'month' => $validMonth,
            'view' => $validView,
            'date' => $validDate,
        ];
    }

    /**
     * Build calendar view data for the requested month, intern, and selected date.
     */
    public function build(
        User $user,
        ?string $monthParam,
        ?string $viewParam,
        ?string $dateParam,
        AttendanceService $attendanceService,
        ?Carbon $customNow = null
    ): array {
        $now = $customNow ? $customNow->copy()->timezone(self::TIMEZONE) : Carbon::now(self::TIMEZONE);
        $todayDateStr = $now->toDateString();

        $params = self::validateParams($monthParam, $viewParam, $dateParam, $now);
        $monthStr = $params['month'];
        $currentView = $params['view'];
        $selectedDateStr = $params['date'];

        // Parse viewed month boundaries
        $viewMonth = Carbon::createFromFormat('Y-m', $monthStr, self::TIMEZONE)->startOfMonth();
        $monthStart = $viewMonth->copy()->startOfMonth();
        $monthEnd = $viewMonth->copy()->endOfMonth();
        $monthTitle = $viewMonth->format('F Y');

        $prevMonth = $viewMonth->copy()->subMonth();
        $nextMonth = $viewMonth->copy()->addMonth();
        $prevMonthStr = $prevMonth->format('Y-m');
        $nextMonthStr = $nextMonth->format('Y-m');

        // Sunday-first grid start and end
        $leadingDays = $monthStart->dayOfWeek; // 0 (Sun) to 6 (Sat)
        $gridStart = $monthStart->copy()->subDays($leadingDays)->startOfDay();

        $trailingDays = 6 - $monthEnd->dayOfWeek; // 0 to 6
        $gridEnd = $monthEnd->copy()->addDays($trailingDays)->endOfDay();

        // Intern details & scope
        $internDetail = $user->internDetails;
        $school = $internDetail?->school;
        $scheduleAssignment = $internDetail?->schedule_assignment ?? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

        // Fetch School Default Schedule if configured
        $schoolDefault = null;
        if ($school) {
            $schoolDefault = Schedule::where('type', 'school_default')
                ->where('school_name', $school)
                ->first();
        }

        // Single query per source across the grid range
        $yearsToQuery = range($gridStart->year, $gridEnd->year);
        $apiHolidays = collect();
        foreach ($yearsToQuery as $y) {
            $apiHolidays = $apiHolidays->merge($attendanceService->getPhilippineHolidays($y));
        }

        $dbOverrides = Schedule::whereNotNull('date')
            ->whereBetween('date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->where(function ($query) use ($school) {
                $query->whereNull('school_name');
                if ($school) {
                    $query->orWhere('school_name', $school);
                }
            })
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy(fn ($item) => $item->date->toDateString());

        $tasks = Task::where('intern_id', $user->id)
            ->whereBetween('due_date', [$gridStart->copy()->startOfDay(), $gridEnd->copy()->endOfDay()])
            ->orderBy('due_date', 'asc')
            ->get()
            ->groupBy(fn ($t) => Carbon::parse($t->due_date)->timezone(self::TIMEZONE)->toDateString());

        $attendances = Attendance::where('intern_id', $user->id)
            ->whereBetween('clock_in', [$gridStart->copy()->startOfDay(), $gridEnd->copy()->endOfDay()])
            ->orderBy('clock_in', 'asc')
            ->get()
            ->groupBy(fn ($a) => Carbon::parse($a->clock_in)->timezone(self::TIMEZONE)->toDateString());

        $absences = AbsenceRequest::where('intern_id', $user->id)
            ->whereBetween('date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(fn ($abs) => $abs->date->toDateString());

        // Build all days in grid
        $days = [];
        $cursor = $gridStart->copy();
        $selectedDayData = null;

        while ($cursor->lte($gridEnd)) {
            $dateStr = $cursor->toDateString();
            $dayNum = $cursor->day;
            $isCurrentMonth = ($cursor->month === $viewMonth->month && $cursor->year === $viewMonth->year);
            $isToday = ($dateStr === $todayDateStr);
            $isPast = ($dateStr < $todayDateStr);
            $isWeekend = $cursor->isWeekend();
            $dayName = $cursor->format('l');
            $isScheduledDay = in_array($dayName, $scheduleAssignment, true);

            $markers = [];

            // 1. Database Overrides (holiday, suspension, work_day)
            $dayOverrides = $dbOverrides->get($dateStr, collect());
            $hasDbHoliday = false;

            foreach ($dayOverrides as $override) {
                if ($override->type === 'holiday') {
                    $hasDbHoliday = true;
                    $markers[] = [
                        'type' => 'holiday',
                        'title' => $override->description ?? 'Official Holiday',
                        'effect' => 'No work scheduled',
                        'is_override' => true,
                        'id' => $override->getRouteKey(),
                    ];
                } elseif ($override->type === 'suspension') {
                    $markers[] = [
                        'type' => 'override',
                        'title' => $override->description ?? 'Work Suspended',
                        'effect' => 'Work suspended',
                        'is_override' => true,
                        'id' => $override->getRouteKey(),
                    ];
                } elseif ($override->type === 'work_day') {
                    $markers[] = [
                        'type' => 'override',
                        'title' => $override->description ?? 'Special Workday',
                        'effect' => 'Weekend shift override',
                        'is_override' => true,
                        'id' => $override->getRouteKey(),
                    ];
                }
            }

            // 2. API Holidays (if not already handled by DB override)
            if (! $hasDbHoliday && isset($apiHolidays[$dateStr])) {
                $markers[] = [
                    'type' => 'holiday',
                    'title' => $apiHolidays[$dateStr]['description'] ?? 'Official Holiday',
                    'effect' => 'No work scheduled',
                    'is_override' => false,
                    'id' => null,
                ];
            }

            // 3. Tasks Due
            $dayTasks = $tasks->get($dateStr, collect());
            foreach ($dayTasks as $task) {
                $dueCarbon = Carbon::parse($task->due_date)->timezone(self::TIMEZONE);
                $isOverdue = $dueCarbon->isPast() &&
                    $task->status !== TaskStatus::COMPLETED &&
                    $task->status !== TaskStatus::SUBMITTED;

                $statusVal = $task->status instanceof TaskStatus ? $task->status->value : (string) $task->status;

                $markers[] = [
                    'type' => 'task',
                    'id' => $task->getRouteKey(),
                    'title' => $task->title,
                    'description' => $task->description,
                    'status' => $statusVal,
                    'is_overdue' => $isOverdue,
                    'due_time' => $dueCarbon->format('g:i A'),
                    'route' => route('intern.tasks.show', $task->getRouteKey()),
                ];
            }

            // 4. Attendance Record
            $dayAttendances = $attendances->get($dateStr, collect());
            foreach ($dayAttendances as $att) {
                $inCarbon = Carbon::parse($att->clock_in)->timezone(self::TIMEZONE);
                $outCarbon = $att->clock_out ? Carbon::parse($att->clock_out)->timezone(self::TIMEZONE) : null;
                $hoursRendered = $outCarbon ? round($outCarbon->diffInMinutes($inCarbon) / 60, 1) : null;

                $markers[] = [
                    'type' => 'attendance',
                    'id' => $att->attendance_id ?? $att->id,
                    'clock_in' => $inCarbon->format('g:i A'),
                    'clock_out' => $outCarbon ? $outCarbon->format('g:i A') : 'In progress',
                    'hours' => $hoursRendered,
                    'status' => $att->status ?? 'present',
                ];
            }

            // 5. Absence Requests
            $dayAbsences = $absences->get($dateStr, collect());
            $hasApprovedAbsence = false;
            foreach ($dayAbsences as $abs) {
                if ($abs->status === 'approved') {
                    $hasApprovedAbsence = true;
                }

                $markers[] = [
                    'type' => 'absence_request',
                    'id' => $abs->getRouteKey(),
                    'request_type' => $abs->type,
                    'status' => $abs->status,
                    'reason' => $abs->reason,
                    'remarks' => $abs->supervisor_remarks,
                    'has_attachment' => (bool) $abs->attachment_path,
                    'attachment_extension' => $abs->attachment_path ? strtolower(pathinfo($abs->attachment_path, PATHINFO_EXTENSION)) : null,
                    'attachment_url' => $abs->attachment_path ? route('intern.schedule.absence.show', $abs->getRouteKey()) : null,
                ];
            }

            // Determine Primary Day Type
            $hasHoliday = collect($markers)->contains('type', 'holiday');
            $hasOverride = collect($markers)->contains('type', 'override');

            if ($hasHoliday) {
                $dayType = 'holiday';
                $dayTypeLabel = 'Holiday';
            } elseif ($hasOverride) {
                $dayType = 'override';
                $dayTypeLabel = 'Schedule change';
            } elseif ($hasApprovedAbsence) {
                $dayType = 'absence';
                $dayTypeLabel = 'Approved absence';
            } elseif ($isWeekend) {
                $dayType = 'weekend';
                $dayTypeLabel = 'Weekend';
            } elseif ($isScheduledDay) {
                $dayType = 'workday';
                $dayTypeLabel = 'Workday';
            } else {
                $dayType = 'weekend';
                $dayTypeLabel = 'Off duty';
            }

            // Determine Scheduled Hours
            $scheduledHours = null;
            if ($hasHoliday) {
                $scheduledHours = 'No work scheduled';
            } elseif ($hasOverride) {
                $overrideMarker = collect($markers)->firstWhere('type', 'override');
                $scheduledHours = $overrideMarker['effect'] ?? 'Schedule change';
            } elseif ($hasApprovedAbsence) {
                $scheduledHours = 'Excused absence';
            } elseif ($isScheduledDay) {
                if ($schoolDefault && $schoolDefault->start_time && $schoolDefault->end_time) {
                    $st = Carbon::parse($schoolDefault->start_time)->format('g:i A');
                    $et = Carbon::parse($schoolDefault->end_time)->format('g:i A');
                    $scheduledHours = "{$st} – {$et} ({$schoolDefault->daily_hours} hrs)";
                } else {
                    $scheduledHours = '8:00 AM – 5:00 PM (8 hrs)';
                }
            } else {
                $scheduledHours = 'Off duty';
            }

            // Accessible screen reader label
            $markerDescriptions = [];
            foreach ($markers as $m) {
                if ($m['type'] === 'holiday') {
                    $markerDescriptions[] = "Holiday: {$m['title']}";
                } elseif ($m['type'] === 'override') {
                    $markerDescriptions[] = "Schedule change: {$m['title']}";
                } elseif ($m['type'] === 'task') {
                    $markerDescriptions[] = "Task due: {$m['title']}";
                } elseif ($m['type'] === 'attendance') {
                    $markerDescriptions[] = "Attendance: In at {$m['clock_in']}, Out at {$m['clock_out']}";
                } elseif ($m['type'] === 'absence_request') {
                    $label = $m['request_type'] === 'early_leave' ? 'Early leave' : 'Absence';
                    $markerDescriptions[] = "{$label} request: {$m['status']}";
                }
            }

            $accessibleName = $cursor->format('l, F j, Y');
            if ($isToday) {
                $accessibleName .= '. Today';
            }
            $accessibleName .= ". {$dayTypeLabel}.";
            if (! empty($markerDescriptions)) {
                $accessibleName .= ' ' . implode('. ', $markerDescriptions) . '.';
            }

            $dayData = [
                'date' => $dateStr,
                'day' => $dayNum,
                'weekday_name' => $cursor->format('D'),
                'weekday_full' => $cursor->format('l'),
                'formatted_title' => $cursor->format('l, F j'),
                'formatted_long' => $cursor->format('l, F j, Y'),
                'is_current_month' => $isCurrentMonth,
                'is_today' => $isToday,
                'is_past' => $isPast,
                'is_weekend' => $isWeekend,
                'is_scheduled_day' => $isScheduledDay,
                'day_type' => $dayType,
                'day_type_label' => $dayTypeLabel,
                'scheduled_hours' => $scheduledHours,
                'accessible_name' => $accessibleName,
                'markers' => $markers,
                'has_data' => ! empty($markers),
                'can_request_absence' => (! $isPast) && (! $isWeekend || $hasOverride) && (! $hasHoliday) && (! collect($markers)->contains('type', 'absence_request')),
            ];

            if ($dateStr === $selectedDateStr) {
                $selectedDayData = $dayData;
            }

            $days[$dateStr] = $dayData;
            $cursor->addDay();
        }

        // If selected date wasn't found in grid, fallback to today or first day
        if (! $selectedDayData) {
            $selectedDayData = $days[$todayDateStr] ?? reset($days);
            $selectedDateStr = $selectedDayData['date'];
        }

        // Split days into weeks of 7
        $weeks = array_chunk(array_values($days), 7);

        // Build Upcoming List (Next 5 items from today onwards)
        $upcomingItems = $this->buildUpcomingList($user, $now, $attendanceService, $school);

        // Build Agenda Items (Only current-month days with items)
        $agendaDays = collect($days)
            ->filter(fn ($d) => $d['is_current_month'] && $d['has_data'])
            ->values()
            ->all();

        // Month Picker range (5 years back, 5 years ahead)
        $pickerYears = range($now->year - 5, $now->year + 5);
        $pickerMonths = [
            ['num' => '01', 'name' => 'January', 'short' => 'Jan'],
            ['num' => '02', 'name' => 'February', 'short' => 'Feb'],
            ['num' => '03', 'name' => 'March', 'short' => 'Mar'],
            ['num' => '04', 'name' => 'April', 'short' => 'Apr'],
            ['num' => '05', 'name' => 'May', 'short' => 'May'],
            ['num' => '06', 'name' => 'June', 'short' => 'Jun'],
            ['num' => '07', 'name' => 'July', 'short' => 'Jul'],
            ['num' => '08', 'name' => 'August', 'short' => 'Aug'],
            ['num' => '09', 'name' => 'September', 'short' => 'Sep'],
            ['num' => '10', 'name' => 'October', 'short' => 'Oct'],
            ['num' => '11', 'name' => 'November', 'short' => 'Nov'],
            ['num' => '12', 'name' => 'December', 'short' => 'Dec'],
        ];

        return [
            'portalType' => 'Intern',
            'userName' => $internDetail?->first_name ?? $user->name,
            'userRole' => $internDetail?->office?->name ?? 'Intern',
            'timezone' => self::TIMEZONE,
            'todayDateStr' => $todayDateStr,
            'selectedDateStr' => $selectedDateStr,
            'currentMonthStr' => $monthStr,
            'currentView' => $currentView,
            'monthTitle' => $monthTitle,
            'prevMonthStr' => $prevMonthStr,
            'nextMonthStr' => $nextMonthStr,
            'weeks' => $weeks,
            'daysByDate' => $days,
            'selectedDay' => $selectedDayData,
            'upcomingItems' => $upcomingItems,
            'agendaDays' => $agendaDays,
            'pickerYears' => $pickerYears,
            'pickerMonths' => $pickerMonths,
            'isCurrentMonthSelected' => ($monthStr === $now->format('Y-m')),
            'isTodaySelected' => ($selectedDateStr === $todayDateStr && $monthStr === $now->format('Y-m')),
            'notifications' => \App\Models\Notification::where('recipient_id', $user->id)->where('is_read', false)->get(),
        ];
    }

    /**
     * Build the Upcoming list: next 5 items from today onwards.
     */
    protected function buildUpcomingList(User $user, Carbon $now, AttendanceService $attendanceService, ?string $school): array
    {
        $today = $now->copy()->startOfDay();
        $todayStr = $today->toDateString();
        $lookaheadEnd = $today->copy()->addMonths(3);

        $items = collect();

        // 1. API Holidays
        $years = array_unique([$today->year, $lookaheadEnd->year]);
        $holidays = collect();
        foreach ($years as $y) {
            $holidays = $holidays->merge($attendanceService->getPhilippineHolidays($y));
        }

        foreach ($holidays as $dateStr => $hol) {
            if ($dateStr >= $todayStr && $dateStr <= $lookaheadEnd->toDateString()) {
                $carbonDate = Carbon::parse($dateStr, self::TIMEZONE);
                $items->push([
                    'date' => $dateStr,
                    'carbon' => $carbonDate,
                    'title' => $hol['description'] ?? 'Official Holiday',
                    'type' => 'holiday',
                    'type_label' => 'Holiday',
                ]);
            }
        }

        // 2. Schedule Overrides
        $overrides = Schedule::whereNotNull('date')
            ->whereBetween('date', [$todayStr, $lookaheadEnd->toDateString()])
            ->where(function ($query) use ($school) {
                $query->whereNull('school_name');
                if ($school) {
                    $query->orWhere('school_name', $school);
                }
            })
            ->orderBy('date', 'asc')
            ->limit(10)
            ->get();

        foreach ($overrides as $ov) {
            $dateStr = $ov->date->toDateString();
            $carbonDate = Carbon::parse($dateStr, self::TIMEZONE);
            $typeLabel = $ov->type === 'holiday' ? 'Holiday' : ($ov->type === 'suspension' ? 'Suspension' : 'Workday override');
            $type = $ov->type === 'holiday' ? 'holiday' : 'override';

            $items->push([
                'date' => $dateStr,
                'carbon' => $carbonDate,
                'title' => $ov->description ?? $typeLabel,
                'type' => $type,
                'type_label' => $typeLabel,
            ]);
        }

        // 3. Pending/In-progress Tasks Due
        $tasks = Task::where('intern_id', $user->id)
            ->where('due_date', '>=', $today)
            ->whereNotIn('status', [TaskStatus::COMPLETED, TaskStatus::SUBMITTED])
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        foreach ($tasks as $task) {
            $carbonDate = Carbon::parse($task->due_date)->timezone(self::TIMEZONE);
            $dateStr = $carbonDate->toDateString();

            $items->push([
                'date' => $dateStr,
                'carbon' => $carbonDate,
                'title' => $task->title,
                'type' => 'task',
                'type_label' => 'Task due',
            ]);
        }

        // 4. Pending/Approved Absence Requests
        $absences = AbsenceRequest::where('intern_id', $user->id)
            ->where('date', '>=', $todayStr)
            ->orderBy('date', 'asc')
            ->limit(5)
            ->get();

        foreach ($absences as $abs) {
            $dateStr = $abs->date->toDateString();
            $carbonDate = Carbon::parse($dateStr, self::TIMEZONE);
            $reqLabel = $abs->type === 'early_leave' ? 'Early leave' : 'Absence';

            $items->push([
                'date' => $dateStr,
                'carbon' => $carbonDate,
                'title' => "{$reqLabel} ({$abs->status}): {$abs->reason}",
                'type' => 'absence',
                'type_label' => ucfirst($abs->status) . ' absence',
            ]);
        }

        // Sort by date ascending, take 5 items
        return $items->sortBy('date')
            ->take(5)
            ->values()
            ->map(function ($item) use ($today) {
                $c = $item['carbon'];
                $diffDays = (int) $today->diffInDays($c->copy()->startOfDay(), false);

                if ($diffDays === 0) {
                    $relative = 'Today';
                } elseif ($diffDays === 1) {
                    $relative = 'Tomorrow';
                } elseif ($diffDays > 1) {
                    $relative = "in {$diffDays} days";
                } else {
                    $relative = $c->format('M j');
                }

                return [
                    'date' => $item['date'],
                    'date_formatted' => $c->format('M j, Y'),
                    'relative_date' => $relative,
                    'title' => $item['title'],
                    'type' => $item['type'],
                    'type_label' => $item['type_label'],
                    'target_month' => $c->format('Y-m'),
                    'target_date' => $item['date'],
                ];
            })
            ->all();
    }
}
