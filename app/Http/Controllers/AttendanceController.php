<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Clock;
use App\Models\Task;
use App\Models\LeaveRequest;
use App\Models\Dealership;
use App\Models\Department;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public function exportExcel(Request $request)
    {
        $user = auth()->user();
        $userEmployee = $user->employee;
        $roleId = session('role_id') ?? ($userEmployee ? $userEmployee->role_id : null);
        $canViewSubordinates = checkMenu($roleId, 36, 'read');
        $canViewAllAttendance = checkMenu($roleId, 37, 'read');

        $filters = $request->only(['from_date', 'to_date', 'employee_id', 'dealership_id', 'department_id']);

        if (!$canViewSubordinates) {
            $filters['employee_id'] = $userEmployee->id ?? 0;
            unset($filters['dealership_id']);
            unset($filters['department_id']);
        } elseif (!$canViewAllAttendance) {
            if ($userEmployee && $userEmployee->dealership_id) {
                $filters['dealership_id'] = $userEmployee->dealership_id;
            }
            if ($userEmployee && $userEmployee->department_id) {
                $filters['department_id'] = $userEmployee->department_id;
            }
        }

        return Excel::download(new AttendanceExport($filters), 'attendance_' . date('Y_m_d_H_i_s') . '.xlsx');
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $userEmployee = $user->employee;
        $userDealershipId = $userEmployee ? $userEmployee->dealership_id : null;
        $userDepartmentId = $userEmployee ? $userEmployee->department_id : null;
        $roleId = session('role_id') ?? ($userEmployee ? $userEmployee->role_id : null);

        // Check for 'Subordinates Attendance' (Menu ID: 36)
        $canViewSubordinates = checkMenu($roleId, 36, 'read');
        $canViewAllAttendance = checkMenu($roleId, 37, 'read');

        if ($request->ajax()) {
            $date = $request->input('date', Carbon::today()->toDateString());
            $employeeId = $request->input('employee_id');
            $dealershipId = $request->input('dealership_id');
            $departmentId = $request->input('department_id');

            // If user has fixed dealership/department, override filters
            if ($userDealershipId && !$canViewAllAttendance) {
                $dealershipId = $userDealershipId;
            }
            if ($userDepartmentId && !$canViewAllAttendance) {
                $departmentId = $userDepartmentId;
            }

            $employeesQuery = Employee::select('id', 'name', 'profile_pic', 'designation', 'department_id', 'user_id', 'dealership_id')
                ->with('department');

            if (!$canViewSubordinates) {
                // If they can't view subordinates, they can only see their own attendance
                $employeesQuery->where('id', $userEmployee->id ?? 0);
            } else {
                if (!empty($employeeId)) {
                    $employeesQuery->where('id', $employeeId);
                }

                if (!empty($dealershipId)) {
                    $employeesQuery->where('dealership_id', $dealershipId);
                }

                if (!empty($departmentId)) {
                    $employeesQuery->where('department_id', $departmentId);
                }
            }

            $employees = $employeesQuery->get();

            $attendanceData = $employees->map(function ($employee) use ($date) {
                // Check for approved leave
                $leave = LeaveRequest::whereHas('user', function ($q) use ($employee) {
                    $q->where('id', $employee->user_id);
                })
                    ->where('status', 'approved')
                    ->whereDate('start_date', '<=', $date)
                    ->whereDate('end_date', '>=', $date)
                    ->first();

                $clock = Clock::where('employee_id', $employee->id)
                    ->whereDate('clock_in_time', $date)
                    ->first();

                $totalTaskTime = Task::where('assigned_to', $employee->id)
                    ->whereDate('start_date_time', $date)
                    ->sum('total_elapsed_time');

                $status = 'Absent';
                $remarks = 'N/A';

                if ($clock) {
                    $status = 'Present';
                    $remarks = $clock->remarks;
                } elseif ($leave) {
                    $status = 'On Leave (' . $leave->leave_type . ')';
                    $remarks = $leave->reason;
                } else {
                    // Check for compensatory work
                    $compensatoryWork = LeaveRequest::whereHas('user', function ($q) use ($employee) {
                        $q->where('id', $employee->user_id);
                    })
                        ->where('status', 'approved')
                        ->where('is_compensatory', true)
                        ->whereDate('compensatory_date', $date)
                        ->first();

                    if ($compensatoryWork) {
                        $status = 'Present (Compensatory Work)';
                        $remarks = 'Worked on holiday/weekend against leave on ' . Carbon::parse($compensatoryWork->start_date)->format('d M Y');
                    }
                }

                return [
                    'employee_id' => $employee->id,
                    'employee_name' => $employee->name,
                    'profile_pic' => $employee->profile_pic,
                    'designation' => $employee->designation,
                    'department_name' => $employee->department ? $employee->department->name : 'N/A',
                    'clock_in_time' => $clock ? Carbon::parse($clock->clock_in_time)->format('h:i A') : '-',
                    'clock_out_time' => $clock && $clock->clock_out_time ? Carbon::parse($clock->clock_out_time)->format('h:i A') : '-',
                    'remarks' => $remarks,
                    'status' => $status,
                    'total_task_time' => $this->formatElapsedTime($totalTaskTime),
                    'clock_in_latitude' => $clock ? $clock->clock_in_latitude : null,
                    'clock_in_longitude' => $clock ? $clock->clock_in_longitude : null,
                    'clock_out_latitude' => $clock ? $clock->clock_out_latitude : null,
                    'clock_out_longitude' => $clock ? $clock->clock_out_longitude : null,
                    'is_leave' => (bool)$leave,
                ];
            });

            $filterType = $request->input('filter_type', 'attendees');

            if ($filterType === 'attendees') {
                $attendanceData = $attendanceData->filter(function ($item) {
                    return strpos($item['status'], 'Present') !== false;
                });
            } elseif ($filterType === 'absents') {
                $attendanceData = $attendanceData->filter(function ($item) {
                    return $item['status'] === 'Absent' || strpos($item['status'], 'On Leave') !== false;
                });
            }

            return DataTables::of($attendanceData)
                ->addIndexColumn()
                ->make(true);
        }

        $employeesQuery = Employee::orderBy('name');
        if (!$canViewSubordinates && !$canViewAllAttendance) {
            $employeesQuery->where('id', $userEmployee->id ?? 0);
        }
        $employees = $employeesQuery->get();

        $dealerships = Dealership::where('brand', 1)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        return view('hr.attendance.index', compact('employees', 'dealerships', 'departments', 'userDealershipId', 'userDepartmentId', 'canViewSubordinates', 'canViewAllAttendance'));
    }

    public function calendar()
    {
        $user = auth()->user();
        $userEmployee = $user->employee;
        $userDealershipId = $userEmployee ? $userEmployee->dealership_id : null;
        $userDepartmentId = $userEmployee ? $userEmployee->department_id : null;
        $roleId = session('role_id') ?? ($userEmployee ? $userEmployee->role_id : null);

        // Check for 'Subordinates Attendance' (Menu ID: 36)
        $canViewSubordinates = checkMenu($roleId, 36, 'read');
        $canViewAllAttendance = checkMenu($roleId, 37, 'read');

        $employeesQuery = Employee::orderBy('name');
        if (!$canViewSubordinates) {
            $employeesQuery->where('id', $userEmployee->id ?? 0);
        }
        $employees = $employeesQuery->get();

        $dealerships = Dealership::where('brand', 1)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        return view('hr.attendance.calendar', compact('employees', 'dealerships', 'departments', 'userDealershipId', 'userDepartmentId', 'canViewSubordinates', 'canViewAllAttendance'));
    }

    public function getCalendarEvents(Request $request)
    {
        $user = auth()->user();
        $userEmployee = $user->employee;
        $userDealershipId = $userEmployee ? $userEmployee->dealership_id : null;
        $userDepartmentId = $userEmployee ? $userEmployee->department_id : null;
        $roleId = session('role_id') ?? ($userEmployee ? $userEmployee->role_id : null);

        // Check for 'Subordinates Attendance' (Menu ID: 36)
        $canViewSubordinates = checkMenu($roleId, 36, 'read');
        $canViewAllAttendance = checkMenu($roleId, 37, 'read');

        $start = $request->input('start');
        $end = $request->input('end');
        $employeeId = $request->input('employee_id');

        if (!$canViewSubordinates) {
            $employeeId = $userEmployee->id ?? 0;
            $dealershipId = null;
            $departmentId = null;
        } else {
            if ($canViewAllAttendance) {
                $dealershipId = $request->input('dealership_id');
                $departmentId = $request->input('department_id');
            } else {
                $dealershipId = $userDealershipId ?: $request->input('dealership_id');
                $departmentId = $userDepartmentId ?: $request->input('department_id');
            }
        }

        $events = [];

        // 1. Fetch Attendance (Clocks)
        $clocksQuery = Clock::with('employee')
            ->whereBetween('clock_in_time', [$start, $end]);

        if (!empty($employeeId)) {
            $clocksQuery->where('employee_id', $employeeId);
        }

        if (!empty($dealershipId)) {
            $clocksQuery->whereHas('employee', function ($q) use ($dealershipId) {
                $q->where('dealership_id', $dealershipId);
            });
        }

        if (!empty($departmentId)) {
            $clocksQuery->whereHas('employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $clocks = $clocksQuery->get();

        foreach ($clocks as $clock) {
            $events[] = [
                'title' => ($clock->employee ? $clock->employee->name : 'Unknown') . ' (Present)',
                'start' => Carbon::parse($clock->clock_in_time)->toIso8601String(),
                'end' => $clock->clock_out_time ? Carbon::parse($clock->clock_out_time)->toIso8601String() : null,
                'color' => '#28a745', // Green
                'extendedProps' => [
                    'type' => 'attendance',
                    'employee_id' => $clock->employee_id
                ]
            ];
        }

        // 2. Fetch Approved Leaves
        // Leaves are linked to Users, Users are linked to Employees.
        $leavesQuery = LeaveRequest::with(['user.employee'])
            ->where('status', 'approved')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('start_date', [$start, $end])
                    ->orWhereBetween('end_date', [$start, $end])
                    ->orWhere(function ($sq) use ($start, $end) {
                        $sq->where('start_date', '<', $start)
                            ->where('end_date', '>', $end);
                    });
            });

        if (!empty($employeeId)) {
            $leavesQuery->whereHas('user.employee', function ($q) use ($employeeId) {
                $q->where('id', $employeeId);
            });
        }

        if (!empty($dealershipId)) {
            $leavesQuery->whereHas('user.employee', function ($q) use ($dealershipId) {
                $q->where('dealership_id', $dealershipId);
            });
        }

        if (!empty($departmentId)) {
            $leavesQuery->whereHas('user.employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $leaves = $leavesQuery->get();

        foreach ($leaves as $leave) {
            $employee = $leave->user->employee ?? null;
            $name = $employee ? $employee->name : ($leave->user->name ?? 'Unknown');

            $events[] = [
                'title' => $name . ' (' . $leave->leave_type . ')',
                'start' => $leave->start_date->format('Y-m-d'),
                'end' => $leave->end_date->addDay()->format('Y-m-d'), // FullCalendar end date is exclusive
                'color' => '#ffc107', // Orange/Yellow
                'textColor' => '#000000',
                'allDay' => true,
                'extendedProps' => [
                    'type' => 'leave',
                    'description' => $leave->reason
                ]
            ];
        }

        // 3. Fetch Compensatory Work Days
        $compensatoryWorksQuery = LeaveRequest::with(['user.employee'])
            ->where('status', 'approved')
            ->where('is_compensatory', true)
            ->whereBetween('compensatory_date', [$start, $end]);

        if (!empty($employeeId)) {
            $compensatoryWorksQuery->whereHas('user.employee', function ($q) use ($employeeId) {
                $q->where('id', $employeeId);
            });
        }

        if (!empty($dealershipId)) {
            $compensatoryWorksQuery->whereHas('user.employee', function ($q) use ($dealershipId) {
                $q->where('dealership_id', $dealershipId);
            });
        }

        if (!empty($departmentId)) {
            $compensatoryWorksQuery->whereHas('user.employee', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $compensatoryWorks = $compensatoryWorksQuery->get();

        foreach ($compensatoryWorks as $work) {
            $employee = $work->user->employee ?? null;
            $name = $employee ? $employee->name : ($work->user->name ?? 'Unknown');

            $events[] = [
                'title' => $name . ' (Present - Comp Work)',
                'start' => Carbon::parse($work->compensatory_date)->format('Y-m-d'),
                'end' => Carbon::parse($work->compensatory_date)->addDay()->format('Y-m-d'),
                'color' => '#28a745', // Green (Same as attendance)
                'textColor' => '#ffffff',
                'allDay' => true,
                'extendedProps' => [
                    'type' => 'compensatory_work',
                    'description' => 'Worked on holiday/weekend against leave on ' . Carbon::parse($work->start_date)->format('d M Y')
                ]
            ];
        }

        return response()->json($events);
    }

    private function formatElapsedTime($totalSeconds)
    {
        if ($totalSeconds === null || $totalSeconds === 0) {
            return '00:00:00';
        }
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    public function show(Request $request, $employeeId)
    {
        $date = $request->input('date', Carbon::today()->toDateString());

        $employee = Employee::with('department')->findOrFail($employeeId);

        $clock = Clock::where('employee_id', $employeeId)
            ->whereDate('clock_in_time', $date)
            ->first();

        $tasks = Task::where('assigned_to', $employeeId)
            ->whereDate('start_date_time', $date)
            ->with(['taskLogs' => function ($query) {
                $query->orderBy('action_time', 'asc');
            }])
            ->get();

        $details = $tasks->map(function ($task) {
            $breaks = [];
            $workIntervals = [];
            $currentInterval = null;

            foreach ($task->taskLogs as $log) {
                if (in_array($log->action_type, ['started', 'resumed'])) {
                    if ($currentInterval === null) {
                        $currentInterval = ['start' => Carbon::parse($log->action_time)];
                    }
                } elseif (in_array($log->action_type, ['paused', 'stopped']) && $currentInterval !== null) {
                    $currentInterval['end'] = Carbon::parse($log->action_time);
                    $workIntervals[] = $currentInterval;
                    $currentInterval = null;
                }
            }

            // Calculate breaks between work intervals
            for ($i = 0; $i < count($workIntervals) - 1; $i++) {
                $breakStart = $workIntervals[$i]['end'];
                $breakEnd = $workIntervals[$i + 1]['start'];
                $breakDuration = $breakEnd->diffInSeconds($breakStart);

                // Add validation for breakDuration
                if ($breakDuration < 0) {
                    $breaks[] = [
                        'start' => $breakStart->format('h:i A'),
                        'end' => $breakEnd->format('h:i A'),
                        'duration' => 'N/A', // Display N/A for invalid breaks
                    ];
                    continue; // Skip to next iteration
                }

                $breaks[] = [
                    'start' => $breakStart->format('h:i A'),
                    'end' => $breakEnd->format('h:i A'),
                    'duration' => $this->formatElapsedTime($breakDuration),
                ];
            }

            return [
                'task_title' => $task->title,
                'total_time' => $this->formatElapsedTime($task->getElapsedTimeInSeconds()),
                'breaks' => $breaks,
            ];
        });

        return view('hr.attendance.show', compact('employee', 'details', 'date', 'clock'));
    }
}
