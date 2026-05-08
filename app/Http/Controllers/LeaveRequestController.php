<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Notification;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = LeaveRequest::with(['user', 'forwardedToEmployee.employee.department']);

            if ($request->has('my_requests')) {
                $query->where('user_id', Auth::id());
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

            if ($request->employee_id) {
                $query->where('user_id', $request->employee_id);
            }

            if ($request->leave_type) {
                $query->where('leave_type', $request->leave_type);
            }

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($request->start_date) {
                $query->whereDate('start_date', '>=', $request->start_date);
            }

            if ($request->end_date) {
                $query->whereDate('end_date', '<=', $request->end_date);
            }
            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('duration_display', function ($row) {
                    return ucfirst(str_replace('_', ' ', $row->duration));
                })
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                    // Check if the current user is the creator and the status is pending
                    if (Auth::id() === $row->user_id && $row->status === 'pending') {
                        $btn .= '<li class="edit"><a title="Edit" href="javascript:void(0)" data-id="' . $row->id . '" class="edit-leave-request-btn"><i class="icon-edit"></i></a></li>';
                        $btn .= '<li class="delete"><a title="Delete" href="javascript:void(0)" data-id="' . $row->id . '" class="delete-leave-request-btn"><i class="icon-trash"></i></a></li>';
                    }
                    $btn .= '<li class="view"><a title="View" href="#" data-bs-toggle="modal" data-bs-target="#viewLeaveRequestModal" data-id="' . $row->id . '"><i class="icon-eye"></i></a></li>'; // Added view button
                    $btn .= '</ul>';
                    return $btn;
                })
                ->editColumn('leave_type', function ($row) {
                    $leaveTypeClasses = [
                        'casual' => 'bg-info',
                        'sick' => 'bg-warning',
                        'paid' => 'bg-success',
                        'unpaid' => 'bg-danger',
                        'compensatory' => 'bg-primary',
                    ];
                    $class = $leaveTypeClasses[$row->leave_type] ?? 'bg-secondary'; // Default to bg-secondary if type not found
                    return '<span class="badge ' . $class . '">' . ucfirst($row->leave_type) . '</span>';
                })
                ->editColumn('status', function ($row) use ($request) {
                    $forwardedToEmployeeName = $row->forwardedToEmployee && $row->forwardedToEmployee->employee ? $row->forwardedToEmployee->employee->name : null;
                    $forwardedToEmployeeDepartment = $row->forwardedToEmployee && $row->forwardedToEmployee->employee && $row->forwardedToEmployee->employee->department ? $row->forwardedToEmployee->employee->department->name : null;

                    return [
                        'status' => $row->status,
                        'forwarded_to_employee_name' => $forwardedToEmployeeName,
                        'forwarded_to_employee_department' => $forwardedToEmployeeDepartment,
                        'my_requests' => $request->my_requests, // Pass this flag to frontend
                    ];
                })
                ->rawColumns(['action', 'leave_type', 'status', 'duration_display'])
                ->make(true);
        }

        $loggedInUser = Auth::user();
        $employees = collect(); // Initialize as an empty collection

        if ($loggedInUser) {
            if ($loggedInUser->user_type === 'admin') {
                $employees = \App\Models\Employee::all(); // Admins see all employees
            } elseif ($loggedInUser->user_type === 'employee') {
                $loggedInUser->load('employee.subordinates'); // Eager load employee and its subordinates
                if ($loggedInUser->employee) {
                    $employees = $loggedInUser->employee->subordinates; // Employees see their subordinates
                }
            }
        }

        $leaveBalances = $this->calculateLeaveBalances($loggedInUser);

        return view('requests.leaves.index', compact('employees', 'leaveBalances'));
    }

    private function calculateLeaveBalances($user)
    {
        if (!$user || !$user->employee) {
            return null;
        }

        $data = $this->calculateAllottedAndTakenLeaves($user);

        $allotted_casual_leaves = $data['casual']['allotted'];
        $taken_casual = $data['casual']['taken'];

        $allotted_sick_leaves = $data['sick']['allotted'];
        $taken_sick = $data['sick']['taken'];

        $allotted_privileged_leaves = $data['paid']['allotted'];
        $taken_privileged = $data['paid']['taken'];
        $taken_unpaid = $data['unpaid']['taken'];

        return [
            'casual' => [
                'allotted' => $allotted_casual_leaves,
                'taken' => $taken_casual,
                'remaining' => max(0, $allotted_casual_leaves - $taken_casual)
            ],
            'sick' => [
                'allotted' => $allotted_sick_leaves,
                'taken' => $taken_sick,
                'remaining' => max(0, $allotted_sick_leaves - $taken_sick)
            ],
            'paid' => [
                'allotted' => $allotted_privileged_leaves,
                'taken' => $taken_privileged,
                'remaining' => max(0, $allotted_privileged_leaves - $taken_privileged)
            ],
            'unpaid' => [
                'taken' => $taken_unpaid
            ]
        ];
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        Log::info('LeaveRequestController@store: Received new leave request.');

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
            'leave_type' => 'required|string|max:255',
            'start_date' => 'required|date_format:d/m/Y',
            'duration' => 'required|string|in:full_day,first_half,second_half,multiple',
            'attachment' => 'nullable|file|mimes:jpeg,png,pdf|max:2048',
            'forwarded_to_employee_id' => 'nullable|exists:users,id',
            'reason' => 'nullable|string',
            'is_compensatory' => 'nullable|boolean',
            'compensatory_date' => 'nullable|required_if:is_compensatory,true|date_format:d/m/Y',
        ];

        if ($request->duration === 'multiple') {
            $rules['end_date'] = 'required|date_format:d/m/Y|after_or_equal:start_date';
        }

        try {
            $request->validate($rules);
            Log::info('LeaveRequestController@store: Validation passed.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('LeaveRequestController@store: Validation failed.', ['errors' => $e->errors()]);
            return response()->json(['errors' => $e->errors()], 422);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/attachments'), $filename);
            $attachmentPath = 'attachments/' . $filename;
        }

        $start_date = Carbon::createFromFormat('d/m/Y', $request->start_date);
        $end_date = $request->duration === 'multiple' ? Carbon::createFromFormat('d/m/Y', $request->end_date) : $start_date;
        $compensatory_date = $request->is_compensatory ? Carbon::createFromFormat('d/m/Y', $request->compensatory_date)->format('Y-m-d H:i:s') : null;

        $leave_type = $request->leave_type;
        $originalLeaveType = $leave_type; // Store original leave type
        $conversionReason = null; // Store reason for conversion
        $user = Auth::user();
        Log::info('LeaveRequestController@store: User:', ['user_id' => $user->id, 'user_email' => $user->email]);

        if (!$user->employee) {
            Log::error('LeaveRequestController@store: User does not have an associated employee record.', ['user_id' => $user->id]);
            return response()->json(['message' => 'You are not an employee and cannot apply for leave.'], 400);
        }
        Log::info('LeaveRequestController@store: Employee record found for user.', ['employee_id' => $user->employee->id]);


        $joiningDate = Carbon::parse($user->employee->joining_date);
        $today = Carbon::today();
        $yearsOfService = $joiningDate->diffInYears($today);

        if ($yearsOfService < 1) {
            if ($leave_type !== 'unpaid') {
                Log::warning('LeaveRequestController@store: User with less than 1 year of service tried to apply for non-unpaid leave.', ['user_id' => $user->id, 'leave_type' => $leave_type]);
                // For new employees, convert non-unpaid leaves to unpaid
                $originalLeaveType = $leave_type;
                $conversionReason = 'new employee ineligibility';
                $leave_type = 'unpaid';
                Log::info('LeaveRequestController@store: New employee. Converting ' . $originalLeaveType . ' to unpaid leave.');
            }
        } else {
            Log::info('LeaveRequestController@store: Starting leave balance calculation.');
            $leaveData = $this->calculateAllottedAndTakenLeaves($user);

            $allotted_casual_leaves = $leaveData['casual']['allotted'];
            $taken_casual = $leaveData['casual']['taken'];

            $allotted_sick_leaves = $leaveData['sick']['allotted'];
            $taken_sick = $leaveData['sick']['taken'];

            $allotted_privileged_leaves = $leaveData['paid']['allotted'];
            $taken_privileged = $leaveData['paid']['taken'];

            Log::info('LeaveRequestController@store: Total allotted leaves.', compact('allotted_privileged_leaves', 'allotted_casual_leaves', 'allotted_sick_leaves'));
            Log::info('LeaveRequestController@store: Leaves taken this year.', compact('taken_casual', 'taken_sick', 'taken_privileged'));

            $requested_days = $start_date->diffInDays($end_date) + 1;
            if ($request->duration === 'first_half' || $request->duration === 'second_half') {
                $requested_days = 0.5;
            }

            // Check if requested leave exceeds available balance and convert to unpaid if needed
            $originalLeaveType = $leave_type;
            if ($leave_type === 'casual' && ($taken_casual + $requested_days) > $allotted_casual_leaves) {
                Log::info(
                    'LeaveRequestController@store: Insufficient casual leaves. Converting to unpaid.',
                    ['user_id' => $user->id, 'available' => $allotted_casual_leaves - $taken_casual, 'requested' => $requested_days]
                );
                $conversionReason = 'insufficient casual leave balance';
                $leave_type = 'unpaid';
            } elseif ($leave_type === 'sick' && ($taken_sick + $requested_days) > $allotted_sick_leaves) {
                Log::info(
                    'LeaveRequestController@store: Insufficient sick leaves. Converting to unpaid.',
                    ['user_id' => $user->id, 'available' => $allotted_sick_leaves - $taken_sick, 'requested' => $requested_days]
                );
                $conversionReason = 'insufficient sick leave balance';
                $leave_type = 'unpaid';
            } elseif ($leave_type === 'paid' && ($taken_privileged + $requested_days) > $allotted_privileged_leaves) {
                Log::info(
                    'LeaveRequestController@store: Insufficient privileged leaves. Converting to unpaid.',
                    ['user_id' => $user->id, 'available' => $allotted_privileged_leaves - $taken_privileged, 'requested' => $requested_days]
                );
                $conversionReason = 'insufficient privileged leave balance';
                $leave_type = 'unpaid';
            }
        }

        try {
            Log::info('LeaveRequestController@store: Attempting to create leave request.');
            $leaveRequest = LeaveRequest::create([
                'user_id' => Auth::id(),
                'leave_type' => $leave_type,
                'start_date' => $start_date->format('Y-m-d H:i:s'),
                'end_date' => $end_date->format('Y-m-d H:i:s'),
                'duration' => $request->duration,
                'status' => 'pending',
                'attachment' => $attachmentPath,
                'forwarded_to_employee_id' => $request->forwarded_to_employee_id,
                'reason' => $request->reason,
                'is_compensatory' => $request->boolean('is_compensatory'),
                'compensatory_date' => $compensatory_date,
            ]);
            Log::info('LeaveRequestController@store: Leave request created successfully.');

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

                    Log::info('OneSignal leave notification sent successfully to reporter.', [
                        'reporter_id' => $reporter->id,
                        'leave_id' => $leaveRequest->id,
                        'response' => $response->json(),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send OneSignal leave notification to reporter.', [
                        'reporter_id' => $reporter->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Prepare response message
            $message = 'Leave request created successfully.';
            if ($originalLeaveType !== $leave_type && $leave_type === 'unpaid') {
                if ($conversionReason === 'new employee ineligibility') {
                    $message = 'Leave request created successfully but submitted as unpaid (LOP) as you are not yet eligible for ' . $originalLeaveType . ' leave.';
                } else {
                    $message = 'Leave request created successfully but submitted as unpaid (LOP) due to ' . $conversionReason . '.';
                }
            }

            return response()->json(['message' => $message], 201);
        } catch (\Exception $e) {
            Log::error('LeaveRequestController@store: Failed to create leave request.', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Failed to create leave request. Please try again.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load('user'); // Eager load the user relationship
        return response()->json($leaveRequest);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveRequest $leaveRequest)
    {
        return response()->json($leaveRequest);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        // Authorization check: Only the creator can update a pending leave request
        if (Auth::id() !== $leaveRequest->user_id || $leaveRequest->status !== 'pending') {
            return response()->json(['message' => 'You are not authorized to update this leave request.'], 403);
        }

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
            'start_date' => 'required|date_format:d/m/Y',
            'duration' => 'required|string|in:full_day,first_half,second_half,multiple',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'reason' => 'nullable|string',
            'is_compensatory' => 'nullable|boolean',
            'compensatory_date' => 'nullable|required_if:is_compensatory,true|date_format:d/m/Y',
        ];

        if ($request->duration === 'multiple') {
            $rules['end_date'] = 'required|date_format:d/m/Y|after_or_equal:start_date';
        }

        // Only validate status if user is admin or if the request does not belong to the current user
        if (Auth::user()->user_type === 'admin' || $leaveRequest->user_id !== Auth::id()) {
            $rules['status'] = 'required|string|in:approved,pending,rejected,cancelled,cancelled by admin,approved and forwarded';
        }

        $request->validate($rules);

        $attachmentPath = $leaveRequest->attachment;
        if ($request->hasFile('attachment')) {
            if ($attachmentPath) {
                if (file_exists(public_path('storage/' . $attachmentPath))) {
                    unlink(public_path('storage/' . $attachmentPath));
                }
            }
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('storage/attachments'), $filename);
            $attachmentPath = 'attachments/' . $filename;
        }

        $start_date = Carbon::createFromFormat('d/m/Y', $request->start_date);
        $end_date = $request->duration === 'multiple' ? Carbon::createFromFormat('d/m/Y', $request->end_date) : $start_date;
        $compensatory_date = $request->is_compensatory ? Carbon::createFromFormat('d/m/Y', $request->compensatory_date)->format('Y-m-d H:i:s') : null;

        $leave_type = $request->leave_type;
        $user = Auth::user();

        if (!$user->employee) {
            return redirect()->back()->with('error', 'You are not an employee and cannot apply for leave.');
        }

        $joiningDate = Carbon::parse($user->employee->joining_date);
        $today = Carbon::today();
        $yearsOfService = $joiningDate->diffInYears($today);

        if ($yearsOfService < 1) {
            if ($leave_type !== 'unpaid') {
                Log::warning('LeaveRequestController@update: User with less than 1 year of service tried to apply for non-unpaid leave.', ['user_id' => $user->id, 'leave_type' => $leave_type]);
                // For new employees, convert non-unpaid leaves to unpaid
                $originalLeaveType = $leave_type;
                $leave_type = 'unpaid';
                Log::info('LeaveRequestController@update: New employee. Converting ' . $originalLeaveType . ' to unpaid leave.');
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
            if ($request->duration === 'first_half' || $request->duration === 'second_half') {
                $requested_days = 0.5;
            }

            // Check if requested leave exceeds available balance and convert to unpaid if needed
            if ($leave_type === 'casual' && ($taken_casual + $requested_days) > $allotted_casual_leaves) {
                Log::info(
                    'LeaveRequestController@update: Insufficient casual leaves. Converting to unpaid.',
                    ['user_id' => $user->id, 'available' => $allotted_casual_leaves - $taken_casual, 'requested' => $requested_days]
                );
                $leave_type = 'unpaid';
            } elseif ($leave_type === 'sick' && ($taken_sick + $requested_days) > $allotted_sick_leaves) {
                Log::info(
                    'LeaveRequestController@update: Insufficient sick leaves. Converting to unpaid.',
                    ['user_id' => $user->id, 'available' => $allotted_sick_leaves - $taken_sick, 'requested' => $requested_days]
                );
                $leave_type = 'unpaid';
            } elseif ($leave_type === 'paid' && ($taken_privileged + $requested_days) > $allotted_privileged_leaves) {
                Log::info(
                    'LeaveRequestController@update: Insufficient privileged leaves. Converting to unpaid.',
                    ['user_id' => $user->id, 'available' => $allotted_privileged_leaves - $taken_privileged, 'requested' => $requested_days]
                );
                $leave_type = 'unpaid';
            }
        }

        $updateData = [
            'leave_type' => $request->leave_type,
            'start_date' => $start_date->format('Y-m-d H:i:s'),
            'end_date' => $end_date->format('Y-m-d H:i:s'),
            'duration' => $request->duration,
            'attachment' => $attachmentPath,
            'reason' => $request->reason,
            'is_compensatory' => $request->boolean('is_compensatory'),
            'compensatory_date' => $compensatory_date,
        ];

        // Only allow status update if user is admin or if the request does not belong to the current user
        if (Auth::user()->user_type === 'admin' || $leaveRequest->user_id !== Auth::id()) {
            $updateData['status'] = $request->status;
        }

        $leaveRequest->update($updateData);

        return redirect()->route('leave-requests.index')->with('success', 'Leave request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->status !== 'pending') {
            return redirect()->route('leave-requests.index')->with('error', 'Only pending leave requests can be deleted.');
        }

        if ($leaveRequest->attachment) {
            if (file_exists(public_path('storage/' . $leaveRequest->attachment))) {
                unlink(public_path('storage/' . $leaveRequest->attachment));
            }
        }

        $leaveRequest->delete();

        return redirect()->route('leave-requests.index')->with('success', 'Leave request deleted successfully.');
    }

    public function updateStatus(Request $request, LeaveRequest $leaveRequest)
    {
        // if (Auth::user()->user_type !== 'admin' && !(Auth::user()->employee && Auth::user()->employee->subordinates->isNotEmpty()) && !($leaveRequest->forwarded_to_employee_id === Auth::id())) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $request->validate([
            'status' => 'required|string|in:approved,pending,rejected,cancelled,cancelled by admin,approved and forwarded',
            'forwarded_to_employee_id' => 'nullable|exists:users,id', // Validate the forwarded_to_employee_id
        ]);

        $leaveRequest->status = $request->status;

        // If status is 'approved and forwarded', save the forwarded_to_employee_id
        if ($request->status === 'approved and forwarded') {
            $leaveRequest->forwarded_to_employee_id = $request->forwarded_to_employee_id;
        } else {
            // If status changes from 'approved and forwarded' to something else, clear the forwarded_to_employee_id
            $leaveRequest->forwarded_to_employee_id = null;
        }

        $leaveRequest->save();
        return response()->json(['message' => 'Leave request status updated.']);
    }

    private function calculateAllottedAndTakenLeaves($user, $excludeLeaveRequestId = null)
    {
        if (!$user || !$user->employee) {
            return [
                'casual' => ['allotted' => 0, 'taken' => 0],
                'sick' => ['allotted' => 0, 'taken' => 0],
                'paid' => ['allotted' => 0, 'taken' => 0],
                'unpaid' => ['taken' => 0]
            ];
        }

        $joiningDate = Carbon::parse($user->employee->joining_date);
        $today = Carbon::today();
        $yearsOfService = $joiningDate->diffInYears($today);
        $currentYear = $today->year;
        $joinYear = $joiningDate->year;

        $allotted_casual_leaves = 0;
        $allotted_sick_leaves = 0;
        $allotted_privileged_leaves = 0;

        if (true) { // Always calculate basic leaves
            $total_casual_leaves = DB::table('settings')->where('key', 'casual_leave_limit')->value('value') ?? 0;
            $total_sick_leaves = DB::table('settings')->where('key', 'sick_leave_limit')->value('value') ?? 0;
            $total_privileged_leaves = DB::table('settings')->where('key', 'privileged_leave_limit')->value('value') ?? 0;

            if ($joinYear == $currentYear) {
                // Joined this year - Pro-rate Casual and Sick
                $eligibleFromMonth = $joiningDate->month;
                $allotted_casual_leaves = round(($total_casual_leaves / 12) * (13 - $eligibleFromMonth));
                $allotted_sick_leaves = round(($total_sick_leaves / 12) * (13 - $eligibleFromMonth));
                $allotted_privileged_leaves = 0; // Usually earned after service
            } else {
                // Joined before this year
                $allotted_casual_leaves = $total_casual_leaves;
                $allotted_sick_leaves = $total_sick_leaves;

                // Privileged Leaves Logic (Requires > 1 year usually, or handled here)
                if ($yearsOfService >= 1) {
                    $allotted_privileged_leaves = $total_privileged_leaves;

                    // Helper logic for people who completed 1 year specifically to adjust? 
                    // The original code had: if ($yearsOfService == 1 && $joinYear == $currentYear - 1)
                    // That was likely to pro-rate the *previous* year's credit? Or just specific rule.
                    // I will assume standard full allocation for > 1 year.
                } else {
                    // Joined last year but hasn't completed 1 full year yet (e.g. joined Dec 31, today Jan 1)
                    // Usually they get full casual/sick for the new calendar year.
                    $allotted_privileged_leaves = 0;
                }
            }

            // Carry forward logic (Only for Privileged Leaves, if eligible)
            $privileged_carry_forward = 0;

            if ($yearsOfService >= 1 && $currentYear > $joinYear) {
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

    public function getCalendarEvents(Request $request)
    {
        $query = LeaveRequest::with(['user']);

        if ($request->has('my_requests')) {
            $query->where('user_id', Auth::id());
        } else {
            if (Auth::user()->employee) {
                $currentEmployeeId = Auth::id();
                $currentUserId = Auth::user()->employee->user_id;
                $reportingEmployeeUserIds = Employee::where('reporting_to', $currentEmployeeId)->pluck('user_id');
                $query->where(function ($q) use ($reportingEmployeeUserIds, $currentUserId) {
                    $q->whereIn('user_id', $reportingEmployeeUserIds)
                        ->orWhere('forwarded_to_employee_id', $currentUserId);
                });
            } elseif (Auth::user()->user_type !== 'admin') {
                $query->where('user_id', Auth::id());
            }
        }

        // Apply filters if present
        if ($request->employee_id) {
            $query->where('user_id', $request->employee_id);
        }
        if ($request->leave_type) {
            $query->where('leave_type', $request->leave_type);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $leaves = $query->get();
        $events = [];

        foreach ($leaves as $leave) {
            $colorClass = 'bg-primary';
            switch ($leave->leave_type) {
                case 'casual':
                    $colorClass = 'bg-info';
                    break;
                case 'sick':
                    $colorClass = 'bg-warning';
                    break;
                case 'paid':
                    $colorClass = 'bg-success';
                    break;
                case 'unpaid':
                    $colorClass = 'bg-danger';
                    break;
                case 'compensatory':
                    $colorClass = 'bg-primary';
                    break;
                default:
                    $colorClass = 'bg-secondary';
            }

            // Adjust end date for FullCalendar (exclusive) if full day
            // But FullCalendar handles inclusive end dates if we format correctly? 
            // Usually standard fullcalendar expects end date to be exclusive for allDay events.
            // But our dates are datetime strings 'Y-m-d H:i:s'.
            // If it's a full day, we might want to add 1 day to end_date for display?
            // Or just rely on the stored dates.
            // Let's stick to stored dates first.

            $title = $leave->user->name . ' - ' . ucfirst($leave->leave_type);
            if ($request->has('my_requests')) {
                $title = ucfirst($leave->leave_type); // Just type for own view maybe? Or keep generic.
            }

            $events[] = [
                'id' => $leave->id,
                'title' => $title,
                'start' => $leave->start_date,
                'end' => Carbon::parse($leave->end_date)->addDay()->format('Y-m-d'), // Add 1 day for inclusive end date in FullCalendar
                'className' => $colorClass,
                'description' => $leave->reason,
                'extendedProps' => [
                    'status' => $leave->status,
                    'leave_type' => $leave->leave_type
                ]
            ];
        }

        return response()->json($events);
    }

    public function getLeaveBalances()
    {
        $user = Auth::user();
        $leaveData = $this->calculateAllottedAndTakenLeaves($user);

        if (!$leaveData) {
            return response()->json([
                'casual' => ['allotted' => 0, 'taken' => 0, 'remaining' => 0],
                'sick' => ['allotted' => 0, 'taken' => 0, 'remaining' => 0],
                'paid' => ['allotted' => 0, 'taken' => 0, 'remaining' => 0],
                'unpaid' => ['taken' => 0]
            ]);
        }

        $leaveBalances = [
            'casual' => [
                'allotted' => $leaveData['casual']['allotted'],
                'taken' => $leaveData['casual']['taken'],
                'remaining' => max(0, $leaveData['casual']['allotted'] - $leaveData['casual']['taken'])
            ],
            'sick' => [
                'allotted' => $leaveData['sick']['allotted'],
                'taken' => $leaveData['sick']['taken'],
                'remaining' => max(0, $leaveData['sick']['allotted'] - $leaveData['sick']['taken'])
            ],
            'paid' => [
                'allotted' => $leaveData['paid']['allotted'],
                'taken' => $leaveData['paid']['taken'],
                'remaining' => max(0, $leaveData['paid']['allotted'] - $leaveData['paid']['taken'])
            ],
            'unpaid' => [
                'taken' => $leaveData['unpaid']['taken']
            ]
        ];

        return response()->json($leaveBalances);
    }
}
