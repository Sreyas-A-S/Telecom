<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Task;
use App\Models\TaskLog;

/**
 * CLEANUP SCRIPT: Fix tasks where the timer kept running after the status was changed.
 */

// Find all tasks that are NOT 'in_progress' but still have an active 'timer_started_at'
$brokenTasks = Task::where('status', '!=', 'in_progress')
    ->whereNotNull('timer_started_at')
    ->get();

echo "Found " . $brokenTasks->count() . " broken tasks.\n";

foreach ($brokenTasks as $task) {
    echo "Processing Task ID: {$task->id} (Status: {$task->status})\n";
    
    // To be as accurate as possible, we look for the latest log entry 
    // that likely coincided with the status change.
    $latestLog = TaskLog::where('task_id', $task->id)
        ->whereIn('action_type', [
            'updated', 
            'paused', 
            'stopped', 
            'status_updated_to_completed', 
            'status_updated_to_hold', 
            'status_updated_to_stopped',
            'status_updated_to_pending',
            'status_updated_to_partial'
        ])
        ->latest('created_at')
        ->first();
    
    // Use the log time as the end point, or fallback to 'now' if no log exists
    $endTime = $latestLog ? $latestLog->created_at : now();
    
    $start = $task->timer_started_at;
    $elapsed = abs($endTime->diffInSeconds($start));
    
    // Update the task record: stop the timer and add the final segment to total_elapsed_time
    $task->total_elapsed_time += $elapsed;
    $task->timer_started_at = null;
    $task->timer_paused_at = $endTime;
    
    // Save the changes
    $task->save();
    
    echo "Fixed Task ID: {$task->id}. Final Elapsed: " . $task->getFormattedElapsedTime() . "\n";
}

echo "Cleanup complete.\n";
