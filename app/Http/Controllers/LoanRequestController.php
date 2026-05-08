<?php

namespace App\Http\Controllers;

use App\Models\LoanRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class LoanRequestController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = LoanRequest::with(['user', 'forwardedToEmployee.employee.department']);

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
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }



            if ($request->filled('start_date')) {
                $query->whereDate('requested_on', '>=', $request->start_date);
            }

            if ($request->filled('end_date')) {
                $query->whereDate('requested_on', '<=', $request->end_date);
            }

            return DataTables::of($query->get()->map(function ($loanRequest) {
                $loanRequest->action = ''; // Initialize action to an empty string
                return $loanRequest;
            }))
                ->addIndexColumn()
                ->addColumn('user', function ($row) {
                    return $row->user->name;
                })
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    // View Button
                    $buttons .= ' <button class="btn btn-sm btn-primary view-loan-request" data-id="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#viewLoanRequestModal"><i class="fas fa-eye"></i></button>';

                    // Only allow delete for the user who created it or admin
                    if ($row->user_id == Auth::id() || Auth::user()->user_type === 'admin') {
                        $buttons .= ' <button class="btn btn-sm btn-danger delete-loan-request" data-id="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#deleteLoanRequestModal"><i class="fas fa-trash"></i></button>';
                    }
                    return $buttons;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('requests.loan.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|min:0',
        ]);

        LoanRequest::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'requested_on' => now(),
            'status' => 'pending',
        ]);

        return redirect()->route('loan-requests.index')->with('success', 'Loan request submitted successfully.');
    }

    public function show(LoanRequest $loanRequest)
    {
        $loanRequest->load('user'); // Eager load the user relationship
        return response()->json($loanRequest);
    }

    public function destroy(LoanRequest $loanRequest)
    {
        if ($loanRequest->user_id != Auth::id() && Auth::user()->user_type !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $loanRequest->delete();

        return redirect()->route('loan-requests.index')->with('success', 'Loan request deleted successfully.');
    }

    public function changeStatus(Request $request, LoanRequest $loanRequest)
    {
        // if (Auth::user()->user_type !== 'admin' && !(Auth::user()->employee && Auth::user()->employee->is_manager) && !($loanRequest->forwarded_to_employee_id === Auth::id())) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected,processed,forwarded,approved and forwarded',
            'forwarded_to_employee_id' => 'nullable|exists:users,id',
        ]);

        $loanRequest->status = $request->status;

        if ($request->status === 'approved and forwarded') {
            $loanRequest->forwarded_to_employee_id = $request->forwarded_to_employee_id;
        } else {
            $loanRequest->forwarded_to_employee_id = null;
        }

        $loanRequest->save();
        return response()->json(['message' => 'Loan request status updated successfully.']);
    }

    public function searchEmployees(Request $request)
    {
        $term = $request->input('q');
        $employees = Employee::where('name', 'like', '%' . $term . '%')->paginate(10);

        return response()->json([
            'data' => $employees->through(function ($employee) {
                return ['id' => $employee->user_id, 'text' => $employee->name];
            }),
            'total' => $employees->total(),
        ]);
    }

    public function getCalendarEvents(Request $request)
    {
        $query = LoanRequest::with(['user']);

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

        // Filters
        if ($request->employee_id) $query->where('user_id', $request->employee_id);
        if ($request->status) $query->where('status', $request->status);
        if ($request->start) $query->whereDate('requested_on', '>=', $request->start);
        if ($request->end) $query->whereDate('requested_on', '<=', $request->end);

        $loans = $query->get();
        $events = [];

        foreach ($loans as $loan) {
            $title = $loan->user->name . ' - Loan (' . $loan->amount . ')';
            if ($request->has('my_requests')) {
                $title = 'Loan Request (' . $loan->amount . ')';
            }

            $colorClass = 'bg-warning';
            switch ($loan->status) {
                case 'approved':
                    $colorClass = 'bg-success';
                    break;
                case 'rejected':
                    $colorClass = 'bg-danger';
                    break;
                case 'processed':
                    $colorClass = 'bg-info';
                    break;
                case 'forwarded':
                    $colorClass = 'bg-primary';
                    break;
                case 'approved and forwarded':
                    $colorClass = 'bg-secondary';
                    break;
            }

            $events[] = [
                'id' => $loan->id,
                'title' => $title,
                'start' => $loan->requested_on,
                'className' => $colorClass,
                'extendedProps' => [
                    'status' => $loan->status,
                    'amount' => $loan->amount
                ]
            ];
        }
        return response()->json($events);
    }
}
