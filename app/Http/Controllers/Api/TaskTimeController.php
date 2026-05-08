<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class TaskTimeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/tasks/{task}/time",
     *     summary="Get the total elapsed time for a specific task",
     *     tags={"Tasks Time"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="ID of the task",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="elapsed_time_seconds", type="integer"),
     *             @OA\Property(property="formatted_elapsed_time", type="string")
     *         )
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
    public function getTaskTime(Task $task)
    {
        return response()->json([
            'elapsed_time_seconds' => $task->getElapsedTimeInSeconds(),
            'formatted_elapsed_time' => $task->getFormattedElapsedTime(),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/tasks/{task}/time-logs",
     *     summary="Get all the time logs for a specific task",
     *     tags={"Tasks Time"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="ID of the task",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TaskLog")
     *         )
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
    public function getTaskLogs(Task $task)
    {
        return response()->json($task->taskLogs()->get());
    }
}
