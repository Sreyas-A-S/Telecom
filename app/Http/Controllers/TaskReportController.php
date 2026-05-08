<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\Dealership;
use App\Models\Employee;
use Yajra\DataTables\Facades\DataTables;
use App\Exports\TaskReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use App\Models\Department;
use Barryvdh\DomPDF\Facade\Pdf;


class TaskReportController extends Controller
{
    public function index()
    {
        if (!checkMenu(Session::get('role_id'), 35, 'read')) {
            abort(403);
        }

        $user = auth()->user();
        $userEmployee = $user->employee;
        $userDealershipId = $userEmployee ? $userEmployee->dealership_id : null;
        $userDepartmentId = $userEmployee ? $userEmployee->department_id : null;
        $roleId = session('role_id') ?? ($userEmployee ? $userEmployee->role_id : null);

        // Check for 'Subordinates Attendance' (Menu ID: 36)
        $canViewSubordinates = checkMenu($roleId, 36, 'read');
        $canViewAll = checkMenu($roleId, 39, 'read');

        $employeesQuery = Employee::orderBy('name');
        if ($canViewAll) {
            // No restriction
        } elseif ($canViewSubordinates) {
            // No additional restriction here, but later in getData we use dealership/department
        } else {
            $employeesQuery->where('id', $userEmployee->id ?? 0);
        }
        $employees = $employeesQuery->get();

        $dealerships = Dealership::where('brand', 1)->orderBy('name')->get();
        $departments = Department::orderBy('name')->get();

        return view('reports.tasks.index', compact('dealerships', 'employees', 'departments', 'userDealershipId', 'userDepartmentId', 'canViewSubordinates', 'canViewAll'));
    }

    public function exportExcel(Request $request)
    {
        $user = auth()->user();
        $userEmployee = $user->employee;
        $roleId = session('role_id') ?? ($userEmployee ? $userEmployee->role_id : null);
        $canViewSubordinates = checkMenu($roleId, 36, 'read');
        $canViewAll = checkMenu($roleId, 39, 'read');

        $filters = $request->only(['start_date', 'end_date', 'dealership_id', 'employee_id', 'department_id', 'task_type']);

        if ($canViewAll) {
            // No restriction
        } elseif (!$canViewSubordinates) {
            $filters['employee_id'] = $userEmployee->id ?? 0;
            unset($filters['dealership_id']);
            unset($filters['department_id']);
        }

        return Excel::download(new TaskReportExport($filters), 'task_report_' . date('Y_m_d_H_i_s') . '.xlsx');
    }

    public function show(Task $task)
    {
        if (!checkMenu(Session::get('role_id'), 35, 'read')) {
            abort(403);
        }

        $task->load([
            'assignedEmployee',
            'dealership',
            'entry',
            'lead.items.product',
            'lead.items.productModel',
            'lead.items.modelSeries',
            'fsrReport.submittedBy',
            'fsrReport.partQuotations.part',
            'fsrReport.paymentHistory.collectedBy',
            'followups.user',
            'taskLogs.employee'
        ]);

        return view('reports.tasks.show', compact('task'));
    }

    public function exportFsrPdf(Task $task)
    {
        if (!checkMenu(Session::get('role_id'), 35, 'read')) {
            abort(403);
        }

        $task->load([
            'assignedEmployee',
            'dealership',
            'entry.client',
            'lead.client',
            'lead.items.product',
            'lead.items.productModel',
            'lead.items.modelSeries',
            'fsrReport.submittedBy',
            'fsrReport.partQuotations.part',
            'fsrReport.paymentHistory.collectedBy'
        ]);

        if (!$task->fsrReport) {
            return back()->with('error', 'FSR Report not found for this task.');
        }

        $pdf = Pdf::loadView('reports.tasks.fsr-pdf', compact('task'));
        return $pdf->download('fsr_report_' . $task->id . '.pdf');
    }

    public function getData(Request $request)
    {
        $user = auth()->user();
        $userEmployee = $user->employee;
        $userDealershipId = $userEmployee ? $userEmployee->dealership_id : null;
        $userDepartmentId = $userEmployee ? $userEmployee->department_id : null;
        $roleId = session('role_id') ?? ($userEmployee ? $userEmployee->role_id : null);

        // Permissions check
        $canViewSubordinates = checkMenu($roleId, 36, 'read');
        $canViewAll = checkMenu($roleId, 39, 'read');

        $query = Task::with([
            'assignedEmployee',
            'dealership',
            'entry.client',
            'lead.client',
            'fsrReport',
            'followups.user'
        ])
            ->select('tasks.*')
            ->orderBy('id', 'asc');

        if ($request->filled('start_date')) {
            $query->whereDate('tasks.created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tasks.created_at', '<=', $request->end_date);
        }

        if ($canViewAll) {
            // Filter only if provided
            if ($request->filled('dealership_id')) {
                $query->where('tasks.dealership_id', $request->dealership_id);
            }

            if ($request->filled('employee_id')) {
                $query->where('assigned_to', $request->employee_id);
            }

            if ($request->filled('department_id')) {
                $query->whereHas('assignedEmployee', function ($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            }
        } elseif ($canViewSubordinates) {
            // Keep current behavior for subordinates
            if ($request->filled('dealership_id')) {
                $query->where('tasks.dealership_id', $request->dealership_id);
            } elseif ($userDealershipId) {
                $query->where('tasks.dealership_id', $userDealershipId);
            }

            if ($request->filled('employee_id')) {
                $query->where('assigned_to', $request->employee_id);
            }

            if ($request->filled('department_id')) {
                $query->whereHas('assignedEmployee', function ($q) use ($request) {
                    $q->where('department_id', $request->department_id);
                });
            } elseif ($userDepartmentId) {
                $query->whereHas('assignedEmployee', function ($q) use ($userDepartmentId) {
                    $q->where('department_id', $userDepartmentId);
                });
            }
        } else {
            // Restrict to current user
            $query->where('assigned_to', $userEmployee->id ?? 0);
        }

        if ($request->filled('task_type')) {
            if ($request->task_type === 'leads') {
                $query->whereNotNull('lead_id');
            } elseif ($request->task_type === 'service') {
                $query->where('is_service', 1);
            } elseif ($request->task_type === 'other') {
                $query->whereNull('lead_id')->where(function ($q) {
                    $q->where('is_service', 0)->orWhereNull('is_service');
                });
            }
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('employee_name', function ($row) {
                return $row->assignedEmployee ? $row->assignedEmployee->name : 'Unassigned';
            })
            ->addColumn('client_name', function ($row) {
                if ($row->lead && $row->lead->client) {
                    return $row->lead->client->name;
                }
                if ($row->entry && $row->entry->client) {
                    return $row->entry->client->name;
                }
                if ($row->lead) {
                    return $row->lead->name;
                }
                if ($row->entry) {
                    return $row->entry->name ?? $row->entry->referral_id ?? 'N/A';
                }
                return 'N/A';
            })
            ->addColumn('task_type_label', function ($row) {
                return $row->task_type_label;
            })
            ->addColumn('dealership_name', function ($row) {
                return $row->dealership ? $row->dealership->name : 'N/A';
            })
            ->addColumn('status', function ($row) {
                return $row->derived_status;
            })
            ->addColumn('followups', function ($row) {
                $count = $row->followups->count();
                if ($count === 0) return '0';
                $latest = $row->followups->sortByDesc('created_at')->first();
                return '<span title="Latest: ' . e($latest->notes) . '" class="badge bg-light text-dark border">' . $count . '</span>';
            })
            ->addColumn('formatted_elapsed_time', function ($row) {
                $time = $row->getFormattedElapsedTime();
                if ($row->timer_started_at !== null) {
                    return $time . ' <span class="badge bg-success-subtle text-success border border-success pulse-small ms-1" style="font-size: 0.7rem;">RUNNING</span>';
                }
                return $time;
            })
            ->addColumn('date', function ($row) {
                return $row->created_at->format('d M, Y');
            })
            ->rawColumns(['followups', 'formatted_elapsed_time'])
            ->make(true);
    }
}
