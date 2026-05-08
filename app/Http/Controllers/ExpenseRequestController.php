<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\ExpenseRequest;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\MockObject\Rule\Parameters;

class ExpenseRequestController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ExpenseRequest::with(['user', 'forwardedToEmployee.employee.department']);

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

            // dd($query->toSql(), $query->getBindings());

            // Handle filters
            if ($request->filled('employee_id')) {
                $query->where('user_id', $request->employee_id);
            }
            if ($request->filled('expense_type')) {
                $query->where('expense_type', $request->expense_type);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('approved_amount', function ($row) {
                    return $row->approved_amount ?? '-';
                })
                ->addColumn('action', function ($row, Request $request) {
                    $buttons = '';
                    $expenseRequestId = (int) $row->id; // Explicitly cast to integer

                    if (Auth::id() === $row->user_id && $row->status === 'pending') {
                        $buttons .= '<button class="btn btn-sm btn-success edit-expense-request-btn" data-id="' . $expenseRequestId . '" data-bs-toggle="modal" data-bs-target="#editExpenseRequestModal"><i class="fas fa-edit"></i></button>';
                    }
                    if (!$request->has('my_requests')) {
                        /*
                        if (Auth::user()->user_type === 'admin' || (Auth::user()->employee && Auth::user()->employee->is_manager)) {
                            $buttons .= ' <button class="btn btn-sm btn-info change-status-expense-request" data-id="' . $expenseRequestId . '" data-bs-toggle="modal" data-bs-target="#changeStatusExpenseRequestModal"><i class="fas fa-check"></i></button>';
                        }
                        */
                    }
                    $buttons .= ' <button class="btn btn-sm btn-primary view-expense-request" data-id="' . $expenseRequestId . '" data-bs-toggle="modal" data-bs-target="#viewExpenseRequestModal" title="View Details"><i class="fas fa-eye"></i></button>';

                    $legacyExportUrl = route('expense-requests.export-legacy-pdf', [
                        'week_date' => $row->date,
                        'employee_id' => $row->user_id
                    ]);
                    $buttons .= ' <a href="' . $legacyExportUrl . '" class="btn btn-sm btn-dark" target="_blank" title="Legacy Weekly Report"><i class="fas fa-file-invoice"></i></a>';

                    if (!empty($row->image)) { // Only show view image button if an image exists
                        // $row->image is an accessor that returns an array of images. Use the first one.
                        $imagePath = $row->image[0];
                        $buttons .= ' <button class="btn btn-sm btn-secondary view-expense-image" data-id="' . $expenseRequestId . '" data-image-path="' . Storage::url($imagePath) . '" data-bs-toggle="modal" data-bs-target="#viewExpenseImageModal"><i class="fas fa-image"></i></button>';
                    }
                    if ($row->user_id == Auth::id() || Auth::user()->user_type === 'admin') {
                        $buttons .= ' <button class="btn btn-sm btn-danger delete-expense-request-btn" data-id="' . $expenseRequestId . '" data-bs-toggle="modal" data-bs-target="#deleteExpenseRequestModal"><i class="fas fa-trash"></i></button>';
                    }
                    return $buttons;
                })
                ->editColumn('status', function ($row) {
                    // Return only the raw status for the dropdown in JS
                    return $row->status;
                })
                ->addColumn('forwarded_info_html', function ($row) {
                    $forwardedToInfo = null;
                    if ($row->forwardedToEmployee) {
                        if ($row->forwardedToEmployee->employee) {
                            $forwardedToEmployeeName = $row->forwardedToEmployee->employee->name;
                            $forwardedToEmployeeDepartment = $row->forwardedToEmployee->employee->department ? $row->forwardedToEmployee->employee->department->name : null;
                            $forwardedToInfo = $forwardedToEmployeeName;
                            if ($forwardedToEmployeeDepartment) {
                                $forwardedToInfo .= ' (' . $forwardedToEmployeeDepartment . ')';
                            }
                        } else {
                            // Fallback to user's name if no associated employee record
                            $forwardedToInfo = $row->forwardedToEmployee->name;
                        }
                    }

                    if ($forwardedToInfo) {
                        return '<br><small>Forwarded to: ' . $forwardedToInfo . '</small>';
                    }
                    return '';
                })
                ->rawColumns(['action', 'forwarded_info_html'])
                ->addColumn('raw_status', function ($row) {
                    return $row->status;
                })
                ->make(true);
        }

        return view('requests.expenses.index');
    }


    public function store(Request $request)
    {
        $request->validate([
            'expense_type' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string', // Added validation for description
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('expense_images', 'public');
        }

        ExpenseRequest::create([
            'user_id' => Auth::id(),
            'expense_type' => $request->expense_type,
            'amount' => $request->amount,
            'date' => $request->date,
            'description' => $request->description, // Added saving description
            'image' => $imagePath,
            'status' => 'pending',
        ]);

        return redirect()->route('expense-requests.index')->with('success', 'Expense request submitted successfully.');
    }

    public function update(Request $request, ExpenseRequest $expenseRequest)
    {
        $request->validate([
            'expense_type' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string', // Added validation for description
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $imagePath = $expenseRequest->image;
        if ($request->hasFile('image')) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $request->file('image')->store('expense_images', 'public');
        }

        $expenseRequest->update([
            'expense_type' => $request->expense_type,
            'amount' => $request->amount,
            'date' => $request->date,
            'description' => $request->description, // Added updating description
            'image' => $imagePath,
        ]);

        return redirect()->route('expense-requests.index')->with('success', 'Expense request updated successfully.');
    }

    public function show(ExpenseRequest $expenseRequest)
    {
        $expenseRequest->load('user.employee.reporter'); // Corrected relationship name
        return response()->json($expenseRequest);
    }

    public function edit(ExpenseRequest $expenseRequest)
    {
        return response()->json($expenseRequest);
    }



    public function destroy(ExpenseRequest $expenseRequest)
    {
        if ($expenseRequest->user_id != Auth::id() && Auth::user()->user_type !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $expenseRequest->delete();

        return response()->json(['message' => 'Expense request deleted successfully.']);
    }



    public function searchEmployees(Request $request)
    {
        //if current route is settlements set no pagination
        if (request()->routeIs('settlements.index')) {
            $employees = Employee::where('name', 'like', '%' . $request->q . '%')->get();
        } else {
            $employees = Employee::where('name', 'like', '%' . $request->q . '%')->paginate(10);
        }

        return response()->json([
            'data' => $employees->through(function ($employee) {
                return ['id' => $employee->user_id, 'text' => $employee->name];
            }),
            'total' => $employees->total(),
        ]);
    }
    public function changeStatus(Request $request, ExpenseRequest $expenseRequest)
    {


        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected,processed,approved and forwarded',
            'forwarded_to_employee_id' => 'nullable|exists:users,id',
            'approved_amount' => 'required_if:status,approved,approved and forwarded|nullable|numeric|min:0',
        ]);

        $expenseRequest->status = $request->status;

        if (in_array($request->status, ['approved', 'approved and forwarded'])) {
            $expenseRequest->approved_amount = $request->approved_amount;
        }

        if ($request->status === 'approved and forwarded') {
            $expenseRequest->forwarded_to_employee_id = $request->forwarded_to_employee_id;
        } else {
            $expenseRequest->forwarded_to_employee_id = null;
        }

        $expenseRequest->save();
        return response()->json(['message' => 'Expense request status updated.']);
    }

    public function updateStatus(Request $request, LeaveRequest $leaveRequest)
    {


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

    public function getCalendarEvents(Request $request)
    {
        $query = ExpenseRequest::with(['user']);

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

        // Filters
        if ($request->employee_id) $query->where('user_id', $request->employee_id);
        if ($request->expense_type) $query->where('expense_type', $request->expense_type);
        if ($request->status) $query->where('status', $request->status);
        if ($request->start) $query->whereDate('date', '>=', $request->start);
        if ($request->end) $query->whereDate('date', '<=', $request->end);

        $expenses = $query->get();
        $events = [];

        foreach ($expenses as $expense) {
            $title = $expense->user->name . ' - ' . ucfirst($expense->expense_type) . ' (' . $expense->amount . ')';
            if ($request->has('my_requests')) {
                $title = ucfirst($expense->expense_type) . ' (' . $expense->amount . ')';
            }

            $colorClass = 'bg-primary';
            switch ($expense->expense_type) {
                case 'travel':
                    $colorClass = 'bg-info';
                    break;
                case 'food':
                    $colorClass = 'bg-warning';
                    break;
                case 'accommodation':
                    $colorClass = 'bg-success';
                    break;
                default:
                    $colorClass = 'bg-secondary';
            }

            $events[] = [
                'id' => $expense->id,
                'title' => $title,
                'start' => $expense->date,
                'className' => $colorClass,
                'description' => $expense->description,
                'extendedProps' => [
                    'status' => $expense->status,
                    'amount' => $expense->amount
                ]
            ];
        }

        return response()->json($events);
    }
    public function exportPdf(Request $request)
    {
        $query = ExpenseRequest::with(['user', 'forwardedToEmployee.employee.department']);

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

        // Handle filters
        if ($request->filled('employee_id')) {
            $query->where('user_id', $request->employee_id);
        }
        if ($request->filled('expense_type')) {
            $query->where('expense_type', $request->expense_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $expenses = $query->orderBy('created_at', 'desc')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('requests.expenses.pdf', compact('expenses'));

        // Optional: Set paper size and orientation
        $pdf->setPaper('a4', 'landscape');

        return $pdf->download(date('Y_m_d') . '_expense_requests_summary.pdf');
    }

    public function exportLegacyPdf(Request $request)
    {
        $request->validate([
            'week_date' => 'required|date',
            'employee_id' => 'nullable|exists:users,id'
        ]);

        $endDate = \Carbon\Carbon::parse($request->week_date)->endOfWeek(\Carbon\Carbon::SATURDAY);
        $startDate = $endDate->copy()->subDays(6); // Sunday to Saturday

        $employeeId = $request->employee_id ?: Auth::id();
        $employee = User::with('employee.department')->find($employeeId);

        $expenses = ExpenseRequest::where('user_id', $employeeId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get();

        $dates = [];
        for ($i = 0; $i < 7; $i++) {
            $dates[] = $startDate->copy()->addDays($i)->format('d/m');
        }

        $categories = [
            'Daily Allowance' => 'food',
            'Conveyance' => 'travel_local', // Placeholder for local travel if added later
            'Rail / Bus Fare' => 'travel',
            'Coolie Charge' => 'coolie',   // Placeholder
            'Postage' => 'postage',       // Placeholder
            'Miscellaneous' => 'miscellaneous'
        ];

        $matrix = [];
        foreach ($categories as $label => $targetType) {
            $matrix[$label] = array_fill(0, 7, 0);
            foreach ($expenses as $expense) {
                $dayIndex = \Carbon\Carbon::parse($expense->date)->dayOfWeek; // 0 (Sun) to 6 (Sat)

                if ($expense->expense_type == $targetType) {
                    $matrix[$label][$dayIndex] += $expense->amount;
                }
            }
        }

        $weekEnding = $endDate->format('d/m/Y');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('requests.expenses.legacy_pdf', [
            'employee' => $employee,
            'expenses' => $expenses,
            'matrix' => $matrix,
            'dates' => $dates,
            'weekEnding' => $weekEnding,
            'startDate' => $startDate
        ]);

        $pdf->setPaper('a4', 'landscape');

        return $pdf->download($endDate->format('Y_m_d') . '_' . str_replace(' ', '_', strtolower($employee->name)) . '_weekly_travel_report.pdf');
    }

    public function viewLegacyReport(Request $request)
    {
        $request->validate([
            'week_date' => 'required|date',
            'employee_id' => 'nullable|exists:users,id'
        ]);

        $endDate = \Carbon\Carbon::parse($request->week_date)->endOfWeek(\Carbon\Carbon::SATURDAY);
        $startDate = $endDate->copy()->subDays(6);

        $employeeId = $request->employee_id ?: Auth::id();
        $employee = User::with('employee.department')->find($employeeId);

        $expenses = ExpenseRequest::where('user_id', $employeeId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date', 'asc')
            ->get();

        $dates = [];
        for ($i = 0; $i < 7; $i++) {
            $dates[] = $startDate->copy()->addDays($i)->format('d/m');
        }

        $categories = [
            'Daily Allowance' => 'food',
            'Conveyance' => 'travel_local',
            'Rail / Bus Fare' => 'travel',
            'Coolie Charge' => 'coolie',
            'Postage' => 'postage',
            'Miscellaneous' => 'miscellaneous'
        ];

        $matrix = [];
        foreach ($categories as $label => $targetType) {
            $matrix[$label] = array_fill(0, 7, 0);
            foreach ($expenses as $expense) {
                $dayIndex = \Carbon\Carbon::parse($expense->date)->dayOfWeek;
                if ($expense->expense_type == $targetType) {
                    $matrix[$label][$dayIndex] += $expense->amount;
                }
            }
        }

        $weekEnding = $endDate->format('d/m/Y');

        return view('requests.expenses.legacy_pdf', [
            'employee' => $employee,
            'expenses' => $expenses,
            'matrix' => $matrix,
            'dates' => $dates,
            'weekEnding' => $weekEnding,
            'startDate' => $startDate
        ]);
    }
}
