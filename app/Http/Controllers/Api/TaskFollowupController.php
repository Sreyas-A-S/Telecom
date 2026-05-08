<?php

namespace App\Http\Controllers\Api;

/**
 * @OA\Schema(
 *     schema="TaskFollowup",
 *     type="object",
 *     title="TaskFollowup",
 *     @OA\Property(property="id", type="integer", readOnly=true, example=1),
 *     @OA\Property(property="task_id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="notes", type="string", example="This is a follow-up note."),
 *     @OA\Property(property="images", type="array", @OA\Items(type="string")),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly=true, example="2021-08-19T12:00:00.000000Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly=true, example="2021-08-19T12:00:00.000000Z"),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 * )
 */

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskFollowup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class TaskFollowupController extends Controller
{
    /**
     * @OA\Get(
     *     path="/tasks/{task}/followups",
     *     summary="Get all follow-ups for a task",
     *     tags={"Task Follow-ups"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="ID of the task",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/TaskFollowup"))
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index(Request $request, Task $task)
    {
        $task->load(['followups.user']);
        $followups = $task->followups->map(function ($followup) {
            $followup->user_name = $followup->user ? $followup->user->name : null;
            return $followup;
        });
        return response()->json($followups);
    }

    /**
     * @OA\Post(
     *     path="/tasks/{task}/followups",
     *     summary="Create a new follow-up for a task",
     *     tags={"Task Follow-ups"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="ID of the task",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"notes"},
     *                 @OA\Property(
     *                     property="notes",
     *                     type="string",
     *                     description="Notes for the follow-up"
     *                 ),
     *                 @OA\Property(
     *                     property="images[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Images for the follow-up"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Follow-up created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="followup", ref="#/components/schemas/TaskFollowup")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request, Task $task)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'required|string',
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $image->getClientOriginalName());
                
                // Ensure public storage directory exists
                if (!File::exists(public_path('storage/task_followups'))) {
                    File::makeDirectory(public_path('storage/task_followups'), 0777, true, true);
                }

                // 1. Store to storage 'public' disk (storage/app/public/task_followups)
                $path = Storage::disk('public')->putFileAs('task_followups', $image, $imageName);

                // 2. Exact duplicate at public/storage/task_followups
                File::copy(storage_path('app/public/' . $path), public_path('storage/' . $path));

                $imagePaths[] = 'storage/' . $path;
            }
        }


        $followup = TaskFollowup::create([
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'notes' => $request->notes,
            'images' => $imagePaths,
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
        ]);

        $followup->load('user');

        return response()->json(['message' => 'Follow-up added successfully.', 'followup' => $followup], 201);
    }

    /**
     * @OA\Get(
     *     path="/tasks/{task}/followups/{followup}",
     *     summary="Get a specific follow-up for a task",
     *     tags={"Task Follow-ups"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="ID of the task",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="followup",
     *         in="path",
     *         required=true,
     *         description="ID of the follow-up",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TaskFollowup")
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
     *         description="Not Found"
     *     )
     * )
     */
    public function edit(Task $task, TaskFollowup $followup)
    {
        $user = Auth::user();
        $isCreator = ($user->id === $followup->user_id);
        $isServiceManager = ($user->employee && $user->employee->role && ($user->employee->role->role === 'service_manager'  || $user->employee->role->role === 'Service Manager'));
        $isNonServiceTask = ($followup->task->type !== 'service');

        if (!($isCreator || ($isServiceManager && $isNonServiceTask))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($followup);
    }

    /**
     * @OA\Put(
     *     path="/tasks/{task}/followups/{followup}",
     *     summary="Update a specific follow-up for a task",
     *     tags={"Task Follow-ups"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="ID of the task",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="followup",
     *         in="path",
     *         required=true,
     *         description="ID of the follow-up",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"notes"},
     *                 @OA\Property(
     *                     property="notes",
     *                     type="string",
     *                     description="Notes for the follow-up"
     *                 ),
     *                 @OA\Property(
     *                     property="images",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Images for the follow-up"
     *                 ),
     *                 @OA\Property(
     *                     property="remove_images",
     *                     type="array",
     *                     @OA\Items(type="string"),
     *                     description="Images to remove"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Follow-up updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="followup", ref="#/components/schemas/TaskFollowup")
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
     *         description="Not Found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, Task $task, TaskFollowup $followup)
    {
        $user = Auth::user();
        $isCreator = ($user->id === $followup->user_id);
        $isServiceManager = ($user->employee && $user->employee->role && ($user->employee->role->role === 'service_manager'  || $user->employee->role->role === 'Service Manager'));
        $isNonServiceTask = ($followup->task->type !== 'service');

        if (!($isCreator || ($isServiceManager && $isNonServiceTask))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'notes' => 'required|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'remove_images' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $currentImages = $followup->images;
        if (is_string($currentImages)) {
            $decoded = json_decode($currentImages, true);
            $currentImages = is_array($decoded) ? $decoded : [];
        }

        if (is_array($currentImages) && $request->has('remove_images')) {
            foreach ($request->remove_images as $removeImg) {
                foreach ($currentImages as $key => $img) {
                    if ($img === $removeImg) {
                        $full = public_path($img);
                        if (File::exists($full)) {
                            File::delete($full);
                        }
                        unset($currentImages[$key]);
                    }
                }
            }
            $currentImages = array_values($currentImages);
        }

        $newImagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $image->getClientOriginalName());
                
                // Ensure public storage directory exists
                if (!File::exists(public_path('storage/task_followups'))) {
                    File::makeDirectory(public_path('storage/task_followups'), 0777, true, true);
                }

                // 1. Store to storage 'public' disk (storage/app/public/task_followups)
                $path = Storage::disk('public')->putFileAs('task_followups', $image, $imageName);

                // 2. Exact duplicate at public/storage/task_followups
                File::copy(storage_path('app/public/' . $path), public_path('storage/' . $path));

                $newImagePaths[] = 'storage/' . $path;
            }
        }

        $updatedImages = array_values(array_merge($currentImages, $newImagePaths));

        $followup->update([
            'notes' => $request->notes,
            'images' => $updatedImages,
        ]);

        return response()->json(['message' => 'Follow-up updated successfully.', 'followup' => $followup]);
    }

    /**
     * @OA\Delete(
     *     path="/tasks/{task}/followups/{followup}",
     *     summary="Delete a specific follow-up for a task",
     *     tags={"Task Follow-ups"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="task",
     *         in="path",
     *         required=true,
     *         description="ID of the task",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="followup",
     *         in="path",
     *         required=true,
     *         description="ID of the follow-up",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Follow-up deleted successfully",
     *         @OA\JsonContent(@OA\Property(property="message", type="string"))
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
     *         description="Not Found"
     *     )
     * )
     */
    public function destroy(Task $task, TaskFollowup $followup)
    {
        $user = Auth::user();
        $isCreator = ($user->id === $followup->user_id);
        $isServiceManager = ($user->employee && $user->employee->role && ($user->employee->role->role === 'service_manager'  || $user->employee->role->role === 'Service Manager'));
        $isNonServiceTask = ($followup->task->type !== 'service');

        if (!($isCreator || ($isServiceManager && $isNonServiceTask))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $images = $followup->images;
        if (is_string($images)) {
            $decoded = json_decode($images, true);
            $images = is_array($decoded) ? $decoded : [];
        }
        if (is_array($images)) {
            foreach ($images as $img) {
                $full = public_path($img);
                if (File::exists($full)) {
                    File::delete($full);
                }
            }
        }

        $followup->delete();

        return response()->json(['message' => 'Follow-up deleted successfully.']);
    }
}
