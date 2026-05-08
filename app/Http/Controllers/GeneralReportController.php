<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\LeaveRequest;
use App\Models\ExpenseRequest;
use App\Models\DocumentRequest;
use App\Models\LoanRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GeneralReportExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class GeneralReportController extends Controller
{
    public function index()
    {
        if (!checkMenu(Session::get('role_id'), 34, 'read')) {
            abort(403);
        }

        $employees = User::orderBy('name')->get();
        return view('reports.general.index', compact('employees'));
    }

    public function getData(Request $request)
    {
        $type = $request->type;
        $data = $this->getFilteredData($request);

        // Transform data for display
        $results = $data->map(function ($item) use ($type) {
            $common = [
                'id' => $item->id,
                'employee' => $item->user->name ?? 'N/A',
                'status' => $item->status,
            ];

            switch ($type) {
                case 'leave':
                    return array_merge($common, [
                        'date' => Carbon::parse($item->start_date)->format('d M, Y') . ' - ' . Carbon::parse($item->end_date)->format('d M, Y'),
                        'type' => $item->leave_type,
                        'details' => $item->reason,
                    ]);
                case 'expense':
                    return array_merge($common, [
                        'date' => Carbon::parse($item->date)->format('d M, Y'),
                        'type' => $item->expense_type,
                        'details' => $item->description . ' (' . number_format($item->amount, 2) . ')',
                        'amount' => $item->amount
                    ]);
                case 'legacy_expense':
                    return array_merge($common, [
                        'date' => Carbon::parse($item->date)->format('d M, Y'),
                        'raw_date' => $item->date,
                        'user_id' => $item->user_id,
                        'type' => $item->expense_type,
                        'details' => $item->description,
                        'amount' => $item->amount,
                        'approved_amount' => $item->approved_amount ?? 0
                    ]);
                case 'document':
                    return array_merge($common, [
                        'date' => Carbon::parse($item->requested_date)->format('d M, Y'),
                        'type' => $item->documentType->name ?? 'N/A',
                        'details' => $item->remarks,
                    ]);
                case 'loan':
                    return array_merge($common, [
                        'date' => Carbon::parse($item->requested_on)->format('d M, Y'),
                        'type' => 'Loan',
                        'details' => 'Amount: ' . number_format($item->amount, 2),
                        'amount' => $item->amount
                    ]);
                default:
                    return $common;
            }
        });

        return response()->json(['data' => $results]);
    }

    public function exportExcel(Request $request)
    {
        $type = $request->type;
        if ($type === 'legacy_expense') {
            return Excel::download(
                new \App\Exports\LegacyWeeklyExpenseExport($request->start_date, $request->end_date, $request->employee_id),
                'weekly_expense_report_' . date('Y_m_d_H_i_s') . '.xlsx'
            );
        }

        $data = $this->getFilteredData($request);
        return Excel::download(new GeneralReportExport($data, $type), 'general_report_' . $type . '_' . date('Y_m_d_H_i_s') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getFilteredData($request);
        $type = $request->type;

        $pdf = Pdf::loadView('reports.general.pdf', compact('data', 'type'));

        if ($type === 'legacy_expense') {
            $pdf->setPaper('a4', 'landscape');
        }

        $filename = 'report_' . $type . '_' . date('Y_m_d');
        if ($request->filled('employee_id')) {
            $employee = User::find($request->employee_id);
            if ($employee) {
                $filename = str_replace(' ', '_', strtolower($employee->name)) . '_' . $type . '_' . date('Y_m_d');
            }
        }

        return $pdf->download($filename . '.pdf');
    }

    private function getFilteredData(Request $request)
    {
        $type = $request->type;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $employeeId = $request->employee_id;

        $query = null;
        $dateField = '';

        switch ($type) {
            case 'leave':
                $query = LeaveRequest::with('user');
                $dateField = 'start_date';
                break;
            case 'expense':
                $query = ExpenseRequest::with('user');
                $dateField = 'date';
                break;
            case 'legacy_expense':
                $query = ExpenseRequest::with('user');
                $dateField = 'date';
                break;
            case 'document':
                $query = DocumentRequest::with(['user', 'documentType']);
                $dateField = 'requested_date';
                break;
            case 'loan':
                $query = LoanRequest::with('user');
                $dateField = 'requested_on';
                break;
            default:
                abort(400, 'Invalid report type');
        }

        if ($startDate) {
            $query->whereDate($dateField, '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate($dateField, '<=', $endDate);
        }
        if ($employeeId) {
            $query->where('user_id', $employeeId);
        }

        return $query->get();
    }
}
