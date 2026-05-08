<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\TaskFollowup;
use App\Models\FSRReport;
use Illuminate\Support\Facades\Log; // Added Log facade

class Task extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'type',
        'description',
        'entry_id',
        'entry_type',
        'assigned_to',
        'location',
        'latitude',
        'longitude',
        'status',
        'due_date',
        'start_date_time',
        'end_date_time',
        'user_id',
        'dealership_id',
        'sm_approved_early_action_date',
        'timer_started_at',
        'timer_paused_at',
        'total_elapsed_time',
        'is_service',
        'lead_id',
        'amount_to_be_collected',
    ];

    protected $casts = [
        'start_date_time' => 'datetime',
        'end_date_time' => 'datetime',
        'due_date' => 'date:Y-m-d',
        'sm_approved_early_action_date' => 'date',
        'timer_started_at' => 'datetime',
        'timer_paused_at' => 'datetime',
        'total_elapsed_time' => 'integer',
        'amount_to_be_collected' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::updating(function ($task) {
            if ($task->isDirty('status')) {
                $newStatus = $task->status;
                // If moving away from in_progress, pause the timer if it's running
                if ($newStatus !== 'in_progress' && $task->timer_started_at !== null) {
                    $elapsed = abs(now()->diffInSeconds($task->timer_started_at));
                    $task->total_elapsed_time += $elapsed;
                    $task->timer_started_at = null;
                    $task->timer_paused_at = now();
                }
            }
        });
    }

    public function entry()
    {
        return $this->belongsTo(Service::class, 'entry_id');
    }

    public function dealership()
    {
        return $this->belongsTo(Dealership::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function followups()
    {
        return $this->hasMany(TaskFollowup::class);
    }

    public function fsrReport()
    {
        return $this->hasOne(FSRReport::class);
    }

    public function assignedEmployee()
    {
        return $this->belongsTo(Employee::class, 'assigned_to');
    }

    public function taskLogs()
    {
        return $this->hasMany(TaskLog::class);
    }

    // Accessor to get the derived status of the task
    public function getDerivedStatusAttribute()
    {
        $status = $this->status;

        if ($status === 'completed') {
            return 'Completed';
        }

        if ($status === 'in_progress') {
            return 'Ongoing';
        }

        return ucfirst(str_replace('_', ' ', $status));
    }

    public function getTaskTypeLabelAttribute()
    {
        if ($this->lead_id) {
            return 'Lead';
        }
        if ($this->is_service) {
            return 'Service';
        }
        return 'Other';
    }

    public function startTimer()
    {
        if ($this->timer_started_at === null) {
            $this->timer_started_at = now();
            $this->timer_paused_at = null;
            $this->save();
        }
    }

    public function pauseTimer()
    {
        Log::debug('pauseTimer called for task ' . $this->id, [
            'timer_started_at_before' => $this->timer_started_at,
            'timer_paused_at_before' => $this->timer_paused_at,
            'total_elapsed_time_before' => $this->total_elapsed_time,
        ]);
        if ($this->timer_started_at !== null) {
            $elapsed = abs(now()->diffInSeconds($this->timer_started_at));
            $this->total_elapsed_time += $elapsed;
            $this->timer_started_at = null;
            $this->timer_paused_at = now();
            $this->save();
            Log::debug('pauseTimer: Timer paused for task ' . $this->id, [
                'timer_started_at_after' => $this->timer_started_at,
                'timer_paused_at_after' => $this->timer_paused_at,
                'total_elapsed_time_after' => $this->total_elapsed_time,
            ]);
        } else {
            Log::debug('pauseTimer: Timer not running for task ' . $this->id);
        }
    }

    /**
     * Resume the timer. Returns a string indicating how the resume happened:
     * 'resumed_from_paused', 'started_anew', or 'already_running'.
     */
    public function resumeTimer()
    {
        Log::debug('resumeTimer called for task ' . $this->id, [
            'timer_paused_at' => $this->timer_paused_at,
            'timer_started_at' => $this->timer_started_at
        ]);

        // If the timer was explicitly paused, resume from paused state
        if ($this->timer_paused_at !== null) {
            $this->timer_started_at = now();
            $this->timer_paused_at = null;
            $this->save();
            Log::debug('resumeTimer: Timer resumed from paused state for task ' . $this->id);
            return 'resumed_from_paused';
        }

        // If there is no active timer (timer_started_at is null), allow starting a new timer
        if ($this->timer_started_at === null) {
            $this->timer_started_at = now();
            $this->timer_paused_at = null;
            $this->save();
            Log::debug('resumeTimer: Timer started anew for task ' . $this->id);
            return 'started_anew';
        }

        // Otherwise, nothing to do (timer already running)
        Log::debug('resumeTimer: No action taken - timer already running for task ' . $this->id, [
            'timer_started_at' => $this->timer_started_at,
            'timer_paused_at' => $this->timer_paused_at
        ]);

        return 'already_running';
    }

    public function getElapsedTimeInSeconds()
    {
        $elapsed = $this->total_elapsed_time;
        if ($this->timer_started_at !== null) {
            $elapsed += abs(now()->diffInSeconds($this->timer_started_at));
        }
        return $elapsed;
    }

    public function getFormattedElapsedTime()
    {
        $totalSeconds = $this->getElapsedTimeInSeconds();

        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }

    /**
     * Check if an employee has any active tasks (in_progress or hold).
     *
     * @param int $employeeId
     * @return bool
     */
    public static function hasActiveTaskForEmployee(int $employeeId, ?int $excludeTaskId = null): bool
    {
        $query = self::where('assigned_to', $employeeId)
            ->where('status', 'in_progress');

        if ($excludeTaskId) {
            $query->where('id', '!=', $excludeTaskId);
        }

        return $query->exists();
    }
}
