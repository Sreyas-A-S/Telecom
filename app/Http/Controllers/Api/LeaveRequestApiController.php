<?php

namespace App\Http\Controllers\Api;

use OpenApi\Annotations as OA;

use App\Http\Controllers\Api\Controller;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Notification;

/**
 * @OA\Schema(
 *     schema="LeaveRequest",
 *     title="LeaveRequest",
 *     description="Leave Request model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the leave request"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the user who made the request"
 *     ),
 *     @OA\Property(
 *         property="leave_type",
 *         type="string",
 *         description="Type of leave (e.g., casual, sick, paid, unpaid, compensatory)"
 *     ),
 *     @OA\Property(
 *         property="start_date",
 *         type="string",
 *         format="date",
 *         description="Start date of the leave"
 *     ),
 *     @OA\Property(
 *         property="end_date",
 *         type="string",
 *         format="date",
 *         nullable=true,
 *         description="End date of the leave"
 *     ),
 *     @OA\Property(
 *         property="reason",
 *         type="string",
 *         description="Reason for the leave"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Status of the leave request (pending, approved, rejected, cancelled, cancelled by admin, approved and forwarded)"
 *     ),
 *     @OA\Property(
 *         property="attachment",
 *         type="string",
 *         nullable=true,
 *         description="Path to the attachment"
 *     ),
 *     @OA\Property(
 *         property="is_compensatory",
 *         type="boolean",
 *         description="Is the leave compensatory"
 *     ),
 *     @OA\Property(
 *         property="compensatory_date",
 *         type="string",
 *         format="date",
 *         nullable=true,
 *         description="Date of the compensatory leave"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Creation timestamp"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Last update timestamp"
 *     )
 * )
 *
 * @OA\Schema(
 *     schema="LeaveRequestPaginatedResponse",
 *     title="LeaveRequestPaginatedResponse",
 *     description="Paginated list of leave requests",
 *     @OA\Property(property="current_page", type="integer"),
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/LeaveRequest")),
 *     @OA\Property(property="first_page_url", type="string"),
 *     @OA\Property(property="from", type="integer"),
 *     @OA\Property(property="last_page", type="integer"),
 *     @OA\Property(property="last_page_url", type="string"),
 *     @OA\Property(property="next_page_url", type="string", nullable=true),
 *     @OA\Property(property="path", type="string"),
 *     @OA\Property(property="per_page", type="integer"),
 *     @OA\Property(property="prev_page_url", type="string", nullable=true),
 *     @OA\Property(property="to", type="integer"),
 *     @OA\Property(property="total", type="integer"),
 * )
 */


/**
 * @OA\Tag(
 *     name="Leave Requests",
 *     description="API Endpoints for Leave Requests"
 * )
 */
class LeaveRequestApiController extends Controller
/**
 * @OA\Get(
 *      path="/leave-requests",
 *      operationId="getLeaveRequestsList",
 *      tags={"Leave Requests"},
 *      summary="Get list of leave requests",
 *      description="Returns list of leave requests",
 *      @OA\Parameter(
 *          name="my_requests",
 *          description="Filter by current user's requests",
 *          in="query",
 *          @OA\Schema(type="boolean")
 *      ),
 *      @OA\Parameter(
 *          name="employee_id",
 *          description="Filter by employee ID",
 *          in="query",
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\Parameter(
 *          name="leave_type",
 *          description="Filter by leave type",
 *          in="query",
 *          @OA\Schema(type="string")
 *      ),
 *      @OA\Parameter(
 *          name="status",
 *          description="Filter by status",
 *          in="query",
 *          @OA\Schema(type="string")
 *      ),
 *      @OA\Parameter(
 *          name="start_date",
 *          description="Filter by start date (YYYY-MM-DD)",
 *          in="query",
 *          @OA\Schema(type="string", format="date")
 *      ),
 *      @OA\Parameter(
 *          name="end_date",
 *          description="Filter by end date (YYYY-MM-DD)",
 *          in="query",
 *          @OA\Schema(type="string", format="date")
 *      ),
 *      @OA\Parameter(
 *          name="page",
 *          description="The page number to retrieve.",
 *          in="query",
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *          @OA\JsonContent(ref="#/components/schemas/LeaveRequestPaginatedResponse")
 *      ),
 *      @OA\Response(
 *          response=499,
 *          description="Unauthenticated",
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden"
 *      ),
 *      security={{"bearerAuth":{}}}
 * )
 */
{
    public function index(Request $request)
    {
        // Eager-load the requesting user and forwarded employee relationships
        $query = LeaveRequest::with(['user', 'forwardedToEmployee.employee.department']);

        $user = Auth::user();


        if ($request->my_requests == 'true') {
            $query->where('user_id', Auth::id());

            //dd the query to see the SQL and user id
            // dd($query->toSql(), $user->employee->id);
        } else {
            if (Auth::user()->employee) {

                $currentEmployeeId = Auth::id();
                $currentUserId = Auth::user()->employee->user_id;
                $reportingEmployeeUserIds = Employee::where('reporting_to', $currentEmployeeId)->pluck('user_id');
                $query->where(function ($q) use ($reportingEmployeeUserIds, $currentEmployeeId, $currentUserId) {
                    $q->whereIn('user_id', $reportingEmployeeUserIds)
                        ->orWhere('forwarded_to_employee_id', $currentUserId);
                });
            } elseif (Auth::user()->user_type !== 'admin') {
                $query->where('user_id', Auth::id());
            }
        }


        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }

        if ($request->filled('leave_type')) {
            $query->where('leave_type', $request->leave_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('end_date', '<=', $request->end_date);
        }



        $leaveRequests = $query->paginate(10);


        $collection = $leaveRequests->getCollection()->map(function ($item) {
            $arr = $item->toArray();


            $requestingUserName = null;
            if ($item->user) {
                $requestingUserName = $item->user->employee->name ?? $item->user->name ?? null;
            }
            $arr['username'] = $requestingUserName;

            $forwardedName = null;
            if ($item->forwardedToEmployee) {

                $forwardedName = $item->forwardedToEmployee->employee->name ?? $item->forwardedToEmployee->name ?? null;
            }
            $arr['forwarded_to'] = $forwardedName;

            unset($arr['user']);
            unset($arr['forwardedToEmployee']);
            unset($arr['forwarded_to_employee']);

            return $arr;
        });
        $leaveRequests->setCollection($collection);

        return response()->json([
            'current_page' => $leaveRequests->currentPage(),
            'data' => $leaveRequests->items(),
            'first_page_url' => $leaveRequests->url(1),
            'from' => $leaveRequests->firstItem(),
            'last_page' => $leaveRequests->lastPage(),
            'last_page_url' => $leaveRequests->url($leaveRequests->lastPage()),
            'next_page_url' => $leaveRequests->nextPageUrl(),
            'path' => $leaveRequests->path(),
            'per_page' => $leaveRequests->perPage(),
            'prev_page_url' => $leaveRequests->previousPageUrl(),
            'to' => $leaveRequests->lastItem(),
            'total' => $leaveRequests->total(),

        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *      path="/leave-requests",
     *      operationId="storeLeaveRequest",
     *      tags={"Leave Requests"},
     *      summary="Store new leave request",
     *      description="Stores a new leave request and returns the created leave request. Note: If the user has insufficient leave balance or less than 1 year of service, the 'leave_type' will automatically be converted to 'unpaid'.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"leave_type", "start_date", "duration"},
     *                  @OA\Property(property="leave_type", type="string", enum={"casual", "sick", "paid", "unpaid", "compensatory"}, example="casual"),
     *                  @OA\Property(property="start_date", type="string", format="date", example="2025-12-25"),
     *                  @OA\Property(property="end_date", type="string", format="date", nullable=true, example="2025-12-26"),
     *                  @OA\Property(property="reason", type="string", example="Vacation"),
     *                  @OA\Property(property="comp_leave_date", type="string", format="date", nullable=true, example="2025-12-20", description="Required if leave_type is compensatory"),
     *                  @OA\Property(property="duration", type="string", example="full_day"),
     *                  @OA\Property(property="is_compensatory", type="boolean", example=true),
     *                  @OA\Property(property="compensatory_date", type="string", format="date", nullable=true, example="2025-12-27"),
     *                  @OA\Property(property="forwarded_to_employee_id", type="integer", nullable=true, example=123, description="ID of the employee to forward the request to"),
     *                  @OA\Property(property="attachment", type="string", format="binary", nullable=true, description="Allowed types: jpg, jpeg, png, pdf")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/LeaveRequest")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      security={{"bearerAuth":{}}}
     * )
     */
    public function store(Request $request)
    {
        Log::info('LeaveRequestApiController@store: Received new leave request.');

        if ($request->duration === 'multiple' && is_string($request->start_date)) {
            $dates = explode(' - ', $request->start_date);
            if (count($dates) === 2) {
                $request->merge([
                    'start_date' => $dates[0],
                    'end_date' => $dates[1],
                ]);
            }
        }

        $rules = [
            'leave_type' => 'required|string',
            'start_date' => 'required|date_format:Y-m-d',
            'reason' => 'nullable|string',
            'duration' => 'required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'forwarded_to_employee_id' => 'nullable|exists:users,id',
            'is_compensatory' => 'nullable|boolean',
            'compensatory_date' => 'nullable|required_if:is_compensatory,true|required_if:leave_type,compensatory|date_format:Y-m-d',
        ];

        if ($request->duration === 'multiple') {
            $rules['end_date'] = 'required|date_format:Y-m-d|after_or_equal:start_date';
        }

        $request->validate($rules);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/attachments'), $filename);
            $attachmentPath = 'attachments/' . $filename;
        }

        $start_date = Carbon::createFromFormat('Y-m-d', $request->start_date);
        $end_date = $request->duration === 'multiple' ? Carbon::createFromFormat('Y-m-d', $request->end_date) : $start_date->copy();

        $is_compensatory = $request->boolean('is_compensatory') || $request->leave_type === 'compensatory';
        $compensatory_date = $is_compensatory ? Carbon::createFromFormat('Y-m-d', $request->compensatory_date)->format('Y-m-d H:i:s') : null;

        $leave_type = $request->leave_type;
        $originalLeaveType = $leave_type; // Store original leave type
        $conversionReason = null; // Store reason for conversion
        $user = Auth::user();

        if (!$user->employee) {
            return response()->json(['message' => 'You are not an employee and cannot apply for leave.'], 400);
        }

        $joiningDate = Carbon::parse($user->employee->joining_date);
        $today = Carbon::today();
        $yearsOfService = $joiningDate->diffInYears($today);

        if ($yearsOfService < 1) {
            if ($leave_type !== 'unpaid') {
                Log::warning('LeaveRequestApiController@store: User with less than 1 year of service tried to apply for non-unpaid leave.', ['user_id' => $user->id, 'leave_type' => $leave_type]);
                // For new employees, convert non-unpaid leaves to unpaid
                $originalLeaveType = $leave_type;
                $conversionReason = 'new employee ineligibility';
                $leave_type = 'unpaid';
                Log::info('LeaveRequestApiController@store: New employee. Converting ' . $originalLeaveType . ' to unpaid leave.');
            }
        } else {
            Log::info('LeaveRequestApiController@store: Starting leave balance calculation.');
            $leaveData = $this->calculateAllottedAndTakenLeaves($user);

            $allotted_casual_leaves = $leaveData['casual']['allotted'];
            $taken_casual = $leaveData['casual']['taken'];

            $allotted_sick_leaves = $leaveData['sick']['allotted'];
            $taken_sick = $leaveData['sick']['taken'];

            $allotted_privileged_leaves = $leaveData['paid']['allotted'];
            $taken_privileged = $leaveData['paid']['taken'];

            $requested_days = $start_date->diffInDays($end_date) + 1;
            if ($request->duration === 'first_half' || $request->duration === 'second_half') {
                $requested_days = 0.5;
            }

            // Check if requested leave exceeds available balance and convert to unpaid if needed
            if ($leave_type === 'casual' && ($taken_casual + $requested_days) > $allotted_casual_leaves) {
                Log::info(
                    'LeaveRequestApiController@store: Insufficient casual leaves. Converting to unpaid.',
                    ['user_id' => $user->id, 'available' => $allotted_casual_leaves - $taken_casual, 'requested' => $requested_days]
                );
                $conversionReason = 'insufficient casual leave balance';
                $leave_type = 'unpaid';
            } elseif ($leave_type === 'sick' && ($taken_sick + $requested_days) > $allotted_sick_leaves) {
                Log::info(
                    'LeaveRequestApiController@store: Insufficient sick leaves. Converting to unpaid.',
                    ['user_id' => $user->id, 'available' => $allotted_sick_leaves - $taken_sick, 'requested' => $requested_days]
                );
                $conversionReason = 'insufficient sick leave balance';
                $leave_type = 'unpaid';
            } elseif ($leave_type === 'paid' && ($taken_privileged + $requested_days) > $allotted_privileged_leaves) {
                Log::info(
                    'LeaveRequestApiController@store: Insufficient privileged leaves. Converting to unpaid.',
                    ['user_id' => $user->id, 'available' => $allotted_privileged_leaves - $taken_privileged, 'requested' => $requested_days]
                );
                $conversionReason = 'insufficient privileged leave balance';
                $leave_type = 'unpaid';
            }
        }

        $leaveRequest = LeaveRequest::create([
            'user_id' => Auth::id(),
            'leave_type' => $leave_type,
            'start_date' => $start_date->format('Y-m-d H:i:s'),
            'end_date' => $end_date->format('Y-m-d H:i:s'),
            'reason' => $request->reason,
            'duration' => $request->duration,
            'status' => 'pending',
            'attachment' => $attachmentPath,
            'forwarded_to_employee_id' => $request->forwarded_to_employee_id,
            'is_compensatory' => $is_compensatory,
            'compensatory_date' => $compensatory_date,
        ]);

        // Notify Reporting Authority
        $reporter = $user->employee->reporter;
        if ($reporter && $reporter->user) {
            try {
                // Generate a unique notification ID
                do {
                    $notificationId = (string) Str::uuid();
                } while (Notification::where('notification_id', $notificationId)->exists());

                $title = "New Leave Request";
                $message = "{$user->name} has applied for " . ucfirst($leave_type) . " leave from " . $start_date->format('d/m/Y') . " to " . $end_date->format('d/m/Y') . ".";
                $payloadData = [
                    'type' => 'leave_request',
                    'id' => $leaveRequest->id,
                    'route' => 'NotificationView',
                    'menu_id' => 31,
                    'notification_id' => $notificationId,
                ];

                $response = Http::withHeaders([
                    'Authorization' => 'Basic ' . env('ONESIGNAL_REST_API_KEY'),
                    'Content-Type' => 'application/json',
                ])->post('https://onesignal.com/api/v1/notifications', [
                    'app_id' => env('ONESIGNAL_APP_ID'),
                    'include_aliases' => [
                        'external_id' => [$reporter->user->email],
                    ],
                    'data' => $payloadData,
                    'target_channel' => 'push',
                    'priority' => 10,
                    'android_visibility' => 1,
                    'headings' => ['en' => $title],
                    'contents' => ['en' => $message],
                ]);

                // Save the notification in the local table
                Notification::create([
                    'notification_id' => $notificationId,
                    'user_id' => $reporter->user_id,
                    'title' => $title,
                    'message' => $message,
                    'data' => $payloadData,
                ]);

                Log::info('OneSignal leave notification sent successfully to reporter (API).', [
                    'reporter_id' => $reporter->id,
                    'leave_id' => $leaveRequest->id,
                    'response' => $response->json(),
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send OneSignal leave notification to reporter (API).', [
                    'reporter_id' => $reporter->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $message = 'Leave request created successfully.';
        if ($originalLeaveType !== $leave_type && $leave_type === 'unpaid') {
            if ($conversionReason === 'new employee ineligibility') {
                $message = 'Leave request created successfully but submitted as unpaid (LOP) as you are not yet eligible for ' . $originalLeaveType . ' leave.';
            } else {
                $message = 'Leave request created successfully but submitted as unpaid (LOP) due to ' . $conversionReason . '.';
            }
        }

        return response()->json(['message' => $message, 'data' => $leaveRequest], 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *      path="/leave-requests/{id}",
     *      operationId="getLeaveRequestById",
     *      tags={"Leave Requests"},
     *      summary="Get leave request information",
     *      description="Returns leave request data",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of leave request to return",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/LeaveRequest")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      ),
     *      security={{"bearerAuth":{}}}
     * )
     */
    public function show(LeaveRequest $leaveRequest)
    {
        return response()->json($leaveRequest);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Post(
     *      path="/leave-requests/{id}",
     *      operationId="updateLeaveRequest",
     *      tags={"Leave Requests"},
     *      summary="Update existing leave request",
     *      description="Updates a leave request and returns the updated leave request",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of leave request to update",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"leave_type", "start_date", "duration"},
     *                  @OA\Property(property="leave_type", type="string", example="casual"),
     *                  @OA\Property(property="start_date", type="string", format="date", example="2025-12-25"),
     *                  @OA\Property(property="end_date", type="string", format="date", nullable=true, example="2025-12-26"),
     *                  @OA\Property(property="reason", type="string", example="Sick leave"),
     *                  @OA\Property(property="duration", type="string", example="full_day"),
     *                  @OA\Property(property="is_compensatory", type="boolean", example=true),
     *                  @OA\Property(property="compensatory_date", type="string", format="date", nullable=true, example="2025-12-27"),
     *                  @OA\Property(property="attachment", type="string", format="binary", nullable=true, description="Allowed types: jpg, jpeg, png, pdf")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/LeaveRequest")
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      ),
     *      security={{"bearerAuth":{}}}
     * )
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        // Authorization check: Only the creator can update a pending leave request
        // if (Auth::id() !== $leaveRequest->user_id || $leaveRequest->status !== 'pending') {
        //     return response()->json(['message' => 'You are not authorized to update this leave request.'], 403);
        // }

        if ($request->duration === 'multiple' && is_string($request->start_date)) {
            $dates = explode(' - ', $request->start_date);
            if (count($dates) === 2) {
                $request->merge([
                    'start_date' => $dates[0],
                    'end_date' => $dates[1],
                ]);
            }
        }

        $rules = [
            'leave_type' => 'sometimes|required|string',
            'start_date' => 'sometimes|required|date_format:Y-m-d',
            'reason' => 'nullable|string',
            'duration' => 'sometimes|required|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'is_compensatory' => 'nullable|boolean',
            'compensatory_date' => 'nullable|required_if:is_compensatory,true|required_if:leave_type,compensatory|date_format:Y-m-d',
        ];

        if ($request->duration === 'multiple') {
            $rules['end_date'] = 'required|date_format:Y-m-d|after_or_equal:start_date';
        }

        $validatedData = $request->validate($rules);

        // Prepare dates
        $start_date = $leaveRequest->start_date;
        if (isset($validatedData['start_date'])) {
            $start_date = Carbon::createFromFormat('Y-m-d', $validatedData['start_date']);
            $validatedData['start_date'] = $start_date->format('Y-m-d H:i:s');

            if ($request->input('duration', $leaveRequest->duration) !== 'multiple') {
                $validatedData['end_date'] = $validatedData['start_date'];
            }
        } else {
            $start_date = Carbon::parse($start_date);
        }

        $end_date = $leaveRequest->end_date;
        if ($request->input('duration', $leaveRequest->duration) === 'multiple') {
            if (isset($validatedData['end_date'])) {
                $end_date_obj = Carbon::createFromFormat('Y-m-d', $validatedData['end_date']);
                $validatedData['end_date'] = $end_date_obj->format('Y-m-d H:i:s');
                $end_date = $end_date_obj;
            } else {
                $end_date = Carbon::parse($end_date);
            }
        } else {
            // If not multiple, end date same as start date (which is already set in validatedData or existing)
            $end_date = $start_date->copy();
            $validatedData['end_date'] = $start_date->format('Y-m-d H:i:s');
        }

        if ($request->hasFile('attachment')) {
            if ($leaveRequest->attachment) {
                if (file_exists(public_path('storage/' . $leaveRequest->attachment))) {
                    unlink(public_path('storage/' . $leaveRequest->attachment));
                }
            }
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/attachments'), $filename);
            $validatedData['attachment'] = 'attachments/' . $filename;
        }

        if ($request->has('is_compensatory') || $request->leave_type === 'compensatory') {
            $validatedData['is_compensatory'] = $request->boolean('is_compensatory') || $request->leave_type === 'compensatory';
            $validatedData['compensatory_date'] = $validatedData['is_compensatory'] ? Carbon::createFromFormat('Y-m-d', $request->compensatory_date)->format('Y-m-d H:i:s') : null;
        }

        // Apply Policy Logic
        $leave_type = $request->input('leave_type', $leaveRequest->leave_type);
        $user = Auth::user();

        if (!$user->employee) {
            return response()->json(['message' => 'You are not an employee and cannot apply for leave.'], 400);
        }

        $joiningDate = Carbon::parse($user->employee->joining_date);
        $today = Carbon::today();
        $yearsOfService = $joiningDate->diffInYears($today);

        if ($yearsOfService < 1) {
            if ($leave_type !== 'unpaid') {
                // For new employees, convert non-unpaid leaves to unpaid
                $validatedData['leave_type'] = 'unpaid';
            }
        } else {
            $leaveData = $this->calculateAllottedAndTakenLeaves($user, $leaveRequest->id);

            $allotted_casual_leaves = $leaveData['casual']['allotted'];
            $taken_casual = $leaveData['casual']['taken'];

            $allotted_sick_leaves = $leaveData['sick']['allotted'];
            $taken_sick = $leaveData['sick']['taken'];

            $allotted_privileged_leaves = $leaveData['paid']['allotted'];
            $taken_privileged = $leaveData['paid']['taken'];

            $requested_days = $start_date->diffInDays($end_date) + 1;
            $duration = $request->input('duration', $leaveRequest->duration);
            if ($duration === 'first_half' || $duration === 'second_half') {
                $requested_days = 0.5;
            }

            if ($leave_type === 'casual' && ($taken_casual + $requested_days) > $allotted_casual_leaves) {
                $validatedData['leave_type'] = 'unpaid';
            } elseif ($leave_type === 'sick' && ($taken_sick + $requested_days) > $allotted_sick_leaves) {
                $validatedData['leave_type'] = 'unpaid';
            } elseif ($leave_type === 'paid' && ($taken_privileged + $requested_days) > $allotted_privileged_leaves) {
                $validatedData['leave_type'] = 'unpaid';
            }
        }

        $leaveRequest->update($validatedData);

        return response()->json(['message' => 'Leave request updated successfully.', 'data' => $leaveRequest]);
    }

    /**
     * Handle the update from a POST request to work around form method limitations.
     */
    public function updateFromPost(Request $request, LeaveRequest $leaveRequest)
    {
        return $this->update($request, $leaveRequest);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *      path="/leave-requests/{id}",
     *      operationId="deleteLeaveRequest",
     *      tags={"Leave Requests"},
     *      summary="Delete existing leave request",
     *      description="Deletes a leave request",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of leave request to delete",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Leave request deleted successfully.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      ),
     *      security={{"bearerAuth":{}}}
     * )
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->attachment) {
            if (file_exists(public_path('storage/' . $leaveRequest->attachment))) {
                unlink(public_path('storage/' . $leaveRequest->attachment));
            }
        }

        $leaveRequest->delete();

        return response()->json(['message' => 'Leave request deleted successfully.']);
    }

    /**
     * Update the status of the specified resource.
     *
     * @OA\Put(
     *      path="/leave-requests/{id}/update-status",
     *      operationId="updateLeaveRequestStatus",
     *      tags={"Leave Requests"},
     *      summary="Update leave request status",
     *      description="Updates the status of a leave request",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of leave request to update status",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          description="The new status for the leave request.",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              enum={"pending", "approved", "rejected", "cancelled", "cancelled by admin", "approved and forwarded"}
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="forwarded_to_employee_id",
     *          description="ID of the employee to forward the request to. Required when status is 'approved and forwarded'.",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Leave request status updated successfully.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      ),
     *      security={{"bearerAuth":{}}}
     * )
     */
    public function updateStatus(Request $request, LeaveRequest $leaveRequest)
    {
        // if (Auth::user()->user_type !== 'admin' && !(Auth::user()->employee && Auth::user()->employee->subordinates->isNotEmpty()) && !($leaveRequest->forwarded_to_employee_id === Auth::id())) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $request->validate([
            'status' => 'required|string|in:approved,pending,rejected,cancelled,cancelled by admin,approved and forwarded',
            'forwarded_to_employee_id' => 'nullable|exists:users,id',
        ]);

        $leaveRequest->status = $request->status;

        if ($request->status === 'approved and forwarded') {
            $leaveRequest->forwarded_to_employee_id = $request->forwarded_to_employee_id;
        } else {
            $leaveRequest->forwarded_to_employee_id = null;
        }

        $leaveRequest->save();
        return response()->json(['message' => 'Leave request status updated.']);
    }

    private function calculateAllottedAndTakenLeaves($user, $excludeLeaveRequestId = null)
    {
        $joiningDate = Carbon::parse($user->employee->joining_date);
        $today = Carbon::today();
        $yearsOfService = $joiningDate->diffInYears($today);
        $currentYear = $today->year;
        $joinYear = $joiningDate->year;

        $allotted_casual_leaves = 0;
        $allotted_sick_leaves = 0;
        $allotted_privileged_leaves = 0;

        if ($yearsOfService >= 1) {
            $total_casual_leaves = DB::table('settings')->where('key', 'casual_leave_limit')->value('value') ?? 0;
            $total_sick_leaves = DB::table('settings')->where('key', 'sick_leave_limit')->value('value') ?? 0;
            $total_privileged_leaves = DB::table('settings')->where('key', 'privileged_leave_limit')->value('value') ?? 0;

            $allotted_casual_leaves = $total_casual_leaves;
            $allotted_sick_leaves = $total_sick_leaves;
            $allotted_privileged_leaves = $total_privileged_leaves;

            if ($yearsOfService == 1 && $joinYear == $currentYear - 1) {
                $eligibleFromMonth = $joiningDate->month;
                $allotted_casual_leaves = round(($total_casual_leaves / 12) * (13 - $eligibleFromMonth));
                $allotted_sick_leaves = round(($total_sick_leaves / 12) * (13 - $eligibleFromMonth));
                $allotted_privileged_leaves = round(($total_privileged_leaves / 12) * (13 - $eligibleFromMonth));
            }

            // Carry forward logic (Only for Privileged Leaves)
            $privileged_carry_forward = 0;

            if ($currentYear > $joinYear) {
                $leaves_last_year = LeaveRequest::where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->whereYear('start_date', $currentYear - 1)
                    ->get();

                $last_year_join_month = ($joinYear == $currentYear - 1) ? $joiningDate->month : 1;
                $allotted_privileged_last_year = round(($total_privileged_leaves / 12) * (13 - $last_year_join_month));

                $taken_privileged_last_year = 0;

                foreach ($leaves_last_year as $leave) {
                    $leave_start = Carbon::parse($leave->start_date);
                    $leave_end = Carbon::parse($leave->end_date);
                    $days = $leave_start->diffInDays($leave_end) + 1;
                    if ($leave->duration !== 'full_day' && $leave->duration !== 'multiple') {
                        $days = 0.5;
                    }

                    if ($leave->leave_type === 'paid') $taken_privileged_last_year += $days;
                }

                $privileged_carry_forward = max(0, $allotted_privileged_last_year - $taken_privileged_last_year);
            }

            $allotted_privileged_leaves += $privileged_carry_forward;
        }

        // Calculate taken leaves this year
        $query = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', ['approved', 'pending', 'approved and forwarded'])
            ->whereYear('start_date', $currentYear);

        if ($excludeLeaveRequestId) {
            $query->where('id', '!=', $excludeLeaveRequestId);
        }

        $leaves_taken_this_year = $query->get();

        $taken_casual = 0;
        $taken_sick = 0;
        $taken_privileged = 0;
        $taken_unpaid = 0;

        foreach ($leaves_taken_this_year as $leave) {
            $leave_start = Carbon::parse($leave->start_date);
            $leave_end = Carbon::parse($leave->end_date);
            $days = $leave_start->diffInDays($leave_end) + 1;

            if ($leave->duration !== 'full_day' && $leave->duration !== 'multiple') {
                $days = 0.5;
            }

            if ($leave->leave_type === 'casual') $taken_casual += $days;
            if ($leave->leave_type === 'sick') $taken_sick += $days;
            if ($leave->leave_type === 'paid') $taken_privileged += $days;
            if ($leave->leave_type === 'unpaid') $taken_unpaid += $days;
        }

        return [
            'casual' => ['allotted' => $allotted_casual_leaves, 'taken' => $taken_casual],
            'sick' => ['allotted' => $allotted_sick_leaves, 'taken' => $taken_sick],
            'paid' => ['allotted' => $allotted_privileged_leaves, 'taken' => $taken_privileged],
            'unpaid' => ['taken' => $taken_unpaid]
        ];
    }
}
