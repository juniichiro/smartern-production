<?php

namespace App\Services;

use App\Models\AbsenceRequest;
use App\Models\Attendance;
use App\Models\Journal;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AttendanceService
{
    /**
     * Fetch Philippine holidays for a specific year, cached for seven days to preserve performance
     */
    public function getPhilippineHolidays($year)
    {
        return Cache::remember("ph_holidays_{$year}", now()->addDays(7), function () use ($year) {
            try {
                // Query Nager.Date public API (free, open, no token required)
                $response = Http::timeout(5)->get("https://date.nager.at/api/v3/PublicHolidays/{$year}/PH");
                if ($response->successful()) {
                    return collect($response->json())->mapWithKeys(function ($holiday) {
                        return [$holiday['date'] => [
                            'id' => null, // Null indicates system-generated holiday
                            'type' => 'holiday',
                            'description' => $holiday['name'],
                        ]];
                    })->toArray();
                }
            } catch (\Exception $e) {
                // Fail-safe: log warning and continue without breaking the app
                logger()->warning('Failed to fetch Philippine holidays from Nager.Date API: '.$e->getMessage());
            }

            return [];
        });
    }

    /**
     * Check if a specific date is a holiday, has a work suspension, or is a weekend without a work_day override
     * Returns an array with ['blocked' => true, 'type' => '...', 'description' => '...'] or ['blocked' => false]
     */
    public function checkDateStatus(Carbon $date)
    {
        $dateStr = $date->toDateString();

        // Check database overrides first (they take priority)
        // We only care about date-specific overrides here (where school_name is null or applies to everyone)
        $schedule = Schedule::whereDate('date', $dateStr)
            ->whereNull('school_name')
            ->first();

        if ($schedule) {
            if ($schedule->type === 'holiday') {
                return [
                    'blocked' => true,
                    'type' => 'holiday',
                    'description' => $schedule->description ?? 'Official Holiday',
                ];
            }
            if ($schedule->type === 'suspension') {
                return [
                    'blocked' => true,
                    'type' => 'suspension',
                    'description' => $schedule->description ?? 'Work Suspended',
                ];
            }
            if ($schedule->type === 'work_day') {
                // Explicitly allowed workday override
                return [
                    'blocked' => false,
                ];
            }
        }

        // Check API holidays
        $apiHolidays = $this->getPhilippineHolidays($date->year);
        if (isset($apiHolidays[$dateStr])) {
            $holidayDetails = $apiHolidays[$dateStr];

            return [
                'blocked' => true,
                'type' => 'holiday',
                'description' => $holidayDetails['description'] ?? 'Official Holiday',
            ];
        }

        // Check weekend rule
        if ($date->isWeekend()) {
            return [
                'blocked' => true,
                'type' => 'weekend',
                'description' => 'Weekends are non-working days by default',
            ];
        }

        return [
            'blocked' => false,
        ];
    }

    /**
     * Get the effective schedule for a specific intern on a specific date
     */
    public function getEffectiveSchedule(User $user, Carbon $date)
    {
        $intern = $user->internDetails;
        $school = $intern->school ?? null;

        // Check for date-specific global overrides (holiday, suspension)
        $dateStatus = $this->checkDateStatus($date);
        if ($dateStatus['blocked']) {
            return $dateStatus;
        }

        // Check for school-specific date overrides (holiday, suspension)
        if ($school) {
            $schoolOverride = Schedule::whereDate('date', $date->toDateString())
                ->where('school_name', $school)
                ->first();

            if ($schoolOverride && in_array($schoolOverride->type, ['holiday', 'suspension'])) {
                return [
                    'blocked' => true,
                    'type' => $schoolOverride->type,
                    'description' => $schoolOverride->description ?? ($schoolOverride->type === 'holiday' ? 'Official Holiday' : 'Work Suspended'),
                ];
            }
        }

        // Check if there is a work_day override (global or school-specific) to bypass weekend block
        $hasWorkDayOverride = Schedule::whereDate('date', $date->toDateString())
            ->where('type', 'work_day')
            ->where(function ($query) use ($school) {
                $query->whereNull('school_name');
                if ($school) {
                    $query->orWhere('school_name', $school);
                }
            })
            ->exists();

        // Check if scheduled to work today
        if (! $hasWorkDayOverride && ! $this->isInternScheduled($user, $date)) {
            return [
                'blocked' => true,
                'type' => 'weekend',
                'description' => 'Today is not in your active reporting schedule.',
            ];
        }

        // Check for approved FULL-DAY absence for this user
        $absence = AbsenceRequest::where('intern_id', $user->id)
            ->whereDate('date', $date->toDateString())
            ->where('status', 'approved')
            ->where('type', 'absence')
            ->first();

        if ($absence) {
            return [
                'blocked' => true,
                'type' => 'absence',
                'description' => 'Approved Absence: '.$absence->reason,
            ];
        }

        // Check for school-specific default schedule (recurring)
        if ($school) {
            $schoolSchedule = Schedule::where('type', 'school_default')
                ->where('school_name', $school)
                ->first();

            if ($schoolSchedule) {
                // Block if current date is before official start
                if ($schoolSchedule->start_date && $date->copy()->startOfDay()->lt($schoolSchedule->start_date->copy()->startOfDay())) {
                    return [
                        'blocked' => true,
                        'type' => 'pre_internship',
                        'description' => 'The internship program has not officially started yet.',
                    ];
                }

                return [
                    'blocked' => false,
                    'type' => 'school_default',
                    'start_time' => $schoolSchedule->start_time,
                    'end_time' => $schoolSchedule->end_time,
                    'daily_hours' => (int) $schoolSchedule->daily_hours,
                    'rounding_mode' => $schoolSchedule->rounding_mode ?? 'none',
                    'late_policy' => $schoolSchedule->late_policy ?? 'actual',
                    'description' => "Schedule for {$school}",
                ];
            }
        }

        // System Default
        return [
            'blocked' => false,
            'type' => 'system_default',
            'start_time' => '08:00:00',
            'end_time' => '17:00:00',
            'daily_hours' => 8,
            'rounding_mode' => 'none',
            'late_policy' => 'actual',
            'description' => 'Standard Office Hours',
        ];
    }

    /**
     * Get a merged, sorted list of check-ins, holidays, suspensions, and absences for an intern
     */
    public function getMergedLogs(User $user, $limit = null)
    {
        // Fetch actual attendance logs
        $actualAttendances = Attendance::where('intern_id', $user->id)
            ->with(['internDetails.user', 'office'])
            ->get()
            ->keyBy(function ($item) {
                return $item->clock_in->toDateString();
            });

        // Fetch approved absence requests
        $absenceRequests = AbsenceRequest::where('intern_id', $user->id)
            ->where('status', 'approved')
            ->get()
            ->keyBy(function ($item) {
                return $item->date->toDateString();
            });

        $intern = $user->internDetails;
        $school = $intern ? ($intern->school ?? null) : null;

        // Fetch all schedule overrides (holidays, suspensions, extra workdays)
        $schedules = Schedule::whereNotNull('date')
            ->where(function ($query) use ($school) {
                $query->whereNull('school_name');
                if ($school) {
                    $query->orWhere('school_name', $school);
                }
            })
            ->get()
            ->sortBy(function ($item) {
                return $item->school_name ? 1 : 0;
            })
            ->keyBy(function ($item) {
                return $item->date->toDateString();
            });

        // Define the active internship period
        // Priority: one. Custom start_date/end_date on internDetails, two. Official start_date in school_default schedule, three. Account creation date, four. thirty-day fallback
        $officialStart = null;
        $customEndDate = null;

        if ($user->internDetails && $user->internDetails->start_date) {
            $officialStart = \Carbon\Carbon::parse($user->internDetails->start_date)->startOfDay();
            if ($user->internDetails->end_date) {
                $customEndDate = \Carbon\Carbon::parse($user->internDetails->end_date)->startOfDay();
            }
        } elseif ($school) {
            $schoolSchedule = Schedule::where('type', 'school_default')
                ->where('school_name', $school)
                ->first();

            if ($schoolSchedule && $schoolSchedule->start_date) {
                $officialStart = $schoolSchedule->start_date->copy()->startOfDay();
            }
        }

        $startDate = $officialStart;
        if (! $startDate) {
            $dates = collect();
            if ($user->created_at) {
                $dates->push($user->created_at->copy()->startOfDay());
            }
            $validAttendances = $actualAttendances->filter(fn ($log) => $log->clock_in !== null);
            if ($validAttendances->isNotEmpty()) {
                $earliestActual = $validAttendances->min(fn ($log) => $log->clock_in)->copy()->startOfDay();
                $dates->push($earliestActual);
            }
            $startDate = $dates->isNotEmpty() ? $dates->min() : now()->subDays(30)->startOfDay();
        }
        $endDate = $customEndDate ?? now()->startOfDay();

        // If official start is in the future, we return an empty collection to signify work hasn't started
        if ($officialStart && $officialStart->greaterThan($endDate)) {
            return collect();
        }

        // Simulation/Testing Fix: If calculated start is in the "future" relative to now (and not using official start),
        // we fallback to showing the last thirty days so logs are visible
        if (! $officialStart && $startDate->greaterThan($endDate)) {
            $startDate = $endDate->copy()->subDays(30);
        }

        // Fetch Philippine holidays for the years in range
        $startYear = $startDate->year;
        $endYear = $endDate->year;

        $apiHolidays = collect();
        for ($y = $startYear; $y <= $endYear; $y++) {
            $apiHolidays = $apiHolidays->merge($this->getPhilippineHolidays($y));
        }

        // Generate the date calendar period
        $period = CarbonPeriod::create($startDate, $endDate);
        $mergedLogs = collect();

        foreach ($period as $date) {
            $dateStr = $date->toDateString();

            // Case A: Actual check-in exists in database
            if (isset($actualAttendances[$dateStr])) {
                $log = $actualAttendances[$dateStr];

                // Guard: status=absent with clock_in/clock_out is invalid data — skip to fall through to absent logic
                if ($log->status === 'absent') {
                    continue;
                }

                // (rest of actual attendance logic)

                // Fetch effective schedule to get rounding mode
                $effectiveSchedule = $this->getEffectiveSchedule($user, $date);
                $roundingMode = $effectiveSchedule['rounding_mode'] ?? 'none';

                $duration = null;
                // If the status is excused, duration is ALWAYS zero.zero
                if ($log->status === 'excused') {
                    $duration = 0.00;
                } elseif ($log->clock_out) {
                    $effectiveClockIn = $this->getEffectiveClockIn($log->clock_in, $effectiveSchedule);
                    if ($log->clock_out->lt($effectiveClockIn)) {
                        $minutes = 0;
                    } else {
                        $minutes = $effectiveClockIn->diffInMinutes($log->clock_out);
                    }
                    $hours = $minutes / 60;

                    // Deduct lunch break if > five hours (standard practice in SmarTern)
                    if ($hours > 5.0) {
                        $hours = max(0.0, $hours - 1.0);
                    }

                    if ($roundingMode === 'floor') {
                        $duration = floor($hours);
                    } elseif ($roundingMode === 'ceil') {
                        $duration = ceil($hours);
                    } elseif ($roundingMode === 'none') {
                        $duration = round($hours, 2);
                    } else {
                        $duration = round($hours);
                    }

                    // Cap at daily hours if it's a school default or system default
                    if (isset($effectiveSchedule['daily_hours'])) {
                        $duration = min((float) $effectiveSchedule['daily_hours'], (float) $duration);
                    }
                }

                $status = $log->status;
                $remarks = $log->remarks;

                // Check for approved early leave to update status/remarks in view
                // We use a manual check here since we haven't updated the fetch logic for earlyLeaves yet in this turn
                $hasEarlyLeave = AbsenceRequest::where('intern_id', $user->id)
                    ->whereDate('date', $dateStr)
                    ->where('status', 'approved')
                    ->where('type', 'early_leave')
                    ->exists();

                if ($hasEarlyLeave && $log->clock_out) {
                    $status = 'excused'; // Visually mark as excused once they have actually checked out early
                    $remarks .= ' (Excused Early Leave)';
                }

                $mergedLogs->push([
                    'type' => 'actual',
                    'date' => $date->copy(),
                    'clock_in' => $log->clock_in,
                    'clock_out' => $log->clock_out,
                    'status' => $status,
                    'remarks' => $remarks,
                    'duration' => $duration,
                    'record' => $log,
                ]);

                continue;
            }

            // If official start is not set, we don't generate absences/holidays for the past
            if (! $officialStart) {
                continue;
            }

            // Case B: Approved Absence Request (Full Day Only)

            if (isset($absenceRequests[$dateStr]) && $absenceRequests[$dateStr]->type === 'absence') {
                $req = $absenceRequests[$dateStr];
                $mergedLogs->push([
                    'type' => 'excused',
                    'date' => $date->copy(),
                    'clock_in' => null,
                    'clock_out' => null,
                    'status' => 'excused',
                    'remarks' => $req->reason,
                    'duration' => 0.00,
                    'record' => null,
                ]);

                continue;
            }

            // Case C: No check-in. Check override schedule rules
            // Database overrides take priority over API holidays
            $schedule = $schedules->get($dateStr);
            if (! $schedule && $apiHolidays->has($dateStr)) {
                $holidayDetails = $apiHolidays->get($dateStr);
                $schedule = (object) [
                    'type' => 'holiday',
                    'description' => $holidayDetails['description'],
                ];
            }

            $isWeekend = $date->isWeekend();

            if ($schedule) {
                if ($schedule->type === 'holiday') {
                    $mergedLogs->push([
                        'type' => 'holiday',
                        'date' => $date->copy(),
                        'clock_in' => null,
                        'clock_out' => null,
                        'status' => 'holiday',
                        'remarks' => $schedule->description ?? 'Official Holiday',
                        'duration' => 0.00,
                        'record' => null,
                    ]);
                } elseif ($schedule->type === 'suspension') {
                    $mergedLogs->push([
                        'type' => 'suspension',
                        'date' => $date->copy(),
                        'clock_in' => null,
                        'clock_out' => null,
                        'status' => 'suspension',
                        'remarks' => $schedule->description ?? 'Work Suspended',
                        'duration' => 0.00,
                        'record' => null,
                    ]);
                } elseif ($schedule->type === 'work_day') {
                    // Extra workday on weekend but missed
                    if (! $date->isToday()) {
                        $mergedLogs->push([
                            'type' => 'absent',
                            'date' => $date->copy(),
                            'clock_in' => null,
                            'clock_out' => null,
                            'status' => 'absent',
                            'remarks' => 'Absent for the day',
                            'duration' => 0.00,
                            'record' => null,
                        ]);
                    }
                }
            } else {
                // Case C: Standard weekday check
                if ($this->isInternScheduled($user, $date)) {
                    $isPastShift = false;
                    if ($date->isToday()) {
                        $effectiveSchedule = $this->getEffectiveSchedule($user, $date);
                        $endTimeStr = $effectiveSchedule['end_time'] ?? '17:00:00';
                        // Shift end time plus a fifteen-minute grace period for scanning issues
                        $endTime = Carbon::parse($date->toDateString() . ' ' . $endTimeStr)->addMinutes(15);
                        if (now()->greaterThan($endTime)) {
                            $isPastShift = true;
                        }
                    }

                    if (! $date->isToday() || $isPastShift) {
                        $mergedLogs->push([
                            'type' => 'absent',
                            'date' => $date->copy(),
                            'clock_in' => null,
                            'clock_out' => null,
                            'status' => 'absent',
                            'remarks' => 'Absent for the day',
                            'duration' => 0.00,
                            'record' => null,
                        ]);
                    }
                }
            }
        }

        // Sort by date descending
        $sorted = $mergedLogs->sortByDesc(function ($item) {
            return $item['date']->timestamp;
        });

        if ($limit) {
            return $sorted->take($limit);
        }

        return $sorted;
    }

    /**
     * Get the consolidated attendance status for an intern today
     */
    public function getInternStatus($userId)
    {
        $user = User::with('internDetails.office')->findOrFail($userId);
        $effectiveSchedule = $this->getEffectiveSchedule($user, now());

        $todayRecord = Attendance::where('intern_id', $userId)
            ->whereDate('clock_in', now()->toDateString())
            ->first();

        $completedToday = $todayRecord && $todayRecord->clock_out !== null;

        $statusMessage = 'Ready to clock in.';
        $nextAvailableScan = null;

        if ($completedToday) {
            $statusMessage = 'You have already completed your shift today.';
        } elseif ($todayRecord) {
            $statusMessage = 'You have already clocked in today at '.$todayRecord->clock_in->format('h:i A').'.';
            // Throttling: Must wait five minutes before clocking out
            $nextAvailableScan = $todayRecord->clock_in->addMinutes(5);
        }

        $canScan = ! $completedToday;
        if (isset($effectiveSchedule['blocked']) && $effectiveSchedule['blocked']) {
            if (! $todayRecord) {
                $canScan = false;
                $statusMessage = 'Attendance is blocked: ' . $effectiveSchedule['description'];
            }
        }
        if ($canScan && ! $todayRecord && isset($effectiveSchedule['end_time'])) {
            $endTime = Carbon::parse(now()->toDateString() . ' ' . $effectiveSchedule['end_time']);
            if (now()->greaterThan($endTime)) {
                $canScan = false;
                $statusMessage = 'The scheduled shift for today has already ended at ' . $endTime->format('h:i A') . '.';
            }
        }
        if ($canScan && !$todayRecord && $intern && $intern->end_date) {
            $endDate = Carbon::parse($intern->end_date)->startOfDay();
            if (now()->startOfDay()->greaterThan($endDate)) {
                $canScan = false;
                $statusMessage = 'Your expected internship completion date has passed (' . Carbon::parse($intern->end_date)->format('M d, Y') . '). Please request a date extension from your dashboard to continue logging attendance.';
            }
        }

        return [
            'todayRecord' => $todayRecord,
            'completedToday' => $completedToday,
            'statusMessage' => $statusMessage,
            'nextAvailableScan' => $nextAvailableScan ? $nextAvailableScan->toIso8601String() : null,
            'effectiveSchedule' => $effectiveSchedule,
            'canScan' => $canScan,
        ];
    }

    /**
     * Record a clock-in or clock-out event
     */
    public function recordAttendance($userId, $latitude = null, $longitude = null, $earlyCheckoutReason = null)
    {
        $user = User::with('internDetails.office')->findOrFail($userId);
        $office = $user->internDetails->office;

        $todayRecord = Attendance::where('intern_id', $userId)
            ->whereDate('clock_in', now()->toDateString())
            ->first();

        // Clock Out Logic
        if ($todayRecord) {
            if ($todayRecord->clock_out) {
                return ['success' => false, 'message' => 'Attendance already completed for today.'];
            }

            // Throttling Check
            $diffMinutes = $todayRecord->clock_in->diffInMinutes(now(), false);
            if (abs($diffMinutes) < 5) {
                $minutesLeft = (int) ceil(5 - abs($diffMinutes));
                return [
                    'success' => false,
                    'message' => "Security cooldown: Please wait {$minutesLeft} more minutes before checking out.",
                ];
            }

            // Rounding mode based on effective schedule
            $effectiveSchedule = $this->getEffectiveSchedule($user, now());
            $roundingMode = $effectiveSchedule['rounding_mode'] ?? 'none';

            // Calculate hours rendered
            $effectiveClockIn = $this->getEffectiveClockIn($todayRecord->clock_in, $effectiveSchedule);
            if (now()->lt($effectiveClockIn)) {
                $minutes = 0;
            } else {
                $minutes = $effectiveClockIn->diffInMinutes(now());
            }
            $hours = $minutes / 60;

            if ($hours > 5.0) {
                $hours = max(0.0, $hours - 1.0); // deduct one hour lunch break
            }

            if ($roundingMode === 'floor') {
                $hoursRendered = floor($hours);
            } elseif ($roundingMode === 'ceil') {
                $hoursRendered = ceil($hours);
            } elseif ($roundingMode === 'none') {
                $hoursRendered = round($hours, 2);
            } else {
                $hoursRendered = round($hours);
            }

            if (isset($effectiveSchedule['daily_hours'])) {
                $hoursRendered = min((float) $effectiveSchedule['daily_hours'], (float) $hoursRendered);
            }

            // Check for approved Early Leave
            $earlyLeave = AbsenceRequest::where('intern_id', $userId)
                ->whereDate('date', now()->toDateString())
                ->where('status', 'approved')
                ->where('type', 'early_leave')
                ->first();

            // Check if early checkout
            $endTime = $effectiveSchedule['end_time'] ?? '17:00:00';
            $isEarly = now()->format('H:i:s') < $endTime;
            $reason = $earlyCheckoutReason ? trim($earlyCheckoutReason) : null;

            // Calculate checkout remarks
            if ($isEarly) {
                if ($earlyLeave) {
                    $checkoutRemarks = "Excused Early Leave: Rendered {$hoursRendered} hour(s) ({$earlyLeave->reason})";
                } else {
                    $checkoutRemarks = 'Left early ('.($reason ?? 'No reason provided')."): Rendered {$hoursRendered} hour(s)";
                }
            } else {
                $checkoutRemarks = "Completed full shift ({$hoursRendered} hrs)";
            }

            $finalRemarks = $todayRecord->remarks
                ? $todayRecord->remarks.' | '.$checkoutRemarks
                : $checkoutRemarks;

            $todayRecord->update([
                'clock_out' => now(),
                'latitude' => $latitude ?? $todayRecord->latitude,
                'longitude' => $longitude ?? $todayRecord->longitude,
                'remarks' => $finalRemarks,
            ]);

            if ($user->internDetails) {
                $user->internDetails->increment('hours_rendered', $hoursRendered);
            }

            // Auto-create Daily Journal if not already created
            try {
                $hasDailyJournal = Journal::where('intern_id', $userId)
                    ->where('journal_type', 'daily')
                    ->where('entry_date', now()->format('Y-m-d'))
                    ->exists();

                if (! $hasDailyJournal) {
                    Journal::create([
                        'intern_id' => $userId,
                        'journal_type' => 'daily',
                        'entry_date' => now()->format('Y-m-d'),
                        'title' => 'Daily Journal - '.now()->format('F d, Y'),
                        'notes' => null,
                        'content' => Journal::compileContent((int) $userId, 'daily', now()->format('Y-m-d'), null),
                    ]);
                }
            } catch (\Exception $e) {
                logger()->error('Failed to auto-create daily journal on clock out: '.$e->getMessage());
                if (app()->environment('testing')) {
                    throw $e;
                }
            }

            // Auto-create Weekly Journal on the last scheduled day of the week for the intern
            try {
                $scheduleAssignment = $user->internDetails->schedule_assignment ?? [];
                
                // Fallback to Friday if no schedule is set
                $lastScheduledDay = 'Friday';
                if (!empty($scheduleAssignment) && is_array($scheduleAssignment)) {
                    $dayOrder = ['Sunday' => 0, 'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6];
                    $maxOrder = -1;
                    foreach ($scheduleAssignment as $day) {
                        if (isset($dayOrder[$day]) && $dayOrder[$day] > $maxOrder) {
                            $maxOrder = $dayOrder[$day];
                            $lastScheduledDay = $day;
                        }
                    }
                }

                if (now()->format('l') === $lastScheduledDay) {
                    $startOfWeek = now()->startOfWeek()->format('Y-m-d');
                    $hasWeeklyJournal = Journal::where('intern_id', $userId)
                        ->where('journal_type', 'weekly')
                        ->where('entry_date', $startOfWeek)
                        ->exists();

                    if (! $hasWeeklyJournal) {
                        Journal::create([
                            'intern_id' => $userId,
                            'journal_type' => 'weekly',
                            'entry_date' => $startOfWeek,
                            'title' => 'Weekly Journal - Week of '.now()->startOfWeek()->format('F d, Y'),
                            'notes' => null,
                            'content' => Journal::compileContent((int) $userId, 'weekly', $startOfWeek, null),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                logger()->error('Failed to auto-create weekly journal on clock out: '.$e->getMessage());
                if (app()->environment('testing')) {
                    throw $e;
                }
            }

            return [
                'success' => true,
                'type' => 'out',
                'message' => 'Clock-out successful. Session finalized.',
            ];
        }

        // Clock In Logic
        $intern = $user->internDetails;
        if ($intern && $intern->end_date) {
            $endDate = Carbon::parse($intern->end_date)->startOfDay();
            if (now()->startOfDay()->greaterThan($endDate)) {
                return [
                    'success' => false,
                    'message' => 'Your expected internship completion date has passed (' . Carbon::parse($intern->end_date)->format('M d, Y') . '). Please request a date extension from your dashboard to continue logging attendance.'
                ];
            }
        }

        $effectiveSchedule = $this->getEffectiveSchedule($user, now());

        if (isset($effectiveSchedule['blocked']) && $effectiveSchedule['blocked']) {
            return ['success' => false, 'message' => 'Attendance is blocked: '.$effectiveSchedule['description']];
        }

        // Prevent clock-in if the shift has already ended
        if (isset($effectiveSchedule['end_time'])) {
            $endTime = Carbon::parse(now()->toDateString() . ' ' . $effectiveSchedule['end_time']);
            if (now()->greaterThan($endTime)) {
                return [
                    'success' => false,
                    'message' => 'Cannot clock in: The scheduled shift for today has ended at ' . $endTime->format('h:i A') . '.'
                ];
            }
        }

        $status = 'on_time';
        if (isset($effectiveSchedule['start_time'])) {
            $startTime = Carbon::createFromFormat('H:i:s', $effectiveSchedule['start_time']);
            // Late if after fifteen-minute grace period
            if (now()->greaterThan($startTime->addMinutes(15))) {
                $status = 'late';
            }
        }

        Attendance::create([
            'intern_id' => $userId,
            'office_id' => $office->id,
            'clock_in' => now(),
            'latitude' => $latitude,
            'longitude' => $longitude,
            'status' => $status,
            'remarks' => $status === 'late' ? 'Clocked in after grace period.' : 'Standard entry.',
        ]);

        return [
            'success' => true,
            'type' => 'in',
            'message' => 'Clock-in successful. Welcome to the office.',
        ];
    }

    /**
     * Get the effective clock-in time based on the late policy
     */
    private function getEffectiveClockIn(Carbon $clockIn, array $effectiveSchedule)
    {
        $latePolicy = $effectiveSchedule['late_policy'] ?? 'actual';
        $startTimeStr = $effectiveSchedule['start_time'] ?? null;

        if ($latePolicy === 'actual' || !$startTimeStr) {
            return $clockIn;
        }

        $schedStart = Carbon::parse($clockIn->toDateString() . ' ' . $startTimeStr);

        // If they clocked in after the scheduled start time, apply the penalty/adjustment policy
        if ($clockIn->greaterThan($schedStart)) {
            $effectiveClockIn = $clockIn->copy();
            if ($latePolicy === 'next_hour') {
                if ($effectiveClockIn->minute > 0 || $effectiveClockIn->second > 0) {
                    $effectiveClockIn->addHour()->startOfHour();
                }
            } elseif ($latePolicy === 'next_half_hour') {
                $minute = $effectiveClockIn->minute;
                if ($minute > 0 && $minute < 30) {
                    $effectiveClockIn->minute(30)->second(0);
                } elseif ($minute > 30) {
                    $effectiveClockIn->addHour()->minute(0)->second(0);
                }
            }
            return $effectiveClockIn;
        }

        return $clockIn;
    }

    /**
     * Check if the intern is scheduled to work on a specific date
     */
    public function isInternScheduled(User $user, Carbon $date): bool
    {
        $intern = $user->internDetails;
        if (! $intern) {
            return false;
        }

        $scheduleAssignment = $intern->schedule_assignment;

        // If no custom schedule is assigned, default to Monday-Friday (standard weekdays)
        if (empty($scheduleAssignment) || !is_array($scheduleAssignment)) {
            return ! $date->isWeekend();
        }

        $dayName = $date->format('l'); // e.g. "Monday"
        return in_array($dayName, $scheduleAssignment);
    }
}
