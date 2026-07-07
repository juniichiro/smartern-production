<?php

namespace App\Models;

use App\Services\AttendanceService;
use App\Traits\HasShortRouteKey;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Journal extends Model
{
    use HasShortRouteKey;

    public static function compileContent(int $internId, string $type, string $dateString, ?string $notes, ?array $includedTaskIds = null): string
    {
        $date = Carbon::parse($dateString);

        if ($type === 'daily') {
            $start = $date->copy()->startOfDay();
            $end = $date->copy()->endOfDay();
            $header = 'DAILY LOG SUMMARY ('.$date->format('F d, Y').')';
        } else {
            $start = $date->copy()->startOfWeek();
            $end = $date->copy()->endOfWeek();
            $header = 'WEEKLY LOG SUMMARY (Week of '.$start->format('F d, Y').' to '.$end->format('F d, Y').')';
        }

        $query = Task::where('intern_id', $internId)
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('due_date', [$start, $end])
                    ->orWhere(function ($q) use ($start, $end) {
                        $q->where('status', 'completed')
                            ->whereBetween('updated_at', [$start, $end]);
                    })
                    ->orWhereHas('submissions', function ($q) use ($start, $end) {
                        $q->whereBetween('created_at', [$start, $end]);
                    })
                    ->orWhereIn('status', ['pending', 'revision']);
            });

        if ($includedTaskIds !== null) {
            $query->whereIn('id', $includedTaskIds);
        }

        $tasks = $query->get();

        $user = User::findOrFail($internId);
        $attendanceService = app(AttendanceService::class);
        $logs = $attendanceService->getMergedLogs($user)
            ->filter(function ($log) use ($start, $end) {
                return $log['date']->between($start->copy()->startOfDay(), $end->copy()->endOfDay());
            })
            ->sortBy(function ($log) {
                return $log['date']->timestamp;
            });

        $contentLines = [];
        $contentLines[] = '=== '.$header.' ===';
        $contentLines[] = '';

        $contentLines[] = '--- AUTOMATED WORKPLACE ACTIVITY ---';
        $contentLines[] = 'Tasks:';
        if ($tasks->isEmpty()) {
            $contentLines[] = '  No tasks recorded for this period.';
        } else {
            foreach ($tasks as $task) {
                $contentLines[] = '  • '.$task->title.' [Status: '.$task->status->label().']';
            }
        }
        $contentLines[] = '';

        $contentLines[] = 'Attendance Remarks:';
        if ($logs->isEmpty()) {
            $contentLines[] = '  No attendance records for this period.';
        } else {
            foreach ($logs as $log) {
                $dateLabel = $log['date']->format('M d, Y');
                $status = ucfirst(str_replace('_', ' ', $log['status']));
                $remarks = $log['remarks'] ? ' (Remarks: '.$log['remarks'].')' : '';

                if ($log['type'] === 'actual') {
                    $timeIn = $log['clock_in'] ? $log['clock_in']->format('h:i A') : 'N/A';
                    $timeOut = $log['clock_out'] ? $log['clock_out']->format('h:i A') : 'N/A';
                    $contentLines[] = '  • '.$dateLabel.' ['.$timeIn.' - '.$timeOut.'] Status: '.$status.$remarks;
                } else {
                    $contentLines[] = '  • '.$dateLabel.' [No Session] Status: '.$status.$remarks;
                }
            }
        }
        $contentLines[] = '';

        $contentLines[] = '--- INTERN PERSONAL LOG & NOTES ---';
        if (trim($notes ?? '') === '') {
            $contentLines[] = '  No personal notes provided.';
        } else {
            $contentLines[] = $notes;
        }

        return implode("\n", $contentLines);
    }

    protected $primaryKey = 'journal_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'intern_id',
        'journal_type',
        'title',
        'content',
        'task_ids',
        'notes',
        'entry_date',
        'created_at',
    ];

    protected $casts = [
        'task_ids' => 'array',
        'entry_date' => 'date',
        'created_at' => 'datetime',
    ];

    /**
     * Relationship to the Intern: Link journals.intern_id to intern_details.user_id[cite: ten, twelve, thirteen]
     */
    public function intern(): BelongsTo
    {
        return $this->belongsTo(InternDetail::class, 'intern_id', 'user_id');
    }
}
