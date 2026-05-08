<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskTimerController extends Controller
{
    public function start(Task $task)
    {
        $task->startTimer();
        return response()->json(['message' => 'Timer started', 'elapsed_time' => $task->getFormattedElapsedTime()]);
    }

    public function pause(Task $task)
    {
        $task->pauseTimer();
        return response()->json(['message' => 'Timer paused', 'elapsed_time' => $task->getFormattedElapsedTime()]);
    }

    public function resume(Task $task)
    {
        // Explicitly set status to in_progress when resuming via timer endpoints
        $task->status = 'in_progress';
        $resumeType = $task->resumeTimer();
        $task->save();

        // Refresh to ensure we return the latest persisted fields
        $task->refresh();

        return response()->json([
            'message' => 'Timer resumed',
            'elapsed_time' => $task->getFormattedElapsedTime(),
            'resume_type' => $resumeType,
            'timer_started_at' => $task->timer_started_at,
            'timer_paused_at' => $task->timer_paused_at,
            'total_elapsed_time' => $task->total_elapsed_time,
            'task_status' => $task->status,
        ]);
    }
}
