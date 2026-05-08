<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Clock;
use App\Models\Task;
use App\Models\UserGpsTrace;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

class GpsTraceController extends Controller
{
    /**
     * @OA\Post(
     *     path="/visits/start",
     *     summary="Record a user's GPS trace for a visit",
     *     tags={"GPS Tracing"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     title="Single Location",
     *                     type="object",
     *                     required={"latitude", "longitude"},
     *                     @OA\Property(property="latitude", type="number", format="float", example=10.1234567),
     *                     @OA\Property(property="longitude", type="number", format="float", example=76.9876543),
     *                     @OA\Property(property="customer_id", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="task_id", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="visit_id", type="integer", nullable=true, example=1),
     *                     @OA\Property(property="recorded_at", type="string", format="date-time", nullable=true, example="2023-10-27 10:00:00"),
     *                     @OA\Property(property="vehicle_type", type="string", enum={"idle", "walk", "bike", "car", "bus", "train", "other"}, nullable=true, example="bike", description="Vehicle type. Defaults to employee's current_vehicle_type if not provided.")
     *                 ),
     *                 @OA\Schema(
     *                     title="Multiple Locations",
     *                     type="object",
     *                     @OA\Property(property="locations", type="array", @OA\Items(
     *                         type="object",
     *                         required={"latitude", "longitude"},
     *                         @OA\Property(property="latitude", type="number", format="float", example=10.1234567),
     *                         @OA\Property(property="longitude", type="number", format="float", example=76.9876543),
     *                         @OA\Property(property="customer_id", type="integer", nullable=true, example=1),
     *                         @OA\Property(property="task_id", type="integer", nullable=true, example=1),
     *                         @OA\Property(property="visit_id", type="integer", nullable=true, example=1),
     *                         @OA\Property(property="recorded_at", type="string", format="date-time", nullable=true, example="2023-10-27 10:00:00"),
     *                         @OA\Property(property="vehicle_type", type="string", enum={"idle", "walk", "bike", "car", "bus", "train", "other"}, nullable=true, example="bike", description="Vehicle type. Defaults to employee's current_vehicle_type if not provided.")
     *                     ))
     *                 )
     *             }
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Visit started and GPS trace recorded successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Visit started and GPS trace recorded successfully."),
     *             @OA\Property(property="visit_id", type="integer", example=1),
     *             @OA\Property(property="count", type="integer", example=1),
     *             @OA\Property(property="is_tracking_on", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User not clocked in.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You must be clocked in to start a visit.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function startVisit(Request $request)
    {
        $user = Auth::user();

        /* -------------------------------------------------
         | 1. Ensure employee is clocked in
         |------------------------------------------------- */
        $clockedIn = false;
        if ($user && $user->employee) {
            $latestClock = Clock::where('employee_id', $user->employee->id)
                ->latest('clock_in_time')
                ->first();

            $clockedIn = $latestClock && is_null($latestClock->clock_out_time);
        }

        if (!$clockedIn) {
            return response()->json([
                'message' => 'You must be clocked in to start a visit.'
            ], 403);
        }

        /* -------------------------------------------------
         | 2. Normalize input
         |------------------------------------------------- */
        $data = $request->all();

        if (isset($data['locations']) && is_array($data['locations'])) {
            $data = $data['locations'];
        } elseif (isset($data['latitude'])) {
            $data = [$data];
        }

        if (empty($data)) {
            return response()->json(['message' => 'No GPS data provided.'], 422);
        }

        /* -------------------------------------------------
         | 3. Validate GPS payload
         |------------------------------------------------- */
        validator($data, [
            '*.latitude'    => 'required|numeric',
            '*.longitude'   => 'required|numeric',
            '*.client_id'   => 'nullable|exists:clients,id',
            '*.task_id'     => 'nullable|exists:tasks,id',
            '*.visit_id'    => 'nullable|integer',
            '*.recorded_at' => 'nullable|date',
            '*.vehicle_type' => 'nullable|string',
        ])->validate();

        /* -------------------------------------------------
         | 4. Determine visit_id
         |------------------------------------------------- */

        // Did client explicitly send a visit_id?
        $visitId = $data[0]['visit_id'] ?? null;

        if (is_null($visitId)) {

            // Get this user's latest trace
            $latestGpsTrace = UserGpsTrace::where('user_id', $user->id)
                ->latest('recorded_at')
                ->first();

            if ($latestGpsTrace && $latestGpsTrace->status === 'active') {
                // ✅ Continue SAME visit for THIS user
                $visitId = $latestGpsTrace->visit_id;
            } else {
                // ✅ NEW GLOBAL visit_id (across ALL users)
                $visitId = (UserGpsTrace::max('visit_id') ?? 0) + 1;
            }
        } else {
            // Validate usage of provided visit_id, prevent using others' visit_id
            $isUsedByOther = UserGpsTrace::where('visit_id', $visitId)
                ->where('user_id', '!=', $user->id)
                ->exists();

            if ($isUsedByOther) {
                return response()->json(['message' => 'This visit ID is already in use by another user.'], 403);
            }
        }

        /* -------------------------------------------------
         | 5. Store GPS traces
         |------------------------------------------------- */
        DB::transaction(function () use ($data, $user, $visitId) {
            $currentVehicleType = $user->employee->current_vehicle_type ?? null;

            foreach ($data as $trace) {
                UserGpsTrace::create([
                    'user_id'     => $user->id,
                    'visit_id'    => $visitId,
                    'client_id'   => $trace['customer_id'] ?? null,
                    'task_id'     => $trace['task_id'] ?? null,
                    'latitude'    => $trace['latitude'],
                    'longitude'   => $trace['longitude'],
                    'recorded_at' => $trace['recorded_at'] ?? now(),
                    'status'      => 'active',
                    'vehicle_type' => $currentVehicleType,
                ]);
            }
        });

        /* -------------------------------------------------
         | 6. Mark employee as tracking
         |------------------------------------------------- */
        if ($user->employee) {
            $user->employee->update(['is_tracking_on' => true]);
        }

        return response()->json([
            'message' => 'Visit started and GPS trace recorded successfully.',
            'visit_id' => $visitId,
            'count' => count($data),
            'is_tracking_on' => $user->employee->is_tracking_on ?? false,
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/visits/mark",
     *     summary="Mark a visit with client, task, image, and remarks",
     *     tags={"GPS Tracing"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"image"},
     *                 @OA\Property(property="customer_id", type="integer", nullable=true, example=1),
     *                 @OA\Property(property="task_id", type="integer", nullable=true, example=1),
     *                 @OA\Property(property="visit_id", type="integer", nullable=true, description="ID of the visit to mark as inactive"),
     *                 @OA\Property(property="image", type="string", format="binary", description="Image file of the visit"),
     *                 @OA\Property(property="remarks", type="string", nullable=true, description="Remarks about the visit"),
     *                 @OA\Property(property="latitude", type="number", format="float", nullable=true, description="Latitude from image metadata"),
     *                 @OA\Property(property="longitude", type="number", format="float", nullable=true, description="Longitude from image metadata"),
     *                 @OA\Property(property="vehicle_type", type="string", enum={"idle", "walk", "bike", "car", "bus", "train", "other"}, nullable=true, example="car", description="Vehicle type. Defaults to employee's current_vehicle_type if not provided.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Visit marked successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Visit marked successfully."),
     *             @OA\Property(property="is_tracking_on", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden - User not clocked in.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="You must be clocked in to mark a visit.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function markVisit(Request $request)
    {
        $user = Auth::user();

        // 1. Validate Clock-in
        $clockedIn = false;
        $latestClock = null;
        if ($user && $user->employee) {
            $latestClock = Clock::where('employee_id', $user->employee->id)->latest('clock_in_time')->first();
            $clockedIn = $latestClock && is_null($latestClock->clock_out_time);
        }

        if (!$clockedIn) {
            return response()->json(['message' => 'You must be clocked in to mark a visit.'], 403);
        }

        // 2. Validate request data
        $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'task_id' => 'nullable|exists:tasks,id',
            'visit_id' => 'nullable|integer',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'remarks' => 'nullable|string|max:1000',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'vehicle_type' => 'nullable|string',
        ]);

        // 3. Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            // Ensure the directory exists
            Storage::disk('public')->makeDirectory('visit_images');
            $imagePath = $request->file('image')->store('visit_images', 'public');
        }

        // 5. Determine visit_id and location
        $visitId = $request->visit_id;

        if (!is_null($visitId)) {
            $isUsedByOther = UserGpsTrace::where('visit_id', $visitId)
                ->where('user_id', '!=', $user->id)
                ->exists();

            if ($isUsedByOther) {
                return response()->json(['message' => 'This visit ID is already in use by another user.'], 403);
            }
        }
        $latestGpsTrace = UserGpsTrace::where('user_id', $user->id)
            ->latest('recorded_at')
            ->first();

        if (is_null($visitId)) {
            $visitId = $latestGpsTrace ? $latestGpsTrace->visit_id : null;
        }

        // Determine Location: Use provided, or fallback to latest trace, or fallback to clock-in location
        $lat = $request->latitude ?? ($latestGpsTrace ? $latestGpsTrace->latitude : ($latestClock ? $latestClock->clock_in_latitude : 0));
        $lng = $request->longitude ?? ($latestGpsTrace ? $latestGpsTrace->longitude : ($latestClock ? $latestClock->clock_in_longitude : 0));

        // If a visitId is determined, mark all active traces for this visitId as inactive
        if ($visitId) {
            UserGpsTrace::where('user_id', $user->id)
                ->where('visit_id', $visitId)
                ->where('status', 'active')
                ->update(['status' => 'inactive']);
        }

        // Create a new GPS Trace record for the closing event
        // Create a new GPS Trace record for the closing event
        UserGpsTrace::create([
            'visit_id' => $visitId,
            'user_id' => $user->id,
            'client_id' => $request->customer_id ?? null,
            'task_id' => $request->task_id ?? null,
            'status' => 'inactive', // This new record is also inactive
            'recorded_at' => now(),
            'image_path' => $imagePath,
            'remarks' => $request->remarks ?? null,
            'latitude' => $lat, // This remains as the "location of the visit mark event"
            'longitude' => $lng,
            'image_latitude' => $request->latitude ?? null, // Explicit image metadata
            'image_longitude' => $request->longitude ?? null,
            'vehicle_type' => $user->employee->current_vehicle_type ?? null,
        ]);

        // Update employee's is_tracking_on status
        if ($user->employee) {
            $user->employee->update(['is_tracking_on' => false]);
        }

        return response()->json(['message' => 'Visit marked successfully.', 'is_tracking_on' => $user->employee->is_tracking_on], 200);
    }

    /**
     * @OA\Get(
     *     path="/tasks/{task}/gps-traces",
     *     summary="Get GPS traces for a specific task",
     *     tags={"GPS Tracing"},
     *     security={{"bearerAuth": {}}},
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
     *             @OA\Items(ref="#/components/schemas/UserGpsTrace")
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
    public function getGpsTracesByTaskId(Task $task)
    {
        $gpsTraces = UserGpsTrace::where('task_id', $task->id)->get();
        return response()->json($gpsTraces);
    }

    /**
     * @OA\Get(
     *     path="/users/{userId}/gps-trace-status",
     *     summary="Get the status of the latest GPS trace for a specific user",
     *     tags={"GPS Tracing"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="ID of the user",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="active"),
     *             @OA\Property(property="message", type="string", example="Latest GPS trace status retrieved successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User or GPS trace not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Latest GPS trace not found for this user.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function getLatestGpsTraceStatus(Request $request, $userId)
    {
        $latestGpsTrace = UserGpsTrace::where('user_id', $userId)
            ->latest('recorded_at')
            ->first();

        if (!$latestGpsTrace) {
            return response()->json(['message' => 'Latest GPS trace not found for this user.'], 404);
        }

        return response()->json([
            'status' => $latestGpsTrace->status,
            'message' => 'Latest GPS trace status retrieved successfully.'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/user/tracking-status",
     *     summary="Get the current user's GPS tracking status (is_tracking_on)",
     *     tags={"GPS Tracing"},
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="is_tracking_on", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User tracking status retrieved successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Employee record not found for the user."
     *     )
     * )
     */
    public function getUserTrackingStatus(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->employee) {
            return response()->json(['message' => 'Employee record not found for the user.'], 404);
        }

        return response()->json([
            'is_tracking_on' => (bool) $user->employee->is_tracking_on,
            'message' => 'User tracking status retrieved successfully.'
        ]);
    }
    /**
     * @OA\Post(
     *     path="/visits/halt",
     *     summary="Record a halt point in the visit route map",
     *     tags={"GPS Tracing"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"latitude", "longitude"},
     *                 @OA\Property(property="latitude", type="number", format="float", example=10.1234567),
     *                 @OA\Property(property="longitude", type="number", format="float", example=76.9876543),
     *                 @OA\Property(property="remarks", type="string", nullable=true, example="Tea Break"),
     *                 @OA\Property(property="task_id", type="integer", nullable=true, example=1),
     *                 @OA\Property(property="customer_id", type="integer", nullable=true, example=1),
     *                 @OA\Property(property="image", type="string", format="binary", description="Image file of the halt"),
     *                 @OA\Property(property="vehicle_type", type="string", enum={"idle", "walk", "bike", "car", "bus", "train", "other"}, nullable=true, example="bike", description="Vehicle type. Defaults to employee's current_vehicle_type if not provided.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Halt recorded successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Halt recorded successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error."
     *     )
     * )
     */
    public function haltVisit(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
            'remarks'     => 'nullable|string',
            'task_id'     => 'nullable|exists:tasks,id',
            'client_id'   => 'nullable|exists:clients,id',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
            'vehicle_type' => 'nullable|string',
        ]);

        // Handle image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            Storage::disk('public')->makeDirectory('visit_images');
            $imagePath = $request->file('image')->store('visit_images', 'public');
        }

        // Automatically fetch the latest visit_id
        $latestGpsTrace = UserGpsTrace::where('user_id', $user->id)
            ->latest('recorded_at')
            ->first();

        $visitId = $latestGpsTrace ? $latestGpsTrace->visit_id : null;

        UserGpsTrace::create([
            'user_id'     => $user->id,
            'visit_id'    => $visitId,
            'latitude'    => $request->latitude,
            'longitude'   => $request->longitude,
            'remarks'     => $request->remarks,
            'task_id'     => $request->task_id,
            'client_id'   => $request->customer_id,
            'image_path'  => $imagePath,
            'status'      => 'halt',
            'recorded_at' => now(),
            'vehicle_type' => $user->employee->current_vehicle_type ?? null,
        ]);

        return response()->json(['message' => 'Halt recorded successfully.'], 200);
    }
    /**
     * @OA\Post(
     *     path="/visits/report-location",
     *     summary="Report a location trace (e.g. for error logging or background updates)",
     *     tags={"GPS Tracing"},
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"latitude", "longitude"},
     *             @OA\Property(property="latitude", type="number", format="float", example=10.1234567),
     *             @OA\Property(property="longitude", type="number", format="float", example=76.9876543),
     *             @OA\Property(property="remarks", type="string", nullable=true, example="Error occurred during sync")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Location reported successfully.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Location reported successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error.",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function reportLocation(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
            'remarks'     => 'nullable|string',
        ]);

        // Automatically fetch the latest visit_id
        $latestGpsTrace = UserGpsTrace::where('user_id', $user->id)
            ->latest('recorded_at')
            ->first();

        $visitId = $latestGpsTrace ? $latestGpsTrace->visit_id : null;

        \App\Models\LocationReport::create([
            'user_id'     => $user->id,
            'visit_id'    => $visitId,
            'latitude'    => $request->latitude,
            'longitude'   => $request->longitude,
            'remarks'     => $request->remarks,
        ]);

        return response()->json(['message' => 'Location reported successfully.'], 200);
    }
}
