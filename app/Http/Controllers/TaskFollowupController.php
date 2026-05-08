<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Employee;
use App\Models\UserGpsTrace;
use App\Models\TaskFollowup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class TaskFollowupController extends Controller
{

    public function index(Task $task)
    {
        $task->load(['followups.user', 'taskLogs.employee']);

        // Calculate task analytics
        $totalSeconds = $task->getElapsedTimeInSeconds();
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $totalTime = "$hours hrs $minutes mins";

        $taskLogs = $task->taskLogs()->orderBy('created_at', 'desc')->get();

        return view('tasks.followups.index', compact('task', 'totalTime', 'taskLogs'));
    }

    public function update(Request $request, $taskId, $followupId)
    {
        $task = Task::findOrFail($taskId);
        $followup = TaskFollowup::where('task_id', $task->id)->findOrFail($followupId);

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

        // Handle removal of images
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
            // reindex
            $currentImages = array_values($currentImages);
        }

        // Handle newly uploaded images
        $newImagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', $image->getClientOriginalName());
                $image->move(public_path('uploads/task_followups'), $imageName);
                $newImagePaths[] = 'uploads/task_followups/' . $imageName;
            }
        }

        // Merge remaining current images with new ones
        $updatedImages = array_values(array_merge($currentImages, $newImagePaths));

        $followup->update([
            'notes' => $request->notes,
            'images' => $updatedImages,
        ]);

        return response()->json(['message' => 'Follow-up updated successfully.', 'followup' => $followup]);
    }

    public function getDataTableData(Request $request, Task $task)
    {
        $query = TaskFollowup::where('task_id', $task->id)->with('user', 'task');

        // Apply date filters
        if ($request->has('start_date') && $request->input('start_date') != null) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->has('end_date') && $request->input('end_date') != null) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        return DataTables::eloquent($query)
            ->addColumn('user_name', function (TaskFollowup $followup) {
                return $followup->user->name;
            })
            ->editColumn('created_at', function (TaskFollowup $followup) {
                return $followup->created_at->format('d M Y, h:i a');
            })
            ->addColumn('images_column', function (TaskFollowup $followup) {
                if (!empty($followup->images) && is_array($followup->images)) {
                    $html = '<div class="d-flex flex-wrap gap-1">';
                    foreach ($followup->images as $img) {
                        $url = asset($img);
                        $html .= '<a href="' . $url . '" data-lightbox="followup-' . $followup->id . '" data-title="Follow-up Image">';
                        $html .= '<img src="' . $url . '" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">';
                        $html .= '</a>';
                    }
                    $html .= '</div>';
                    return $html;
                }
                return '';
            })
            ->addColumn('action', function (TaskFollowup $followup) {
                $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                if (Auth::id() === $followup->user_id) {
                    $btn .= '<li class="edit"><a title="Edit" href="javascript:void(0)" data-id="' . $followup->id . '" class="edit-followup-btn"><i class="icon-pencil"></i></a></li>';
                    $btn .= '<li class="delete"><a title="Delete" href="javascript:void(0)" data-id="' . $followup->id . '" class="delete-followup-btn"><i class="icon-trash"></i></a></li>';
                }
                $btn .= '</ul>';
                return $btn;
            })
            ->rawColumns(['user_name', 'images_column', 'action'])
            ->make(true);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'task_id' => 'required|exists:tasks,id',
                'notes' => 'required|string',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120', // Validate each image
            ]);
            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $imagePaths = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imageName = time() . '_' . $image->getClientOriginalName();
                    $image->move(public_path('uploads/task_followups'), $imageName);
                    $imagePaths[] = 'uploads/task_followups/' . $imageName;
                }
            }

            $followup = TaskFollowup::create([
                'task_id' => $request->task_id,
                'user_id' => Auth::id(),
                'notes' => $request->notes,
                'images' => $imagePaths, // Save image paths as JSON
            ]);

            return response()->json(['message' => 'Follow-up added successfully.', 'followup' => $followup], 201);
        } catch (\Exception $e) {
            Log::error('Error storing task followup: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    public function getGpsData(Request $request, $employeeId)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $employee = Employee::find($employeeId);
        if (!$employee || !$employee->user_id) {
            return response()->json(['gpsTraces' => [], 'followups' => []]);
        }
        $userId = $employee->user_id;

        Log::info('getGpsData', [
            'employeeId' => $employeeId,
            'userId' => $userId,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);

        $gpsTraces = UserGpsTrace::where('user_id', $userId)
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('recorded_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('recorded_at', '<=', $endDate);
            })
            ->orderBy('recorded_at')
            ->get();

        // Fetch clock-in/clock-out times for the given user and date range
        $clockRecords = \App\Models\Clock::where('employee_id', $employeeId)
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('clock_in_time', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('clock_in_time', '<=', $endDate);
            })
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

        $followups = TaskFollowup::where('user_id', $userId)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->get();

        return response()->json([
            'gpsTraces' => $filteredGpsTraces->values()->all(),
            'followups' => $followups,
        ]);
    }

    public function edit($taskId, $followupId)
    {
        $task = Task::findOrFail($taskId); // Manually find the task
        $followup = TaskFollowup::where('task_id', $task->id)->findOrFail($followupId); // Find followup scoped to task

        $user = Auth::user();
        $isCreator = ($user->id === $followup->user_id);
        $isServiceManager = ($user->employee && $user->employee->role && ($user->employee->role->role === 'service_manager'  || $user->employee->role->role === 'Service Manager'));
        $isNonServiceTask = ($followup->task->type !== 'service'); // Assuming 'service' is the value for service type tasks

        if (!($isCreator || ($isServiceManager && $isNonServiceTask))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($followup);
    }

    public function destroy($taskId, $followupId)
    {
        $task = Task::findOrFail($taskId); // Manually find the task
        $followup = TaskFollowup::where('task_id', $task->id)->findOrFail($followupId); // Find followup scoped to task

        $user = Auth::user();
        $isCreator = ($user->id === $followup->user_id);
        $isServiceManager = ($user->employee && $user->employee->role && ($user->employee->role->role === 'service_manager'  || $user->employee->role->role === 'Service Manager'));
        $isNonServiceTask = ($followup->task->type !== 'service'); // Assuming 'service' is the value for service type tasks

        if (!($isCreator || ($isServiceManager && $isNonServiceTask))) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // delete any images from disk
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
