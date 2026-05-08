<?php

namespace App\Services;

use App\Models\Task;
use App\Models\Service;
use App\Models\Lead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskService
{
    public function createTasksForService(Request $request, Service $service)
    {
        $user = Auth::user();
        $dealershipId = $user->employee->dealership_id ?? $service->dealership_id ?? null;

        // Requirement: Only ONE task per service.
        // We look for an existing task for this service.
        $task = Task::where('entry_id', $service->id)
            ->where('entry_type', Service::class)
            ->first();

        // If we have at least one engineer assigned (primary or secondary)
        if ($service->service_engineer_id || $service->service_engineer_id_2) {
            // We use service_engineer_id as the primary assigned_to. 
            // If primary is not set but secondary is, we use secondary.
            $engineerId = $service->service_engineer_id ?: $service->service_engineer_id_2;
            
            // For due date, we prefer due_date_1, then due_date_2
            $dueDate = $request->input('due_date_1') ?? $service->due_date_1 ?? $request->input('due_date_2') ?? $service->due_date_2 ?? null;

            // applyTaskData will update the existing task (preserving history) or create a new one.
            $this->applyTaskData($task, $engineerId, $service, $dealershipId, $dueDate);
        } else {
            // No engineers assigned.
            if ($task) {
                // Only delete if NO work history exists.
                $hasWorkHistory = \App\Models\TaskFollowup::where('task_id', $task->id)->exists();
                if (!$hasWorkHistory) {
                    $task->delete();
                } else {
                    // Mark as completed/closed if work was done but engineers were removed.
                    $task->update(['status' => 'completed']);
                }
            }
        }
    }

    private function applyTaskData($task, $engineerId, Service $service, $dealershipId, $dueDate)
    {
        $taskData = [
            'title' => $service->name ?? $service->referral_id ?? 'Service Complaint',
            'type' => 'client_based',
            'description' =>  $service->id . ' - ' . $service->description,
            'entry_id' => $service->id,
            'entry_type' => Service::class,
            'assigned_to' => $engineerId,
            'location' => $service->requested_location,
            'latitude' => $service->latitude,
            'longitude' => $service->longitude,
            'status' => $task ? $task->status : 'pending',
            'user_id' => Auth::id(),
            'due_date' => $dueDate,
            'dealership_id' => $dealershipId,
            'is_service' => 1,
        ];

        $oldAssignedTo = $task ? $task->assigned_to : null;

        if ($task) {
            $task->update($taskData);
        } else {
            $taskData['start_date_time'] = now();
            $task = Task::create($taskData);
        }

        $newAssignedTo = $task->assigned_to;
        $user = Auth::user();

        if ($user && $user->employee) {
            if (!$oldAssignedTo && $newAssignedTo) {
                // New task or initial assignment
                \App\Models\TaskLog::create([
                    'task_id' => $task->id,
                    'employee_id' => $newAssignedTo,
                    'action_type' => 'assigned',
                    'action_time' => now(),
                ]);
            } elseif ($oldAssignedTo != $newAssignedTo && $newAssignedTo) {
                // Reassignment
                \App\Models\TaskLog::create([
                    'task_id' => $task->id,
                    'employee_id' => $newAssignedTo,
                    'action_type' => 'assigned',
                    'action_time' => now(),
                ]);
            }
        }

        return $task;
    }

    public function createTasksForLead(Request $request, Lead $lead)
    {
        $user = Auth::user();
        $dealershipId = $user->employee->dealership_id ?? $lead->dealership_id ?? null;

        // Try to find existing task for this lead
        $task = Task::where('lead_id', $lead->id)->first();

        $taskData = [
            'title' => $lead->name ?? 'Lead Assignment',
            'type' => 'client_based',
            'description' => $lead->remarks ?? 'Lead assigned',
            'entry_id' => null,
            'assigned_to' => $lead->employee_id,
            'location' => $lead->location,
            'latitude' => $lead->latitude,
            'longitude' => $lead->longitude,
            'status' => $task ? $task->status : 'pending',
            'user_id' => $user->id,
            'due_date' => $request->input('due_date'),
            'dealership_id' => $dealershipId,
            'lead_id' => $lead->id,
            'is_service' => 0,
        ];

        $oldAssignedTo = $task ? $task->assigned_to : null;

        if ($task) {
            $task->update($taskData);
        } elseif ($lead->employee_id) {
            $taskData['start_date_time'] = now();
            $task = Task::create($taskData);
        }

        if ($task) {
            $newAssignedTo = $task->assigned_to;
            if ($user && $user->employee) {
                if ($oldAssignedTo != $newAssignedTo && $newAssignedTo) {
                    \App\Models\TaskLog::create([
                        'task_id' => $task->id,
                        'employee_id' => $newAssignedTo,
                        'action_type' => 'assigned',
                        'action_time' => now(),
                    ]);
                }
            }
        }
    }
}
