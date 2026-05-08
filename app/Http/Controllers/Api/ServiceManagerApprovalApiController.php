<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Service Manager Approvals",
 *     description="API Endpoints for Service Manager Approvals"
 * )
 */
class ServiceManagerApprovalApiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/service-manager/approvals",
     *     summary="Get tasks for approval",
     *     tags={"Service Manager Approvals"},
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
     *         response=403,
     *         description="Forbidden"
     *     )
     * )
     */
    public function getTasksForApproval(Request $request)
    {
        $user = Auth::user();
        $userDealershipId = null;

        if ($user->employee) {
            $userDealershipId = $user->employee->dealership_id;
        }

        // Authorization: Only service managers can access this page
        if (!$user || !$user->employee || !$user->employee->role || ($user->employee->role->role !== 'service_manager' && $user->employee->role->role !== 'Service Manager')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $tasks = Task::with('assignedEmployee')
            ->whereNotIn('status', ['completed'])
            ->where(function ($query) {
                $query->whereNotNull('start_date_time')
                    ->orWhereDate('start_date_time', '<', today());
            })
            ->where(function ($query) {
                $query->whereNull('sm_approved_early_action_date')
                    ->orWhereDate('sm_approved_early_action_date', '<', today());
            });

        if ($userDealershipId) {
            $tasks->where('dealership_id', $userDealershipId);
        }

        $tasks = $tasks->get();

        $tasks->transform(function ($task) {
            $task->requires_approval = ($task->status !== 'completed' && $task->start_date_time && $task->start_date_time->toDateString() < now()->toDateString());
            return $task;
        });

        return response()->json($tasks);
    }

    /**
     * @OA\Post(
     *     path="/api/service-manager/approvals/{task}",
     *     summary="Approve a task for early action",
     *     tags={"Service Manager Approvals"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="ID of the task to approve",
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
     *         description="Forbidden"
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
        if (!$user || !$user->employee || !$user->employee->role || ($user->employee->role->role !== 'service_manager' && $user->employee->role->role !== 'Service Manager')) {
            return response()->json(['message' => 'Unauthorized to approve early action.'], 403);
        }

        // Update the task with the current date as the approval date
        $task->sm_approved_early_action_date = now()->toDateString();
        $task->save();

        // Log the action
        if ($user->employee) {
            \App\Models\TaskLog::create([
                'task_id' => $task->id,
                'employee_id' => $user->employee->id,
                'action_type' => 'sm_approved_early_action',
                'notes' => 'Service Manager approved early action for ' . now()->toDateString(),
            ]);
        }

        return response()->json(['message' => 'Early action approved successfully for today.'], 200);
    }
}
