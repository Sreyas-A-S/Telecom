<?php

namespace App\Http\Controllers;

use App\Models\FSRQuotation;
use App\Models\FSRReport; // Assuming FSRReport model exists
use App\Models\Part; // Assuming Part model exists
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

use Yajra\DataTables\Facades\DataTables; // Import DataTables

class FSRQuotationController extends Controller
{
    /**
     * Display a listing of the FSR quotations for a given FSR report.
     */
    public function index()
    {

        return view('fsr_quotations.index');
        // If it's an API call from the FSR create/edit page (client-side rendering)

    }

    /**
     * Display a listing of the FSR quotations for review by parts department.
     */
    public function reviewIndex(Task $task)
    {
        return view('fsr_quotations.index', compact('task'));
    }

    public function generalReviewIndex()
    {
        return view('fsr_quotations.index');
    }

    public function getReviewQuotations(Request $request, Task $task)
    {
        if ($request->ajax()) {
            $query = FSRQuotation::with(['part', 'approver', 'fsrReport.task.assignedEmployee.user', 'fsrReport.submittedBy'])
                ->whereHas('fsrReport', function ($q) use ($task) {
                    $q->where('task_id', $task->id);
                });

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('fsr_report.id', function (FSRQuotation $quotation) {
                    return $quotation->fsrReport ? $quotation->fsrReport->id : 'N/A';
                })
                ->addColumn('fsr_report.task.id', function (FSRQuotation $quotation) {
                    return $quotation->fsrReport && $quotation->fsrReport->task ? $quotation->fsrReport->task->id : 'N/A';
                })
                ->addColumn('part.part_number', function (FSRQuotation $quotation) {
                    return $quotation->part ? $quotation->part->part_number : 'N/A';
                })
                ->addColumn('part.material_description', function (FSRQuotation $quotation) {
                    return $quotation->part ? $quotation->part->material_description : 'N/A';
                })
                ->addColumn('fsr_report.submitted_by.name', function (FSRQuotation $quotation) {
                    return $quotation->fsrReport && $quotation->fsrReport->submittedBy ? $quotation->fsrReport->submittedBy->name : 'N/A';
                })
                ->rawColumns(['status']) // If you plan to render status as HTML
                ->make(true);
        }
    }

    public function getGeneralReviewQuotations(Request $request)
    {
        if ($request->ajax()) {
            $query = FSRReport::with(['task.assignedEmployee.user', 'submittedBy', 'partQuotations.part', 'partQuotations.approver'])->has('partQuotations');

            // Filter by dealership_id of the current user if available
            $user = Auth::user();
            if ($user && $user->user_type === 'employee' && $user->employee && $user->employee->dealership_id) {
                $query->whereHas('task', function ($q) use ($user) {
                    $q->where('dealership_id', $user->employee->dealership_id);
                });
            } elseif ($user && property_exists($user, 'dealership_id') && $user->dealership_id) {
                $query->whereHas('task', function ($q) use ($user) {
                    $q->where('dealership_id', $user->dealership_id);
                });
            }

            // Filter by status of quotations if requested
            if ($request->filled('status')) {
                $query->whereHas('partQuotations', function ($q) use ($request) {
                    $q->where('status', $request->status);
                });
            }

            $query->orderBy('created_at', 'desc'); // Order by newest first

            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('task_id', function (FSRReport $fsrReport) {
                    return $fsrReport->task ? $fsrReport->task->id : 'N/A';
                })
                ->addColumn('task_title', function (FSRReport $fsrReport) {
                    return $fsrReport->task ? $fsrReport->task->title : 'N/A';
                })
                ->addColumn('submitted_by', function (FSRReport $fsrReport) {
                    return $fsrReport->submittedBy ? $fsrReport->submittedBy->name : 'N/A';
                })
                ->addColumn('quotations', function (FSRReport $fsrReport) {
                    $quotationsHtml = '<ul class="list-unstyled mb-0">';
                    $quotationCount = $fsrReport->partQuotations->count();
                    $displayCount = 4;

                    foreach ($fsrReport->partQuotations as $index => $quotation) {
                        if ($index >= $displayCount && $quotationCount > $displayCount) {
                            $quotationsHtml .= '<li class="collapse multi-collapse-' . $fsrReport->id . '">';
                        } else {
                            $quotationsHtml .= '<li>';
                        }

                        $statusBadgeClass = '';
                        switch ($quotation->status) {
                            case 'approved':
                                $statusBadgeClass = 'success';
                                break;
                            case 'pending':
                                $statusBadgeClass = 'busy';
                                break;
                            case 'rejected':
                                $statusBadgeClass = 'offline';
                                break;
                            case 'Approved':
                                $statusBadgeClass = 'success';
                                break;
                            case 'Pending':
                                $statusBadgeClass = 'busy';
                                break;
                            case 'Rejected':
                                $statusBadgeClass = 'offline';
                                break;
                            default:
                                $statusBadgeClass = 'secondary'; // Fallback if needed
                        }
                        $approvedQuantityBadge = '';

                        $quotationsHtml .= ($quotation->part ? $quotation->part->part_number : 'N/A') .
                            ' (' . $quotation->quoted_quantity . ')' . // Adding quantity text
                            ' <div class="social-status social-' . $statusBadgeClass . '"></div>' .
                            '</li>';
                    }
                    $quotationsHtml .= '</ul>';

                    if ($quotationCount > $displayCount) {
                        $quotationsHtml .= '<button class="btn btn-link btn-sm p-0 mt-2" type="button" data-bs-toggle="collapse" data-bs-target=".multi-collapse-' . $fsrReport->id . '" aria-expanded="false" aria-controls="multi-collapse-' . $fsrReport->id . '">';
                        $quotationsHtml .= 'Read More (' . ($quotationCount - $displayCount) . ' more)';
                        $quotationsHtml .= '</button>';
                    }
                    return $quotationsHtml;
                })
                ->addColumn('overall_status', function (FSRReport $fsrReport) {
                    $quotations = $fsrReport->partQuotations;
                    if ($quotations->isEmpty()) {
                        return '<span class="badge bg-secondary">No Quotations</span>';
                    }

                    $totalCount = $quotations->count();
                    $approvedCount = $quotations->where('status', 'approved')->count();
                    $rejectedCount = $quotations->where('status', 'rejected')->count();

                    if ($approvedCount === $totalCount) {
                        return '<span class="badge bg-success">Approved</span>';
                    } elseif ($rejectedCount === $totalCount) {
                        return '<span class="badge bg-danger">Rejected</span>';
                    } elseif ($approvedCount > 0 || $rejectedCount > 0) {
                        return '<span class="badge bg-info">Partially Approved</span>';
                    } else {
                        return '<span class="badge bg-warning">Pending</span>';
                    }
                })
                ->rawColumns(['quotations', 'overall_status'])
                ->make(true);
        }
    }

    /**
     * Store a newly created FSR quotation in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fsr_id' => 'required|exists:fsr_reports,id',
            'part_id' => 'required|exists:parts,id',
            'quoted_quantity' => 'required|integer|min:1',
            'quoted_unit_price' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
        ]);

        // Authorization: Only the assigned service engineer or a manager can add quotations
        $fsrReport = FSRReport::findOrFail($request->fsr_id);
        $user = Auth::user();

        // Assuming FSRReport has an assigned_to (employee_id) or similar field
        // and that employee has a user_id
        $isAssignedEngineer = $fsrReport->task->assignedEmployee && $fsrReport->task->assignedEmployee->user_id === $user->id;
        $isManager = $user->employee && $user->employee->role && ($user->employee->role->role === 'service_manager' || $user->employee->role->role === 'Service Manager'); // Example role check

        if (!$isAssignedEngineer && !$isManager) {
            return response()->json(['message' => 'Unauthorized to add quotation to this FSR report.'], 403);
        }

        $fsrQuotation = FSRQuotation::create([
            'fsr_id' => $request->fsr_id,
            'part_id' => $request->part_id,
            'quoted_quantity' => $request->quoted_quantity,
            'quoted_unit_price' => $request->quoted_unit_price,
            'status' => 'pending', // Default status
            'remarks' => $request->remarks,
        ]);

        return response()->json(['message' => 'FSR quotation added successfully.', 'quotation' => $fsrQuotation], 201);
    }

    /**
     * Display the specified FSR quotation.
     */
    public function show(FSRQuotation $fsrQuotation)
    {
        // Authorization: Only the assigned service engineer, manager, or approver can view
        $user = Auth::user();
        $fsrReport = $fsrQuotation->fsrReport;

        $isAssignedEngineer = $fsrReport->task->assigned_to_user_id === $user->id; // Adjust relationship
        $isManager = $user->employee && $user->employee->role && ($user->employee->role->role === 'service_manager'  || $user->employee->role->role === 'Service Manager');
        $isApprover = $fsrQuotation->approved_by_user_id === $user->id;

        if (!$isAssignedEngineer && !$isManager && !$isApprover) {
            return response()->json(['message' => 'Unauthorized to view this quotation.'], 403);
        }

        return response()->json($fsrQuotation->load(['part', 'approver']));
    }

    /**
     * Update the specified FSR quotation in storage.
     */
    public function update(Request $request, FSRQuotation $fsrQuotation)
    {
        $request->validate([
            'part_id' => 'required|exists:parts,id',
            'quoted_quantity' => 'required|integer|min:1',
            'quoted_unit_price' => 'required|numeric|min:0',
            'remarks' => 'nullable|string',
            'status' => ['nullable', 'string', Rule::in(['pending', 'approved', 'rejected'])],
        ]);

        // Authorization: Only the assigned service engineer or a manager can update pending quotations
        $user = Auth::user();
        $fsrReport = $fsrQuotation->fsrReport;

        $isAssignedEngineer = $fsrReport->task->assigned_to_user_id === $user->id; // Adjust relationship
        $isManager = $user->employee && $user->employee->role && ($user->employee->role->role === 'service_manager'  || $user->employee->role->role === 'Service Manager');

        if (!$isAssignedEngineer && !$isManager) {
            return response()->json(['message' => 'Unauthorized to update this quotation.'], 403);
        }

        // Prevent updating if already approved/rejected, unless by a manager
        if ($fsrQuotation->status !== 'pending' && !$isManager) {
            return response()->json(['message' => 'Cannot update an already processed quotation.'], 403);
        }

        $fsrQuotation->update($request->only([
            'part_id',
            'quoted_quantity',
            'quoted_unit_price',
            'remarks',
            'status',
        ]));

        return response()->json(['message' => 'FSR quotation updated successfully.', 'quotation' => $fsrQuotation]);
    }

    /**
     * Remove the specified FSR quotation from storage.
     */
    public function destroy(FSRQuotation $fsrQuotation)
    {
        // Authorization: Only the assigned service engineer or a manager can delete pending quotations
        $user = Auth::user();
        $fsrReport = $fsrQuotation->fsrReport;

        $isAssignedEngineer = $fsrReport->task->assigned_to_user_id === $user->id; // Adjust relationship
        $isManager = $user->employee && $user->employee->role && ($user->employee->role->role === 'service_manager'  || $user->employee->role->role === 'Service Manager');

        if (!$isAssignedEngineer && !$isManager) {
            return response()->json(['message' => 'Unauthorized to delete this quotation.'], 403);
        }

        if ($fsrQuotation->status !== 'pending' && !$isManager) {
            return response()->json(['message' => 'Cannot delete an already processed quotation.'], 403);
        }

        $fsrQuotation->delete();

        return response()->json(['message' => 'FSR quotation deleted successfully.']);
    }

    /**
     * Approve the specified FSR quotation.
     */
    public function approve(FSRQuotation $fsrQuotation)
    {
        // Authorization: Only a service manager can approve
        $user = Auth::user();
        if (!$user->employee || !$user->employee->role || ($user->employee->role->role !== 'service_manager' || $user->employee->role->role === 'Service Manager')) {
            return response()->json(['message' => 'Unauthorized to approve quotations.'], 403);
        }

        if ($fsrQuotation->status !== 'pending') {
            return response()->json(['message' => 'Only pending quotations can be approved.'], 400);
        }

        $fsrQuotation->update([
            'status' => 'approved',
            'approved_by_user_id' => $user->id,
            'approved_at' => now(),
        ]);

        return response()->json(['message' => 'FSR quotation approved successfully.', 'quotation' => $fsrQuotation]);
    }

    /**
     * Reject the specified FSR quotation.
     */
    public function reject(FSRQuotation $fsrQuotation)
    {
        // Authorization: Only a service manager can reject
        $user = Auth::user();
        if (!$user->employee || !$user->employee->role || ($user->employee->role->role !== 'service_manager'  || $user->employee->role->role === 'Service Manager')) {
            return response()->json(['message' => 'Unauthorized to reject quotations.'], 403);
        }

        if ($fsrQuotation->status !== 'pending') {
            return response()->json(['message' => 'Only pending quotations can be rejected.'], 400);
        }

        $fsrQuotation->update([
            'status' => 'rejected',
            'approved_by_user_id' => $user->id,
            'approved_at' => now(),
        ]);

        return response()->json(['message' => 'FSR quotation rejected successfully.', 'quotation' => $fsrQuotation]);
    }

    public function updateApprovedQuantities(Request $request, FSRReport $fsrReport)
    {
        $request->validate([
            'quantities' => 'required|array',
            'quantities.*' => 'required|integer|min:0',
        ]);

        foreach ($request->quantities as $quotationId => $approvedQuantity) {
            $quotation = FSRQuotation::where('id', $quotationId)
                ->where('fsr_id', $fsrReport->id)
                ->first();

            if ($quotation) {
                $status = $approvedQuantity > 0 ? 'approved' : 'rejected';
                $quotation->update([
                    'approved_quantity' => $approvedQuantity,
                    'status' => $status,
                    'approved_by_user_id' => Auth::id(),
                    'approved_at' => now(),
                ]);

                // Deduct approved quantity from part's stock_quantity
                $part = $quotation->part;
                if ($part) {
                    $part->stock_quantity -= $approvedQuantity;
                    $part->save();
                }
            }
        }

        return response()->json(['message' => 'Approved quantities updated successfully.']);
    }

    public function exportPdf(FSRReport $fsrReport)
    {
        $fsrReport->load(['task', 'submittedBy', 'partQuotations.part']);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('fsr_quotations.pdf', compact('fsrReport'));
        return $pdf->download('fsr_quotation_' . $fsrReport->id . '.pdf');
    }
}
