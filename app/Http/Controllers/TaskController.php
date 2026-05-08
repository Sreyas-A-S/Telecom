<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Task;
use App\Models\User;
use App\Models\Clock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Service;
use App\Models\TaskFollowup;
use App\Models\TaskLog;
use App\Models\UserGpsTrace;

class TaskController extends Controller
{
    public function getGlobalTimerStatus()
    {
        $user = Auth::user();

        if (!$user || !$user->employee) {
            return response()->json(['active' => false]);
        }

        $employee = $user->employee;

        // Find the task that the employee is currently working on
        $activeTaskLog = TaskLog::where('employee_id', $employee->id)
            ->whereIn('action_type', ['started', 'resumed'])
            ->whereNotNull('start_time')
            ->whereNull('end_time')
            ->latest('action_time')
            ->first();

        if ($activeTaskLog) {
            Log::info('Global Timer Status: Active Task Log Found', ['task_log_id' => $activeTaskLog->id, 'task_id' => $activeTaskLog->task_id, 'employee_id' => $activeTaskLog->employee_id, 'start_time' => $activeTaskLog->start_time]);
            $task = $activeTaskLog->task; // Get the associated task

            // Use the Task model's method to get the total elapsed time
            $totalElapsedTime = $task->getElapsedTimeInSeconds();

            Log::info('Global Timer Status: Final Total Elapsed Time', ['finalTotalElapsedTime' => $totalElapsedTime]);

            return response()->json([
                'active' => true,
                'task_id' => $task->id,
                'status' => $task->status, // Use task status
                'elapsed_time' => max(0, $totalElapsedTime),
                'task_started_time' => $task->timer_started_at, // Provide the actual start time of the current segment from the Task model
            ]);
        }

        Log::info('Global Timer Status: No Active Task Log Found');
        return response()->json(['active' => false]);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $clockedIn = false;
        $userRole = null;
        $assignedEmployeeId = null;
        $showEmployeeColumn = false;

        if ($user && $user->employee) {
            // Always show the employee column for tasks
            $showEmployeeColumn = true;
            $user->load('employee.role');
            $assignedEmployeeId = $user->employee->id;
            if ($user->employee->role) {
                $userRole = $user->employee->role->role;
            }
            $latestClock = Clock::where('employee_id', $user->employee->id)->latest('clock_in_time')->first();
            $clockedIn = $latestClock && is_null($latestClock->clock_out_time);
        }

        if ($request->ajax()) {
            $data = Task::with('fsrReport', 'assignedEmployee', 'entry', 'lead')
                ->whereDate('due_date', now()->toDateString());

            // Filter tasks based on user type and assignment
            if ($user && $user->user_type === 'employee') {
                $employeeId = $user->employee->id;
                $userId = $user->id;

                $data->where(function ($query) use ($employeeId, $userId) {
                    $query->where('assigned_to', $employeeId)
                        ->orWhere('tasks.user_id', $userId);
                });
            }

            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->addColumn('task_elapsed_time', function ($task) {
                    return $task->getElapsedTimeInSeconds();
                })
                ->addColumn('task_started_time', function ($task) {
                    return $task->timer_started_at;
                })
                ->addColumn('employee_name', function ($row) {
                    return $row->assignedEmployee ? ($row->assignedEmployee->name ?? 'N/A') : 'N/A';
                })
                ->editColumn('is_service', function ($row) {
                    return $row->is_service ? 'Yes' : 'No';
                })
                ->addColumn('action', function ($row) use ($userRole, $assignedEmployeeId, $clockedIn) { // Pass clockedIn

                    $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-4">';

                    // Conditionally render FSR and Follow-up buttons
                    if ($assignedEmployeeId && $row->assigned_to == $assignedEmployeeId) {

                        if ($userRole === 'service_engineer') {
                            // dd($row->fsrReport);

                            if ($row->is_service == 1 || $row->lead_id) {
                                if (!empty($row->fsrReport)) {
                                    $btn .= '<li><a href="' . route('fsr.edit', $row->fsrReport->id) . '" class="btn btn-success btn-sm edit-fsr-task" title="Edit FSR">FSR</a></li>';
                                    if ($row->is_service == 1) {
                                        $btn .= '<li><a href="' . route('fsr-quotations.review.index', ['task' => $row->id]) . '" class="btn btn-warning btn-sm view-quotations-task" title="View Quotations">Quotations</a></li>';
                                    }
                                } else {
                                    $btn .= '<li><a href="' . route('tasks.fsr.create', $row->id) . '" class="btn btn-info btn-sm add-fsr-task" title="Add FSR">FSR</a></li>';
                                }
                            }
                        }
                        $btn .= '<li><a href="' . route('tasks.followups.index', ['task' => $row->id]) . '" class="add-followup-task" title="Add Follow-up"><i class="fa fa-plus text-primary"></i></a></li>';
                    }

                    $btn .= '<li><a href="javascript:void(0)" class="view-task" data-id="' . $row->id . '" title="View"><i class="fa fa-eye text-success"></i></a></li>';

                    $disabledClass = !$clockedIn ? 'disabled' : '';
                    $disabledStyle = !$clockedIn ? 'pointer-events: none; opacity: 0.5;' : '';

                    // Conditionally render delete button
                    if ($userRole === 'service_manager'  || $userRole === 'Service Manager') {
                        $btn .= '<li><a href="javascript:void(0)" class="delete-task ' . $disabledClass . '" data-id="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#deleteTaskModal" title="Delete" style="' . $disabledStyle . '"><i class="icon-trash text-danger"></i></a></li>';
                    }

                    // Conditionally render start/stop/pause/resume buttons for assigned engineer
                    if ($assignedEmployeeId && $row->assigned_to == $assignedEmployeeId) {
                        // dd($row->derived_status);
                        if ($row->derived_status === 'pending') {
                            $btn .= '<li><a href="javascript:void(0)" class="start-task ' . $disabledClass . '" data-id="' . $row->id . '" title="Start" style="' . $disabledStyle . '"><i class="fa fa-play text-primary"></i></a></li>';
                        } elseif ($row->derived_status === 'hold') {
                            $btn .= '<li><a href="javascript:void(0)" class="resume-task ' . $disabledClass . '" data-id="' . $row->id . '" title="Resume" style="' . $disabledStyle . '"><i class="fa fa-play text-success"></i></a></li>';
                        } elseif ($row->derived_status === 'in_progress' || $row->derived_status === 'In Progress') {
                            $btn .= '<li><a href="javascript:void(0)" class="pause-task ' . $disabledClass . '" data-id="' . $row->id . '" title="Pause" style="' . $disabledStyle . '"><i class="fa fa-pause text-warning"></i></a></li>';
                            $btn .= '<li><a href="javascript:void(0)" class="stop-task ' . $disabledClass . '" data-id="' . $row->id . '" title="Stop" style="' . $disabledStyle . '"><i class="fa fa-stop text-danger"></i></a></li>';
                        } elseif ($row->derived_status === 'stopped') {
                            $btn .= '<li><a href="javascript:void(0)" class="resume-task ' . $disabledClass . '" data-id="' . $row->id . '" title="Resume" style="' . $disabledStyle . '"><i class="fa fa-play text-success"></i></a></li>';
                        } elseif ($row->derived_status === 'completed') {
                            $btn .= '<li><a href="javascript:void(0)" class="start-task ' . $disabledClass . '" data-id="' . $row->id . '" title="Restart" style="' . $disabledStyle . '"><i class="fa fa-play text-primary"></i></a></li>';
                        }
                    }

                    $btn .= '</ul>';
                    // dd($row->derived_status);
                    return $btn;
                })
                ->editColumn('type', function ($row) {
                    if ($row->type == 'client_based') {
                        return '<span class="badge bg-primary">Client Based</span>';
                    } else if ($row->type == 'open') {
                        return '<span class="badge bg-secondary">Open</span>';
                    } else {
                        return ucfirst($row->type); // Handle unexpected values
                    }
                })
                ->editColumn('due_date', function ($row) {
                    return $row->due_date ? $row->due_date->format('d M Y') : 'N/A';
                })
                ->editColumn('start_date_time', function ($row) {
                    return $row->start_date_time ? $row->start_date_time->format('d M Y') : 'N/A';
                })
                ->editColumn('end_date_time', function ($row) {
                    return $row->end_date_time ? $row->end_date_time->format('d M Y') : 'N/A';
                })
                ->rawColumns(['action', 'status', 'type'])
                ->with(['clockedIn' => $clockedIn, 'userRole' => $userRole]) // Add clockedIn and userRole status here
                ->make(true);
        }

        $baseQuery = Task::query()->whereDate('due_date', now()->toDateString());

        if ($user && $user->user_type === 'employee') {
            $employeeId = $user->employee->id;
            $userId = $user->id;

            $baseQuery->where('assigned_to', $employeeId);
        }

        $totalTasks = $baseQuery->count();
        $pendingTasks = $baseQuery->clone()->where('status', 'pending')->count();
        $ongoingTasks = $baseQuery->clone()->where('status', 'in_progress')->count();
        $holdTasks = $baseQuery->clone()->where('status', 'hold')->count();
        $completedTasks = $baseQuery->clone()->where('status', 'completed')->count();
        $employees = collect(); // Initialize as an empty collection

        if ($user->user_type === 'admin') {
            // If admin, fetch all employees
            $employees = Employee::all();
        } else if ($user->user_type === 'employee') {
            // Load the employee relationship if it's not already loaded
            // $user->employee is already loaded via Auth::user()


            if ($user->employee && $user->employee->dealership_id === null) {
                // If employee and dealership_id is null, fetch all employees
                $employees = Employee::all();
                //   dd(2);
            } else if ($user->employee) {
                // If employee and dealership_id is not null, fetch employees reporting to current user
                // dd($user->id);
                $employees = Employee::where('dealership_id', $user->employee->dealership_id)->get();
            }
        }

        return view('tasks.index', compact('totalTasks', 'pendingTasks', 'ongoingTasks', 'holdTasks', 'completedTasks', 'employees', 'clockedIn', 'showEmployeeColumn'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'type' => 'required|string|in:client_based,open',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:employees,id',
            'due_date' => 'required|date',
            'location' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'nullable|string|in:pending,in_progress,completed,hold,partial',
            'dealership_id' => 'nullable|exists:dealerships,id',
            'lead_id' => 'nullable|exists:leads,id',
            'amount_to_be_collected' => 'nullable|numeric|min:0',
        ]);

        $validatedData['user_id'] = Auth::id(); // Set the creator's ID
        $validatedData['status'] = $validatedData['status'] ?? 'pending'; // Default status

        if (isset($validatedData['assigned_to'])) {
            $assignedEmployee = Employee::find($validatedData['assigned_to']);
            if ($assignedEmployee) {
                if (!isset($validatedData['dealership_id'])) {
                    $validatedData['dealership_id'] = $assignedEmployee->dealership_id;
                }
            }
        } elseif (!isset($validatedData['dealership_id'])) {
            // If no employee is assigned and no dealership provided, try to get dealership_id from the current user's employee
            $user = Auth::user();
            if ($user && $user->employee) {
                $validatedData['dealership_id'] = $user->employee->dealership_id;
            }
        }

        Log::info('Task Store Validated Data:', $validatedData);

        try {
            $task = Task::create($validatedData);

            $user = Auth::user();
            if ($user && $user->employee) {
                TaskLog::create([
                    'task_id' => $task->id,
                    'employee_id' => $user->employee->id,
                    'action_type' => 'created',
                ]);

                if ($task->assigned_to) {
                    TaskLog::create([
                        'task_id' => $task->id,
                        'employee_id' => $task->assigned_to,
                        'action_type' => 'assigned',
                    ]);
                }
            }

            return response()->json(['message' => 'Task created successfully.', 'task' => $task], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating task.', 'error' => $e->getMessage()], 500);
        }
    }

    public function show(Task $task)
    {
        if ($task->type === 'client_based') {
            $task->load('entry.client', 'entry.product', 'entry.productModel', 'entry.modelSeries', 'lead.client', 'lead.product', 'lead.productModel', 'lead.modelSeries', 'followups.user', 'fsrReport');
        } else {
            $task->load('followups.user', 'fsrReport');
        }

        $user = Auth::user();
        $employee = $user->employee;
        $completedTime = 0;
        $task_started_time = null; // Initialize here

        if ($employee) {
            // Sum up completed segments for this task by this employee
            $completedSegments = TaskLog::where('task_id', $task->id)
                ->where('employee_id', $employee->id)
                ->whereNotNull('start_time')
                ->whereNotNull('end_time')
                ->get();

            foreach ($completedSegments as $log) {
                $completedTime += (int) abs($log->end_time->diffInSeconds($log->start_time));
            }
        }

        // Get task_started_time, scoped to the current employee
        if ($task->status === 'in_progress' && $employee) {
            $lastStartTimeLog = $task->taskLogs()
                ->where('employee_id', $employee->id) // Filter by employee
                ->whereIn('action_type', ['started', 'resumed'])
                ->latest('action_time')
                ->first();
            $task_started_time = $lastStartTimeLog ? $lastStartTimeLog->start_time : null;
        }

        // Manually construct the response array
        $taskData = $task->toArray();
        $taskData['completed_time'] = $completedTime;
        $taskData['task_started_time'] = $task_started_time;

        return response()->json($taskData);
    }

    public function edit(Task $task)
    {
        return response()->json($task);
    }

    public function update(Request $request, Task $task)
    {
        Log::info('Task Update Request Data:', $request->all());

        $validatedData = $request->validate([
            'type' => 'required|string|in:client_based,open',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:employees,id',
            'due_date' => 'required|date',
            'location' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'status' => 'nullable|string|in:pending,in_progress,completed,hold,partial',
            'dealership_id' => 'nullable|exists:dealerships,id',
            'lead_id' => 'nullable|exists:leads,id',
            'amount_to_be_collected' => 'nullable|numeric|min:0',
        ]);

        // If assigned_to is being updated, we must:
        // 1. Swap Employee ID -> User ID
        // 2. Update dealership_id if not provided
        if (isset($validatedData['assigned_to'])) {
            $assignedEmployee = Employee::find($validatedData['assigned_to']);
            if ($assignedEmployee) {
                // Update Dealership if not provided
                if (!isset($validatedData['dealership_id'])) {
                    $validatedData['dealership_id'] = $assignedEmployee->dealership_id;
                }
            }
        } elseif (!isset($validatedData['dealership_id'])) {
            // If dealership_id is not provided in the request and assigned_to is not changed/provided,
            // ensure it's set from the current user's employee if available.
            $user = Auth::user();
            if ($user && $user->employee) {
                $validatedData['dealership_id'] = $user->employee->dealership_id;
            }
        }

        $oldAssignedTo = $task->assigned_to;
        $task->update($validatedData);
        $newAssignedTo = $task->assigned_to;

        $user = Auth::user();
        if ($user && $user->employee) {
            TaskLog::create([
                'task_id' => $task->id,
                'employee_id' => $user->employee->id,
                'action_type' => 'updated',
            ]);

            if ($oldAssignedTo != $newAssignedTo && $newAssignedTo) {
                TaskLog::create([
                    'task_id' => $task->id,
                    'employee_id' => $newAssignedTo,
                    'action_type' => 'assigned',
                ]);
            }
        }

        return redirect()->route('tasks.index');
    }

    public function destroy(Task $task)
    {
        $user = Auth::user();

        $clockedIn = false;
        if ($user && $user->employee) {
            $latestClock = Clock::where('employee_id', $user->employee->id)->latest('clock_in_time')->first();
            $clockedIn = $latestClock && is_null($latestClock->clock_out_time);
        }

        if (!$clockedIn) {
            return response()->json(['message' => 'You must be clocked in to perform this action.'], 403);
        }

        $isServiceManager = $user && $user->employee && $user->employee->role && ($user->employee->role->role === 'service_manager'  || $user->employee->role->role === 'Service Manager');
        $isTaskCreator = $user && $user->id === $task->user_id;

        if ($isServiceManager || $isTaskCreator) {
            if ($task->type === 'client_based' && $task->entry_id) {
                $service = \App\Models\Service::find($task->entry_id);
                if ($service) {
                    $service->service_engineer_id = null;
                    $service->service_engineer_id_2 = null;
                    $service->save();
                }
            }

            TaskLog::create([
                'task_id' => $task->id,
                'employee_id' => $user->employee->id,
                'action_type' => 'deleted',
            ]);

            $task->delete();
            return response()->json(['message' => 'Task deleted successfully.']);
        }

        return response()->json(['message' => 'Unauthorized to delete tasks.'], 403);
    }

    public function startTask(Request $request, Task $task)
    {
        $user = Auth::user();
        $clockedIn = false;
        if ($user && $user->employee) {
            $latestClock = Clock::where('employee_id', $user->employee->id)->latest('clock_in_time')->first();
            $clockedIn = $latestClock && is_null($latestClock->clock_out_time);
        }

        if ($user && $user->employee && $user->employee->id === $task->assigned_to && $clockedIn) {
            // Check if the employee already has an active task
            if (\App\Models\Task::hasActiveTaskForEmployee($user->employee->id, $task->id)) {
                return response()->json(['message' => 'You already have an active task. Please complete or pause it before starting a new one.'], 409); // 409 Conflict
            }

            // Check for Service Manager approval for early action
            $setting = \App\Models\Setting::where('name', 'task continuation approval')->first();
            if ($task->is_service) {
                if ($setting) {
                    $dealershipId = $user->employee ? $user->employee->dealership_id : null;
                    $dealershipSetting = \App\Models\DealershipSetting::where('dealership_id', $dealershipId)
                        ->where('setting_id', $setting->id)
                        ->first();

                    // If setting is enabled and user is not a service manager, deny
                    if ($dealershipSetting && $dealershipSetting->enabled) {

                        if (
                            $task->status !== 'completed' &&
                            $task->start_date_time && // Ensure start_date_time is not null
                            (\Carbon\Carbon::parse($task->start_date_time)->toDateString() < now()->toDateString()) && // Check if start_date_time is strictly after today
                            ($task->sm_approved_early_action_date === null || $task->sm_approved_early_action_date->toDateString() < now()->toDateString())
                        ) {
                            return response()->json(['message' => 'Service Manager approval required for early action on this date.'], 403);
                        }
                    }
                }
            }

            if ($task->start_date_time == null) {
                $task->start_date_time = now();
            }

            $task->status = 'in_progress';
            $task->startTimer(); // Call the Task model's startTimer method

            TaskLog::create([
                'task_id' => $task->id,
                'employee_id' => $user->employee->id,
                'action_type' => 'started',
                'start_time' => now(),
            ]);

            return response()->json(['message' => 'Task started successfully.']);
        }
        return response()->json(['message' => 'Unauthorized or not clocked in to start this task.'], 403);
    }

    public function pauseTask(Request $request, Task $task)
    {
        $user = Auth::user();
        $clockedIn = false;
        if ($user && $user->employee) {
            $latestClock = Clock::where('employee_id', $user->employee->id)->latest('clock_in_time')->first();
            $clockedIn = $latestClock && is_null($latestClock->clock_out_time);
        }

        if ($user && $user->employee && $user->employee->id === $task->assigned_to && $clockedIn) {
            // Check for Service Manager approval for early action
            // if ($task->status !== 'completed' &&
            //     $task->start_date_time && // Ensure start_date_time is not null
            //     (\Carbon\Carbon::parse($task->start_date_time)->toDateString() < now()->toDateString()) && // Check if start_date_time is strictly after today
            //     ($task->sm_approved_early_action_date === null || $task->sm_approved_early_action_date->toDateString() < now()->toDateString())) {
            //     return response()->json(['message' => 'Service Manager approval required for early action on this date.'], 403);
            // }

            $task->status = 'hold';
            $task->pauseTimer(); // Call the Task model's pauseTimer method

            TaskLog::create([
                'task_id' => $task->id,
                'employee_id' => $user->employee->id,
                'action_type' => 'paused',
            ]);

            return response()->json(['message' => 'Task paused successfully.']);
        }
        return response()->json(['message' => 'Unauthorized or not clocked in to pause this task.'], 403);
    }

    public function resumeTask(Request $request, Task $task)
    {
        $user = Auth::user();
        $clockedIn = false;
        if ($user && $user->employee) {
            $latestClock = Clock::where('employee_id', $user->employee->id)->latest('clock_in_time')->first();
            $clockedIn = $latestClock && is_null($latestClock->clock_out_time);
        }

        if ($user && $user->employee && $user->employee->id === $task->assigned_to && $clockedIn) {
            $task->refresh(); // Reload the task from the database
            Log::debug('resumeTask: before resume', ['task_id' => $task->id, 'status' => $task->status, 'timer_started_at' => $task->timer_started_at, 'timer_paused_at' => $task->timer_paused_at, 'total_elapsed_time' => $task->total_elapsed_time]);
            // Check for Service Manager approval for early action

            $setting = \App\Models\Setting::where('name', 'task continuation approval')->first();
            if ($task->is_service) {
                if ($setting) {
                    $dealershipId = $user->employee ? $user->employee->dealership_id : null;
                    $dealershipSetting = \App\Models\DealershipSetting::where('dealership_id', $dealershipId)
                        ->where('setting_id', $setting->id)
                        ->first();

                    // If setting is enabled and user is not a service manager, deny
                    if ($dealershipSetting && $dealershipSetting->enabled) {

                        if (
                            $task->status !== 'completed' &&
                            $task->start_date_time && // Ensure start_date_time is not null
                            (\Carbon\Carbon::parse($task->start_date_time)->toDateString() < now()->toDateString()) && // Check if start_date_time is strictly after today
                            ($task->sm_approved_early_action_date === null || $task->sm_approved_early_action_date->toDateString() < now()->toDateString())
                        ) {
                            return response()->json(['message' => 'Service Manager approval required for early action on this date.'], 403);
                        }
                    }
                }
            }


            // Check if there is any other task already in progress for this employee
            if (\App\Models\Task::hasActiveTaskForEmployee($user->employee->id, $task->id)) {
                return response()->json(['message' => 'You already have an active task. Please complete or pause it before starting a new one.'], 409); // 409 Conflict
            }

            // Ensure status is set and persisted. resumeTimer also saves, but we want to be explicit
            $task->status = 'in_progress';
            // Call the Task model's resumeTimer method and capture type
            $resumeType = $task->resumeTimer();

            // Refresh to get the latest timer fields written by resumeTimer()
            $task->refresh();

            // Persist status explicitly (resumeTimer saves other fields but being explicit avoids race conditions)
            $task->save();

            Log::debug('resumeTask: after resume', ['task_id' => $task->id, 'status' => $task->status, 'timer_started_at' => $task->timer_started_at, 'timer_paused_at' => $task->timer_paused_at, 'total_elapsed_time' => $task->total_elapsed_time, 'resume_type' => $resumeType]);

            // Create TaskLog with the actual timer start time if available
            TaskLog::create([
                'task_id' => $task->id,
                'employee_id' => $user->employee->id,
                'action_type' => 'resumed',
                'start_time' => $task->timer_started_at ?? now(),
            ]);

            return response()->json([
                'message' => 'Task resumed successfully.',
                'resume_type' => $resumeType,
                'timer_started_at' => $task->timer_started_at,
                'timer_paused_at' => $task->timer_paused_at,
                'total_elapsed_time' => $task->total_elapsed_time,
                'task_status' => $task->status,
            ]);
        }
        return response()->json(['message' => 'Unauthorized or not clocked in to resume this task.'], 403);
    }

    public function stopTask(Request $request, Task $task)
    {
        $user = Auth::user();
        $clockedIn = false;
        if ($user && $user->employee) {
            $latestClock = Clock::where('employee_id', $user->employee->id)->latest('clock_in_time')->first();
            $clockedIn = $latestClock && is_null($latestClock->clock_out_time);
        }

        if ($user && $user->employee && $user->employee->id === $task->assigned_to && $clockedIn) {
            // Check for Service Manager approval for early action
            // if ($task->status !== 'completed' &&
            //     $task->start_date_time && // Ensure start_date_time is not null
            //     (\Carbon\Carbon::parse($task->start_date_time)->toDateString() > now()->toDateString()) && // Check if start_date_time is strictly after today
            //     ($task->sm_approved_early_action_date === null || $task->sm_approved_early_action_date->toDateString() !== now()->toDateString())) {
            //     return response()->json(['message' => 'Service Manager approval required for early action on this date.'], 403);
            // }

            $task->pauseTimer(); // Call the Task model's pauseTimer method
            $task->status = 'stopped';
            $task->save();

            TaskLog::create([
                'task_id' => $task->id,
                'employee_id' => $user->employee->id,
                'action_type' => 'stopped',
            ]);

            return response()->json(['message' => 'Task stopped successfully.']);
        }
        return response()->json(['message' => 'Unauthorized or not clocked in to stop this task.'], 403);
    }

    public function updateTaskStatus(Request $request, Task $task)
    {
        $user = Auth::user();
        if (!$user || !$user->employee || $user->employee->id !== $task->assigned_to || $user->employee->role->role !== 'service_engineer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|string|in:pending,in_progress,completed,hold,partial',
        ]);

        $task->status = $request->status;
        $task->save();

        if ($user->employee) {
            TaskLog::create([
                'task_id' => $task->id,
                'employee_id' => $user->employee->id,
                'action_type' => 'status_updated_to_' . $request->status,
            ]);
        }

        return response()->json(['message' => 'Task status updated successfully.']);
    }

    public function approveEarlyAction(Request $request, Task $task)
    {
        $user = Auth::user();

        // Authorization: Only service managers can approve early actions
        if (!$user || !$user->employee || !$user->employee->role || ($user->employee->role->role !== 'service_manager' || $user->employee->role->role === 'Service Manager')) {
            return response()->json(['message' => 'Unauthorized to approve early action.'], 403);
        }

        // Update the task with the current date as the approval date
        $task->sm_approved_early_action_date = now()->toDateString();
        $task->save();

        // Log the action
        if ($user->employee) {
            TaskLog::create([
                'task_id' => $task->id,
                'employee_id' => $user->employee->id,
                'action_type' => 'sm_approved_early_action',
                'notes' => 'Service Manager approved early action for ' . now()->toDateString(),
            ]);
        }

        return response()->json(['message' => 'Early action approved successfully for today.'], 200);
    }

    public function addFSR(Request $request, Task $task)
    {
        $user = Auth::user();

        // Ensure only service engineers can add FSR to their assigned and completed tasks
        if (!$user || !$user->employee || $user->employee->id !== $task->assigned_to || $user->employee->role->role !== 'service_engineer' || $task->status !== 'completed') {
            return response()->json(['message' => 'Unauthorized to add FSR to this task.'], 403);
        }

        $validatedData = $request->validate([
            'on_site_assessment' => 'required|string',
            'analysis_of_cause' => 'required|string',
            'actions_taken' => 'required|string',
            // Add other FSR specific fields here
        ]);

        try {
            $fsrReport = new \App\Models\FSRReport();
            $fsrReport->task_id = $task->id;
            $fsrReport->submitted_by_user_id = $user->id; // Use user ID, not employee ID
            $fsrReport->on_site_assessment = $validatedData['on_site_assessment'];
            $fsrReport->analysis_of_cause = $validatedData['analysis_of_cause'];
            $fsrReport->actions_taken = $validatedData['actions_taken'];
            // Assign other validated fields
            $fsrReport->save();

            return response()->json(['message' => 'FSR added successfully.', 'fsr' => $fsrReport], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error adding FSR.', 'error' => $e->getMessage()], 500);
        }
    }

    public function createFSR(Task $task)
    {
        // You might want to add authorization checks here
        $user = Auth::user();
        if (!$user || !$user->employee || $user->employee->id !== $task->assigned_to || $user->employee->role->role !== 'service_engineer') {
            abort(403, 'Unauthorized to add FSR to this task.');
        }

        $task->load('entry.client.lead.leadSource', 'entry.client.lead.leadCategory', 'entry.client.lead.product', 'entry.client.lead.productModel', 'entry.client.lead.modelSeries', 'entry.product', 'entry.productModel', 'entry.modelSeries', 'lead.leadSource', 'lead.leadCategory', 'lead.product', 'lead.productModel', 'lead.modelSeries', 'lead.client');
        $client = $task->entry->client ?? $task->lead->client ?? null;
        $lead = $task->lead;
        return view('tasks.fsr.create', compact('task', 'client', 'lead'));
    }

    public function showRouteMap(Request $request, Task $task)
    {
        $user = Auth::user();

        // Ensure the task is assigned to an employee
        if (!$task->assigned_to) {
            abort(404, 'No employee assigned to this task for route mapping.');
        }

        // Authorization: Only the assigned employee, or a service manager can view the route map
        $isAssignedEmployee = ($user && $user->employee && $user->employee->id === $task->assigned_to);
        $isServiceManager = ($user && $user->employee && $user->employee->role && ($user->employee->role->role === 'service_manager' || $user->employee->role->role === 'Service Manager'));

        if (!($isAssignedEmployee || $isServiceManager)) {
            abort(403, 'Unauthorized to view this route map.');
        }

        $assignedEmployeeId = $task->assigned_to;

        // For route map, we are interested in the GPS traces for the day the task is due or performed.
        // Let's consider the task's due_date as the primary date for fetching GPS data.
        // If the task has start_date_time, we can use that as well.
        $targetDate = $task->start_date_time ? \Carbon\Carbon::parse($task->start_date_time) : \Carbon\Carbon::parse($task->due_date);

        $startDate = $request->input('start_date', $targetDate->toDateString());
        $endDate = $request->input('end_date', $targetDate->toDateString());

        $gpsTraces = UserGpsTrace::where('user_id', $assignedEmployeeId)
            ->whereDate('recorded_at', '>=', $startDate)
            ->whereDate('recorded_at', '<=', $endDate)
            ->orderBy('recorded_at')
            ->get();

        // Fetch clock-in/clock-out times for the given user and date range
        $clockRecords = Clock::where('employee_id', $assignedEmployeeId)
            ->whereDate('clock_in_time', '>=', $startDate)
            ->whereDate('clock_in_time', '<=', $endDate)
            ->get();

        // Filter gpsTraces to only include points within clock-in/clock-out periods
        $filteredGpsTraces = $gpsTraces->filter(function ($trace) use ($clockRecords, $endDate) {
            foreach ($clockRecords as $clock) {
                $clockIn = \Carbon\Carbon::parse($clock->clock_in_time);
                $clockOut = $clock->clock_out_time ? \Carbon\Carbon::parse($clock->clock_out_time) : null;

                // If clock_out_time is null, consider the session ongoing
                // If endDate is also null, use a very distant future date
                if ($clockOut === null) {
                    $clockOut = $endDate ? \Carbon\Carbon::parse($endDate)->endOfDay() : \Carbon\Carbon::createFromDate(9999, 12, 31);
                }

                if ($trace->recorded_at->greaterThanOrEqualTo($clockIn) && $trace->recorded_at->lessThanOrEqualTo($clockOut)) {
                    return true;
                }
            }
            return false;
        });

        $followups = TaskFollowup::where('task_id', $task->id)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->get();

        if ($request->ajax() || $request->has('fetch_data_only')) {
            return response()->json([
                'gpsTraces' => $filteredGpsTraces->values()->all(),
                'followups' => $followups,
            ]);
        }

        return view('tasks.route-map', compact('task', 'filteredGpsTraces', 'followups', 'startDate', 'endDate'));
    }

    public function getAnalytics(Request $request, Task $task)
    {
        $task->load('taskLogs.employee');
        $logs = $task->taskLogs()->orderBy('created_at', 'desc')->get();
        // Calculate total time
        $totalSeconds = $task->getElapsedTimeInSeconds();
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);

        return response()->json([
            'total_time' => "$hours hrs $minutes mins",
            'logs' => $logs->map(function ($log) {
                return [
                    'action_type' => ucfirst($log->action_type),
                    'action_time' => $log->action_time, // Assuming this is a datetime string or carbon object
                    'employee_name' => $log->employee->name ?? 'N/A'
                ];
            })
        ]);
    }
    public function overview(Task $task)
    {
        // Load relationships needed for the view
        $task->load(['followups.user', 'taskLogs.employee', 'assignedEmployee']);

        // Calculate task analytics (Total Time)
        $totalSeconds = $task->getElapsedTimeInSeconds();
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $totalTime = "$hours hrs $minutes mins";

        // Get logs
        $taskLogs = $task->taskLogs()->orderBy('created_at', 'desc')->get();

        return view('tasks.overview', compact('task', 'totalTime', 'taskLogs'));
    }
}
