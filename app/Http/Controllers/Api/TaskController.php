<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Employee;
use App\Models\Clock;
use App\Models\TaskLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(
 *     name="Tasks",
 *     description="API Endpoints for Tasks"
 * )
 *
 * @OA\Schema(
 *     schema="Task",
 *     title="Task",
 *     description="Task model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="Task ID"
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         description="Type of task (client_based or open)"
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="Title of the task"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Description of the task"
 *     ),
 *     @OA\Property(
 *         property="assigned_to",
 *         type="integer",
 *         description="ID of the employee assigned to the task"
 *     ),
 *     @OA\Property(
 *         property="due_date",
 *         type="string",
 *         format="date",
 *         description="Due date of the task"
 *     ),
 *     @OA\Property(
 *         property="location",
 *         type="string",
 *         description="Location of the task"
 *     ),
 *     @OA\Property(
 *         property="latitude",
 *         type="number",
 *         format="float",
 *         description="Latitude of the task location"
 *     ),
 *     @OA\Property(
 *         property="longitude",
 *         type="number",
 *         format="float",
 *         description="Longitude of the task location"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Status of the task (pending, in_progress, completed, hold)"
 *     ),
 *     @OA\Property(
 *         property="dealership_id",
 *         type="integer",
 *         description="ID of the dealership associated with the task"
 *     ),
 *     @OA\Property(
 *         property="amount_to_be_collected",
 *         type="number",
 *         format="float",
 *         description="Amount needs to be collected for the task"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="ID of the user who created the task"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp of task creation"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp of last update"
 *     ),
 *     @OA\Property(
 *         property="fsr_report_id",
 *         type="integer",
 *         description="ID of the FSR report associated with the task",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="completed_time",
 *         type="integer",
 *         description="Completed time for the task in seconds"
 *     ),
 *     @OA\Property(
 *         property="task_started_time",
 *         type="string",
 *         format="date-time",
 *         description="Start time of the task",
 *         nullable=true
 *     )
 * )
 */
class TaskController extends Controller
{
    /**
     * @OA\Get(
     *     path="/tasks",
     *     summary="Get all tasks",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $array = [];
        $user = Auth::user();
        $tasks = Task::query();  // Start with a query builder, not a collection

        if ($user && $user->user_type === 'employee') {
            $employeeId = $user->employee->id;
            $userId = $user->id;

            $tasks->where(function ($query) use ($employeeId, $userId) {
                $query->where('assigned_to', $employeeId)
                    ->orWhere('tasks.user_id', $userId);
            });
        }

        // Get the tasks from the database without eager loading 'fsrReport'
        $tasks = $tasks->get();

        $tasks->transform(function ($task) {
            // Check if fsrReport exists, and get its id if it exists
            $fsrReport = $task->fsrReport()->first(); // Only fetch the first related fsrReport
            $task->fsr_report_id = $fsrReport ? $fsrReport->id : null;
            //if task->is_broker boolean convert it to 1 or 0
            $task->is_broker = $task->is_broker ? 1 : 0;
            $task->is_service = $task->is_service ? 1 : 0;
            $task->overall_elapsed_time = $task->getFormattedElapsedTime();
            $task->current_status = $task->status;
            return $task;
        });
        // var_dump($tasks);
        return response()->json($tasks);
    }


    /**
     * @OA\Get(
     *     path="/tasks/my-tasks",
     *     summary="Get tasks assigned to the authenticated employee",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employee not found"
     *     )
     * )
     */
    public function getMyTasks()
    {
        $user = Auth::user();
        if (!$user || !$user->employee) {
            return response()->json(['message' => 'Employee not found.'], 404);
        }

        $employeeId = $user->employee->id;
        $tasks = Task::with(['fsrReport', 'entry.client', 'lead'])
            ->where('assigned_to', $employeeId)
            ->get();


        $tasks->transform(function ($task) {

            // Check if fsrReport exists, and get its id if it exists
            $fsrReport = $task->fsrReport()->first(); // Only fetch the first related fsrReport
            $task->fsr_report_id = $fsrReport ? $fsrReport->id : null;
            //if task->is_broker boolean convert it to 1 or 0
            $task->is_broker = $task->is_broker ? 1 : 0;
            $task->is_service = $task->is_service ? 1 : 0;
            $task->overall_elapsed_time = $task->getFormattedElapsedTime();


            $task->timer_status = $task->taskLogs()->latest('created_at')
                ->value('action_type') ?? null;
            $task->last_updated_time = $task->taskLogs()->latest('created_at')
                ->value('start_time') ?? null;


            return $task;
        });
        return response()->json($tasks);
    }

    /**
     * @OA\Post(
     *     path="/tasks",
     *     summary="Create a new task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={
     *                 "type",
     *                 "title",
     *                 "due_date"
     *             },
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 enum={
     *                     "client_based",
     *                     "open"
     *                 },
     *                 description="Type of task"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 maxLength=255,
     *                 description="Title of the task"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 nullable=true,
     *                 description="Description of the task"
     *             ),
     *             @OA\Property(
     *                 property="assigned_to",
     *                 type="integer",
     *                 nullable=true,
     *                 description="ID of the employee assigned to the task"
     *             ),
     *             @OA\Property(
     *                 property="due_date",
     *                 type="string",
     *                 format="date",
     *                 description="Due date of the task"
     *             ),
     *             @OA\Property(
     *                 property="location",
     *                 type="string",
     *                 nullable=true,
     *                 description="Location of the task"
     *             ),
     *             @OA\Property(
     *                 property="latitude",
     *                 type="number",
     *                 format="float",
     *                 nullable=true,
     *                 description="Latitude of the task location"
     *             ),
     *             @OA\Property(
     *                 property="longitude",
     *                 type="number",
     *                 format="float",
     *                 nullable=true,
     *                 description="Longitude of the task location"
     *             ),
     *             @OA\Property(

     *                 property="status",
     *                 type="string",
     *                 enum={
     *                     "pending",
     *                     "in_progress",
     *                     "completed",
     *                     "hold"
     *                 },
     *                 nullable=true,
     *                 description="Status of the task"
     *             ),
     *             @OA\Property(
     *                 property="dealership_id",
     *                 type="integer",
     *                 nullable=true,
     *                 description="ID of the dealership associated with the task"
     *             ),
     *             @OA\Property(
     *                 property="amount_to_be_collected",
     *                 type="number",
     *                 format="float",
     *                 nullable=true,
     *                 description="Amount needs to be collected for the task"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task created successfully."),
     *             @OA\Property(property="task", ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error creating task"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
            'amount_to_be_collected' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        $validatedData['user_id'] = Auth::id(); // Set the creator's ID
        $validatedData['status'] = $validatedData['status'] ?? 'pending'; // Default status

        // Get the dealership_id from the assigned employee if not already provided
        if (!isset($validatedData['dealership_id'])) {
            if (isset($validatedData['assigned_to'])) {
                $assignedEmployee = Employee::find($validatedData['assigned_to'], ['*']);
                if ($assignedEmployee) {
                    $validatedData['dealership_id'] = $assignedEmployee->dealership_id;
                }
            } else {
                // If no employee is assigned, try to get dealership_id from the current user's employee
                $user = Auth::user();
                if ($user && $user->employee) {
                    $validatedData['dealership_id'] = $user->employee->dealership_id;
                }
            }
        }

        try {
            $task = Task::create($validatedData);

            $user = Auth::user();
            if ($user && $user->employee) {
                TaskLog::create([
                    'task_id' => $task->id,
                    'employee_id' => $user->employee->id,
                    'action_type' => 'created',
                ]);
            }

            return response()->json(['message' => 'Task created successfully.', 'task' => $task], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error creating task.', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/tasks/{task}",
     *     summary="Get a specific task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="ID of the task to retrieve",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     )
     * )
     */
    public function show(Task $task)
    {
        if ($task->type === 'client_based') {
            $task->load(['entry.client', 'entry.product', 'entry.productModel', 'entry.modelSeries', 'followups.user', 'lead', 'fsrReport']);
        } else {
            $task->load(['followups.user', 'lead', 'fsrReport']);
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
        $taskData['overall_elapsed_time'] = $task->getFormattedElapsedTime();
        $taskData['current_status'] = $task->status;
        $taskData['followups'] = $task->followups;

        // Populate client info based on whether it's a service or lead task
        if ($task->is_service && $task->entry) {
            $taskData['client'] = $task->entry->client ?? [];
            $taskData['service'] = $task->entry ?? [];
        } elseif ($task->lead) {
            $taskData['client'] = [
                'name' => $task->lead->name,
                'email' => $task->lead->email,
                'phone_number' => $task->lead->phone_number,
                'location' => $task->lead->location,
            ];
            $taskData['lead'] = $task->lead;
        } else {
            $taskData['client'] = [];
            $taskData['service'] = [];
        }

        return response()->json($taskData);
    }

    /**
     * @OA\Put(
     *     path="/tasks/{task}",
     *     summary="Update a specific task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="ID of the task to update",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 enum={
     *                     "client_based",
     *                     "open"
     *                 },
     *                 description="Type of task"
     *             ),
     *             @OA\Property(
     *                 property="title",
     *                 type="string",
     *                 maxLength=255,
     *                 description="Title of the task"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 nullable=true,
     *                 description="Description of the task"
     *             ),
     *             @OA\Property(
     *                 property="assigned_to",
     *                 type="integer",
     *                 nullable=true,
     *                 description="ID of the employee assigned to the task"
     *             ),
     *             @OA\Property(
     *                 property="due_date",
     *                 type="string",
     *                 format="date",
     *                 description="Due date of the task"
     *             ),
     *             @OA\Property(
     *                 property="location",
     *                 type="string",
     *                 nullable=true,
     *                 description="Location of the task"
     *             ),
     *             @OA\Property(
     *                 property="latitude",
     *                 type="number",
     *                 format="float",
     *                 nullable=true,
     *                 description="Latitude of the task location"
     *             ),
     *             @OA\Property(
     *                 property="longitude",
     *                 type="number",
     *                 format="float",
     *                 nullable=true,
     *                 description="Longitude of the task location"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={
     *                     "pending",
     *                     "in_progress",
     *                     "completed",
     *                     "hold"
     *                 },
     *                 nullable=true,
     *                 description="Status of the task"
     *             ),
     *             @OA\Property(
     *                 property="dealership_id",
     *                 type="integer",
     *                 nullable=true,
     *                 description="ID of the dealership associated with the task"
     *             ),
     *             @OA\Property(
     *                 property="amount_to_be_collected",
     *                 type="number",
     *                 format="float",
     *                 nullable=true,
     *                 description="Amount needs to be collected for the task"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task updated successfully."),
     *             @OA\Property(property="task", ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, Task $task)
    {
        $validator = Validator::make($request->all(), [
            // 'type' => 'required|string|in:client_based,open',
            // 'title' => 'required|string|max:255',
            // 'description' => 'nullable|string',
            // 'assigned_to' => 'nullable|exists:employees,id',
            // 'due_date' => 'required|date',
            // 'location' => 'nullable|string',
            // 'latitude' => 'nullable|numeric',
            // 'longitude' => 'nullable|numeric',
            'status' => 'nullable|string|in:pending,in_progress,completed,hold,partial',
            'amount_to_be_collected' => 'nullable|numeric|min:0',
            // 'dealership_id' => 'nullable|exists:dealerships,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        $validatedData['dealership_id'] = Auth::user()->employee->dealership_id ?? null;

        // If assigned_to is being updated, update the dealership_id
        if (isset($validatedData['assigned_to']) && $validatedData['assigned_to'] !== $task->assigned_to) {
            $assignedEmployee = Employee::find($validatedData['assigned_to'], ['*']);
            if ($assignedEmployee) {
                $validatedData['dealership_id'] = $assignedEmployee->dealership_id;
            }
        } else if (!isset($validatedData['dealership_id'])) {
            // If dealership_id is not provided in the request and assigned_to is not changed,
            // ensure it's set from the current user's employee if available.
            $user = Auth::user();
            if ($user && $user->employee) {
                $validatedData['dealership_id'] = $user->employee->dealership_id;
            }
        }

        $task->update($validatedData);

        $user = Auth::user();
        if ($user && $user->employee) {
            TaskLog::create([
                'task_id' => $task->id ?? null,
                'employee_id' => $user->employee->id ?? null,
                'action_type' => 'updated',
            ]);
        }

        return response()->json(['message' => 'Task updated successfully.', 'task' => $task]);
    }

    /**
     * @OA\Delete(
     *     path="/tasks/{task}",
     *     summary="Delete a specific task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="ID of the task to delete",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (e.g., not clocked in, or unauthorized to delete)"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     )
     * )
     */
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
                $service = \App\Models\Service::find($task->entry_id, ['*']);
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

    /**
     * @OA\Post(
     *     path="/tasks/{task}/status",
     *     summary="Update the status of a specific task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="ID of the task to update status",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={
     *                 "status"
     *             },
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={
     *                     "pending",
     *                     "in_progress",
     *                     "completed",
     *                     "hold"
     *                 },
     *                 description="New status of the task"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task status updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task status updated successfully."),
     *             @OA\Property(property="task", ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (e.g., not assigned to task, or not service engineer)"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function updateStatus(Request $request, Task $task)
    {
        $user = Auth::user();
        if (!$user || !$user->employee || $user->employee->id !== $task->assigned_to || $user->employee->role->role !== 'service_engineer') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,in_progress,completed,hold,partial',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task->status = $request->status;
        $task->save();

        if ($user->employee) {
            TaskLog::create([
                'task_id' => $task->id,
                'employee_id' => $user->employee->id,
                'action_type' => 'status_updated_to_' . $request->status,
            ]);
        }

        return response()->json(['message' => 'Task status updated successfully.', 'task' => $task]);
    }

    /**
     * @OA\Post(
     *     path="/tasks/{task}/start",
     *     summary="Start a task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="ID of the task to start",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task started successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task started successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (e.g., not assigned to task, not clocked in, or early action not approved)"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     )
     * )
     */
    public function startTask(Request $request, Task $task)
    {
        $user = Auth::user();

        // Check for 'task continuation approval' setting
        $setting = \App\Models\Setting::where('name', 'task continuation approval')->first();
        if ($setting) {
            $dealershipId = $user->employee ? $user->employee->dealership_id : null;
            $dealershipSetting = \App\Models\DealershipSetting::where('dealership_id', $dealershipId)
                ->where('setting_id', $setting->id)
                ->first();

            // If setting is enabled and user is not a service manager, deny
            if ($dealershipSetting && $dealershipSetting->enabled) {
                if (!($user->employee && $user->employee->role && ($user->employee->role->role === 'service_manager' || $user->employee->role->role === 'Service Manager'))) {
                    return response()->json(['message' => 'Task continuation approval is enabled for your dealership. Action denied.'], 403);
                }
            }
        }

        $clockedIn = false;
        if ($user && $user->employee) {
            $latestClock = Clock::where('employee_id', $user->employee->id)->latest('clock_in_time')->first();
            $clockedIn = $latestClock && is_null($latestClock->clock_out_time);
        }

        if ($user && $user->employee && $user->employee->id === $task->assigned_to && $clockedIn) {
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

            /*
            // fetch all the tasklogs of the current employee
            $taskLogs = TaskLog::where('employee_id', $user->employee->id)->get();

            //loop through each tasklog and check if there is any 'started' or 'resumed' action without an 'ended' action
            foreach ($taskLogs as $log) {
                if (in_array($log->action_type, ['started', 'resumed'])) {
                    $endedLog = TaskLog::where('employee_id', $user->employee->id)
                        ->where('task_id', $log->task_id)
                        ->whereIn('action_type', ['paused', 'stopped'])
                        ->where('created_at', '>', $log->created_at)
                        ->first();
                    if (!$endedLog) {
                        return response()->json(['message' => 'You have another task that is already started. Please pause or end that task before starting a new one.'], 403);
                    }
                }
            }
            */

            // Check if there is any other task already in progress for this employee
            if (\App\Models\Task::hasActiveTaskForEmployee($user->employee->id, $task->id)) {
                return response()->json(['message' => 'You have another task that is already started. Please pause or end that task before starting a new one.'], 403);
            }

            $task->status = 'in_progress';
            $task->startTimer(); // Call the Task model's startTimer method

            $taskLog = TaskLog::create([
                'task_id' => $task->id,
                'employee_id' => $user->employee->id,
                'action_type' => 'started',
                'start_time' => now(),
            ]);
            // return the response only if the task log is created successfully
            if ($taskLog) {
                return response()->json(['message' => 'Task started successfully.']);
            }
        }
        return response()->json(['message' => 'Unauthorized or not clocked in to start this task.'], 403);
    }

    /**
     * @OA\Post(
     *     path="/tasks/{task}/pause",
     *     summary="Pause a task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="ID of the task to pause",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task paused successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task paused successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (e.g., not assigned to task, not clocked in, or early action not approved)"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     )
     * )
     */
    public function pauseTask(Request $request, Task $task)
    {
        $user = Auth::user();

        // Check for 'task continuation approval' setting
        $setting = \App\Models\Setting::where('name', 'task continuation approval')->first();
        if ($setting) {
            $dealershipId = $user->employee ? $user->employee->dealership_id : null;
            $dealershipSetting = \App\Models\DealershipSetting::where('dealership_id', $dealershipId)
                ->where('setting_id', $setting->id)
                ->first();

            // If setting is enabled and user is not a service manager, deny
            // if ($dealershipSetting && $dealershipSetting->enabled) {
            //     if (!($user->employee && $user->employee->role && ($user->employee->role->role === 'service_manager' || $user->employee->role->role === 'Service Manager'))) {
            //         return response()->json(['message' => 'Task continuation approval is enabled for your dealership. Action denied.'], 403);
            //     }
            // }
        }

        $clockedIn = false;
        if ($user && $user->employee) {
            $latestClock = Clock::where('employee_id', $user->employee->id)->latest('clock_in_time')->first();
            $clockedIn = $latestClock && is_null($latestClock->clock_out_time);
        }

        if ($user && $user->employee && $user->employee->id === $task->assigned_to && $clockedIn) {
            // Check for Service Manager approval for early action
            // if (
            //     $task->status !== 'completed' &&
            //     $task->start_date_time && // Ensure start_date_time is not null
            //     (\Carbon\Carbon::parse($task->start_date_time)->toDateString() > now()->toDateString()) && // Check if start_date_time is strictly after today
            //     ($task->sm_approved_early_action_date === null || $task->sm_approved_early_action_date->toDateString() !== now()->toDateString())
            // ) {
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

    /**
     * @OA\Post(
     *     path="/tasks/{task}/resume",
     *     summary="Resume a paused task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="ID of the task to resume",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task resumed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task resumed successfully."),
     *             @OA\Property(property="resume_type", type="string", example="resumed"),
     *             @OA\Property(property="timer_started_at", type="string", format="date-time"),
     *             @OA\Property(property="timer_paused_at", type="string", format="date-time", nullable=true),
     *             @OA\Property(property="total_elapsed_time", type="integer"),
     *             @OA\Property(property="task_status", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (e.g., not assigned to task, not clocked in, or early action not approved)"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     )
     *
     * )
     */
    public function resumeTask(Request $request, Task $task)
    {
        $user = Auth::user();

        // Check for 'task continuation approval' setting
        $setting = \App\Models\Setting::where('name', 'task continuation approval')->first();
        if ($setting) {
            $dealershipId = $user->employee ? $user->employee->dealership_id : null;
            $dealershipSetting = \App\Models\DealershipSetting::where('dealership_id', $dealershipId)
                ->where('setting_id', $setting->id)
                ->first();

            // If setting is enabled and user is not a service manager, deny
            if ($dealershipSetting && $dealershipSetting->enabled) {
                if (!($user->employee && $user->employee->role && ($user->employee->role->role === 'service_manager' || $user->employee->role->role === 'Service Manager'))) {
                    return response()->json(['message' => 'Task continuation approval is enabled for your dealership. Action denied.'], 403);
                }
            }
        }

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


            /*
            // fetch all the tasklogs of the current employee
            $taskLogs = TaskLog::where('employee_id', $user->employee->id)->get();

            //loop through each tasklog and check if there is any 'started' or 'resumed' action without an 'ended' action
            foreach ($taskLogs as $log) {
                if (in_array($log->action_type, ['started', 'resumed'])) {
                    $endedLog = TaskLog::where('employee_id', $user->employee->id)
                        ->where('task_id', $log->task_id)
                        ->whereIn('action_type', ['paused', 'stopped'])
                        ->where('created_at', '>', $log->created_at)
                        ->first();
                    if (!$endedLog) {
                        return response()->json(['message' => 'You have another task that is already started. Please pause or end that task before starting a new one.'], 403);
                    }
                }
            }
            */

            // Check if there is any other task already in progress for this employee
            if (\App\Models\Task::hasActiveTaskForEmployee($user->employee->id, $task->id)) {
                return response()->json(['message' => 'You have another task that is already started. Please pause or end that task before starting a new one.'], 403);
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

    /**
     * @OA\Post(
     *     path="/tasks/{task}/stop",
     *     summary="Stop a task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="ID of the task to stop",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task stopped successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Task stopped successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (e.g., not assigned to task, not clocked in, or early action not approved)"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     )
     *
     * )
     */
    public function stopTask(Request $request, Task $task)
    {
        $user = Auth::user();

        // Check for 'task continuation approval' setting
        $setting = \App\Models\Setting::where('name', 'task continuation approval')->first();
        if ($setting) {
            $dealershipId = $user->employee ? $user->employee->dealership_id : null;
            $dealershipSetting = \App\Models\DealershipSetting::where('dealership_id', $dealershipId)
                ->where('setting_id', $setting->id)
                ->first();

            // If setting is enabled and user is not a service manager, deny
            // if ($dealershipSetting && $dealershipSetting->enabled) {
            //     if (!($user->employee && $user->employee->role && ($user->employee->role->role === 'service_manager' || $user->employee->role->role === 'Service Manager'))) {
            //         return response()->json(['message' => 'Task continuation approval is enabled for your dealership. Action denied.'], 403);
            //     }
            // }
        }

        $clockedIn = false;
        if ($user && $user->employee) {
            $latestClock = Clock::where('employee_id', $user->employee->id)->latest('clock_in_time')->first();
            $clockedIn = $latestClock && is_null($latestClock->clock_out_time);
        }

        if ($user && $user->employee && $user->employee->id === $task->assigned_to && $clockedIn) {
            // Check for Service Manager approval for early action
            // if (
            //     $task->status !== 'completed' &&
            //     $task->start_date_time && // Ensure start_date_time is not null
            //     (\Carbon\Carbon::parse($task->start_date_time)->toDateString() > now()->toDateString()) && // Check if start_date_time is strictly after today
            //     ($task->sm_approved_early_action_date === null || $task->sm_approved_early_action_date->toDateString() !== now()->toDateString())
            // ) {
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

    /**
     * @OA\Post(
     *     path="/tasks/{task}/approve-early-action",
     *     summary="Approve early action for a task",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="ID of the task to approve early action for",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Early action approved successfully for today.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Early action approved successfully for today.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden (e.g., not a service manager)"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     )
     * )
     */
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
}
