<?php

namespace App\Http\Controllers\Intern;

use App\Http\Controllers\Controller;
use App\Models\AbsenceRequest;
use App\Models\Attendance;
use App\Models\Schedule;
use App\Models\User;
use App\Services\AttendanceService;
use App\Services\SecurityAuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Vinkla\Hashids\Facades\Hashids;

class AttendanceController extends Controller
{
    protected $attendanceService;

    protected $auditService;

    public function __construct(AttendanceService $attendanceService, SecurityAuditService $auditService)
    {
        $this->attendanceService = $attendanceService;
        $this->auditService = $auditService;
    }

    public function index()
    {
        $user = Auth::user();
        $intern = $user->internDetails;

        $token = Cache::get("active_qr_token_{$user->id}");
        if (! $token) {
            $token = Hashids::encode($user->id, now()->timestamp);
            Cache::put("active_qr_token_{$user->id}", $token, now()->addSeconds(20));
        }
        $baseUrl = url('/');
        $qrPayload = "{$baseUrl}/attendance/verify?token={$token}";

        $allAttendanceLogs = $this->attendanceService->getMergedLogs($user);
        $attendanceHistory = $allAttendanceLogs->take(5);

        $statusData = $this->attendanceService->getInternStatus($user->id);
        $todayRecord = $statusData['todayRecord'];
        $completedToday = $statusData['completedToday'];
        $effectiveSchedule = $statusData['effectiveSchedule'];

        $schoolSchedule = null;
        if ($intern->school) {
            $schoolSchedule = Schedule::where('type', 'school_default')
                ->where('school_name', $intern->school)
                ->first();
        }

        $timelineNotSet = !$schoolSchedule || !$schoolSchedule->start_date || !$schoolSchedule->target_end_date;

        $isEnabled = true;
        $nextAvailableScan = null;
        $statusMessage = 'Daily Session Pending';

        if ($timelineNotSet) {
            $isEnabled = false;
            $statusMessage = 'Your internship schedule has not started just yet.';
        } elseif ($effectiveSchedule['blocked'] && ! $todayRecord && ! $completedToday) {
            $isEnabled = false;
            $labelMap = [
                'holiday' => 'Holiday',
                'suspension' => 'Suspended',
                'weekend' => 'Weekend',
                'pre_internship' => 'Program Pending',
            ];
            $label = $labelMap[$effectiveSchedule['type']] ?? 'Disabled';
            $statusMessage = "Attendance Disabled ({$label}: {$effectiveSchedule['description']})";
        }

        if (!$timelineNotSet) {
            if ($todayRecord) {
                $statusMessage = 'Ready for Time-Out';
                if ($statusData['nextAvailableScan']) {
                    $nextAvailableScan = Carbon::parse($statusData['nextAvailableScan']);
                    $statusMessage = 'Security Lock Active';
                }
            } elseif ($completedToday) {
                $isEnabled = false;
                $statusMessage = 'Shift Complete';
            } elseif (! $statusData['canScan']) {
                $isEnabled = false;
                $statusMessage = $statusData['statusMessage'];
            }
        }

        $totalRequired = (int) ($intern->required_hours ?? $intern->office->total_hours_required ?? 480);
        $rendered = (int) ($intern->hours_rendered ?? 0);
        $percentage = $totalRequired > 0 ? (int) min(100, round(($rendered / $totalRequired) * 100)) : 0;

        return view('intern.attendance', [
            'qrPayload' => $qrPayload,
            'token' => $token,
            'attendanceHistory' => $attendanceHistory,
            'allAttendanceLogs' => $allAttendanceLogs,
            'is_enabled' => $isEnabled,
            'next_available_scan' => $nextAvailableScan,
            'current_record' => $todayRecord,
            'completed_today' => $completedToday,
            'status_message' => $statusMessage,
            'rendered' => $rendered,
            'totalRequired' => $totalRequired,
            'percentage' => $percentage,
            'date_status' => $effectiveSchedule,
            'schoolSchedule' => $schoolSchedule,
            'timeline_not_set' => $timelineNotSet,
        ]);
    }

    public function showVerifyPage(Request $request)
    {
        $token = $request->query('token');

        if (! $token) {
            abort(404, 'Security token missing.');
        }

        return view('intern.verify-scan', compact('token'));
    }

    public function checkStatus()
    {
        $user = Auth::user();
        $intern = $user->internDetails;
        $office = $intern->office;

        // Use AttendanceService to get consolidated status logic
        $statusData = $this->attendanceService->getInternStatus($user->id);

        $todayRecord = $statusData['todayRecord'];
        $completedToday = $statusData['completedToday'];
        $statusMessage = $statusData['statusMessage'];
        $nextAvailableScan = $statusData['nextAvailableScan'];
        $effectiveSchedule = $statusData['effectiveSchedule'];

        $totalRequired = (int) ($intern->required_hours ?? $intern->office->total_hours_required ?? 480);
        $rendered = round($intern->hours_rendered ?? 0);
        $remaining = max(0, $totalRequired - $rendered);
        $percentage = $totalRequired > 0 ? round(($rendered / $totalRequired) * 100) : 0;

        $data = [
            'geofence' => [
                'latitude' => (float) ($office->latitude ?? 14.6495),
                'longitude' => (float) ($office->longitude ?? 121.0501),
                'radius' => (int) ($office->radius_meters ?? 100),
            ],
            'can_scan' => $statusData['canScan'],
        ];

        // Generate token for any state that isn't completed using cache
        $token = null;
        if ($statusData['canScan']) {
            $token = Cache::get("active_qr_token_{$user->id}");
            if (! $token) {
                $token = Hashids::encode($user->id, now()->timestamp);
                Cache::put("active_qr_token_{$user->id}", $token, now()->addSeconds(20));
            }
            $baseUrl = url('/');
            $qrPayload = "{$baseUrl}/attendance/verify?token={$token}";
            $data['qr_code_html'] = (string) QrCode::size(350)->generate($qrPayload);
            $data['verification_link'] = $qrPayload;
        }

        return response()->json([
            'geofence' => $data['geofence'],
            'qr_code_html' => $data['qr_code_html'] ?? null,
            'verification_link' => $data['verification_link'] ?? null,
            'token' => $token,
            'can_scan' => $data['can_scan'],
            'next_available_scan' => $nextAvailableScan,
            'is_clocked_in' => (bool) $todayRecord,
            'is_completed' => (bool) $completedToday,
            'status_message' => $statusMessage,
            'rendered' => $rendered,
            'totalRequired' => $totalRequired,
            'percentage' => $percentage,
            'remaining' => $remaining,
            'date_status' => $effectiveSchedule,
        ]);
    }

    public function processScan(Request $request)
    {
        try {
            logger()->info('Attendance Scan Started', ['token' => $request->token]);

            // 0. Token Validation
            $token = $request->token;
            if (! $token) {
                return response()->json(['message' => 'Security token missing.'], 400);
            }

            $cacheKey = "qr_burned_{$token}";
            if (Cache::has($cacheKey)) {
                return response()->json(['message' => 'This QR code has already been scanned.'], 403);
            }

            $decoded = Hashids::decode($token);
            if (count($decoded) < 2) {
                return response()->json(['message' => 'Invalid QR token format.'], 400);
            }

            [$userId, $timestamp] = $decoded;
            logger()->info('Token Decoded', ['userId' => $userId, 'timestamp' => $timestamp]);

            // 1. Replay Protection (90s)
            $tokenTime = Carbon::createFromTimestamp($timestamp);
            if ($tokenTime->diffInSeconds(now()) > 90) {
                Cache::put($cacheKey, true, now()->addMinutes(10));
                Cache::forget("active_qr_token_{$userId}");
                return response()->json(['message' => 'Security token expired.'], 403);
            }

            $user = User::with('internDetails.office')->findOrFail($userId);
            $office = $user->internDetails->office;

            // 2. Geofence Check
            if (! $request->latitude || ! $request->longitude) {
                Cache::put($cacheKey, true, now()->addMinutes(10));
                Cache::forget("active_qr_token_{$userId}");
                return response()->json(['message' => 'Location coordinates required.'], 403);
            }

            $distance = $this->calculateDistance(
                $request->latitude,
                $request->longitude,
                $office->latitude,
                $office->longitude
            );

            if ($distance > $office->radius_meters) {
                $this->auditService->log('Geofence Violation', "User #{$userId} attempted scan outside boundary ({$distance}m)");
                Cache::put($cacheKey, true, now()->addMinutes(10));
                Cache::forget("active_qr_token_{$userId}");

                return response()->json(['message' => 'Location verification failed.'], 403);
            }

            // 3. Early Checkout Warning
            $todayRecord = Attendance::where('intern_id', $userId)
                ->whereDate('clock_in', now()->toDateString())
                ->first();

            if ($todayRecord && ! $todayRecord->clock_out) {
                // Require daily journal entry before clock-out
                $hasDailyJournal = \App\Models\Journal::where('intern_id', $userId)
                    ->where('journal_type', 'daily')
                    ->where('entry_date', now()->format('Y-m-d'))
                    ->exists();

                if (! $hasDailyJournal) {
                    Cache::put($cacheKey, true, now()->addMinutes(10));
                    Cache::forget("active_qr_token_{$userId}");
                    return response()->json([
                        'message' => 'You must submit your Daily Journal entry for today before clocking out.',
                    ], 403);
                }

                $statusData = $this->attendanceService->getInternStatus($userId);
                $effectiveSchedule = $statusData['effectiveSchedule'];

                // Check for approved Early Leave request
                $hasApprovedEarlyLeave = AbsenceRequest::where('intern_id', $userId)
                    ->whereDate('date', now()->toDateString())
                    ->where('status', 'approved')
                    ->where('type', 'early_leave')
                    ->exists();

                if (isset($effectiveSchedule['end_time'])) {
                    $endTime = Carbon::parse(now()->toDateString().' '.$effectiveSchedule['end_time']);

                    if (now()->lt($endTime) && ! $request->confirm_early_checkout) {
                        logger()->info('Requesting Early Checkout Confirmation');

                        $message = $hasApprovedEarlyLeave
                            ? 'You have an approved Early Leave request for today. Proceed to clock out?'
                            : 'You are clocking out before your scheduled end time ('.$endTime->format('h:i A').'). Proceed?';

                        // Note: We do NOT burn the token or clear the active token yet,
                        // because the user needs to submit the confirmation using the same token.
                        return response()->json([
                            'requires_confirmation' => true,
                            'requires_reason' => ! $hasApprovedEarlyLeave,
                            'message' => $message,
                        ]);
                    }
                }
            }

            // Burn the token immediately to ensure that any scan attempt (whether successful or blocked)
            // invalidates this token/QR code instantly.
            Cache::put($cacheKey, true, now()->addMinutes(10));

            // Clear the active QR token cache for this user immediately so that the polling
            // endpoint checkStatus() will generate a new token on the next poll.
            Cache::forget("active_qr_token_{$userId}");

            // 4. Final Processing
            $result = $this->attendanceService->recordAttendance(
                $userId,
                $request->latitude,
                $request->longitude,
                $request->early_checkout_reason
            );

            if (! $result['success']) {
                logger()->error('Attendance Service Failed', ['message' => $result['message']]);

                return response()->json(['message' => $result['message']], 400);
            }

            $this->auditService->log($result['type'] === 'in' ? 'Clock In' : 'Clock Out', "User #{$userId} via QR Scan");

            logger()->info('Attendance Recorded Successfully', ['type' => $result['type']]);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'type' => $result['type'],
            ]);

        } catch (\Exception $e) {
            logger()->error('Attendance Controller Exception', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Attendance failed: '.$e->getMessage()], 400);
        }
    }

    public function getCheckoutStatus()
    {
        $user = Auth::user();
        $intern = $user->internDetails;
        $office = $intern->office;

        // Use AttendanceService for consistency
        $statusData = $this->attendanceService->getInternStatus($user->id);

        $todayRecord = $statusData['todayRecord'];
        $completedToday = $statusData['completedToday'];
        $statusMessage = $statusData['statusMessage'];
        $nextAvailableScan = $statusData['nextAvailableScan'];
        $effectiveSchedule = $statusData['effectiveSchedule'];

        $totalRequired = (int) ($intern->required_hours ?? 480);
        $rendered = round($intern->hours_rendered ?? 0);
        $remaining = max(0, $totalRequired - $rendered);
        $percentage = $totalRequired > 0 ? round(($rendered / $totalRequired) * 100) : 0;

        $data = [
            'geofence' => [
                'latitude' => (float) ($office->latitude ?? 14.6495),
                'longitude' => (float) ($office->longitude ?? 121.0501),
                'radius' => (int) ($office->radius_meters ?? 100),
            ],
            'can_scan' => true,
        ];

        // If checking out, check if user is already checked in today
        $todayRecord = Attendance::where('intern_id', $user->id)
            ->where('date', now()->toDateString())
            ->first();

        if ($todayRecord && $todayRecord->check_out) {
            $data['status_message'] = 'You have already completed your shift today.';
            $data['can_scan'] = false;
        } else {
            // Generate a fresh QR token using Hashids for compactness
            $token = Hashids::encode($user->id, now()->timestamp);
            $baseUrl = config('app.url');
            $qrPayload = "{$baseUrl}/attendance/verify?token={$token}";
            $data['qr_code_html'] = (string) QrCode::size(350)->generate($qrPayload);
            $data['verification_link'] = $qrPayload;
        }

        return response()->json([
            'geofence' => $data['geofence'],
            'qr_code_html' => $data['qr_code_html'] ?? null,
            'verification_link' => $data['verification_link'] ?? null,
            'can_scan' => $data['can_scan'],
            'next_available_scan' => $nextAvailableScan,
            'is_clocked_in' => (bool) $todayRecord,
            'is_completed' => (bool) $completedToday,
            'status_message' => $statusMessage,
            'rendered' => $rendered,
            'totalRequired' => $totalRequired,
            'percentage' => $percentage,
            'remaining' => $remaining,
            'date_status' => $effectiveSchedule,
        ]);
    }

    /**
     * Calculate the distance between two points in meters using the Haversine formula.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // Radius of the earth in meters

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}
