<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class DocumentRequestController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DocumentRequest::with(['user', 'forwardedToEmployee.employee.department', 'documentType']);

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
            if ($request->filled('document_type')) {
                $query->whereHas('documentType', function ($q) use ($request) {
                    $q->where('name', $request->document_type);
                });
            }
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereBetween('requested_date', [$request->start_date, $request->end_date]);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            return DataTables::of($query->get()->map(function ($documentRequest) {
                $documentRequest->action = ''; // Initialize action to an empty string
                return $documentRequest;
            }))
                ->addIndexColumn()
                ->addColumn('user', function ($row) {
                    return $row->user->name;
                })
                ->addColumn('document_type', function ($row) {
                    $typeClasses = [
                        'NOC' => 'bg-primary',
                        'salary_slip' => 'bg-secondary',
                    ];
                    $currentTypeClass = $typeClasses[$row->documentType->name] ?? 'bg-info';
                    return '<span class="badge ' . $currentTypeClass . '">' . ucfirst(str_replace('_', ' ', $row->documentType->name)) . '</span>';
                })
                ->addColumn('status', function ($row) use ($request) {
                    $statusClasses = [
                        'pending' => 'bg-warning text-dark',
                        'approved' => 'bg-success text-white',
                        'rejected' => 'bg-danger text-white',
                        'processed' => 'bg-info text-white',
                        'forwarded' => 'bg-primary text-white',
                        'approved and forwarded' => 'bg-secondary text-white',
                    ];
                    $currentStatusClass = $statusClasses[$row->status] ?? 'bg-secondary text-white';

                    $forwardedToEmployeeName = $row->forwardedToEmployee && $row->forwardedToEmployee->employee ? $row->forwardedToEmployee->employee->name : null;
                    $forwardedToEmployeeDepartment = $row->forwardedToEmployee && $row->forwardedToEmployee->employee && $row->forwardedToEmployee->employee->department ? $row->forwardedToEmployee->employee->department->name : null;

                    if ($request->has('my_requests')) {

                        $statusHtml = '<span class="badge ' . $currentStatusClass . '">' . ucfirst($row->status) . '</span>';
                        if ($row->status === 'approved and forwarded' && $forwardedToEmployeeName) {
                            $statusHtml .= '<br><small>(Forwarded to: ' . $forwardedToEmployeeName . (' (' . $forwardedToEmployeeDepartment . ')') . ')</small>';
                        }
                        return $statusHtml;
                    } else {
                        $options = '';
                        $allowedStatuses = ['pending', 'approved', 'rejected', 'processed', 'forwarded', 'approved and forwarded'];
                        foreach ($allowedStatuses as $status) {
                            $selected = ($row->status === $status) ? 'selected' : '';
                            $options .= '<option value="' . $status . '" ' . $selected . '>' . ucfirst(str_replace('_', ' ', $status)) . '</option>';
                        }

                        $selectHtml = '<select style="scale: 0.7; width: auto;" class="form-select fs-5 status-select status-chip ' . $currentStatusClass . '" data-id="' . $row->id . '" data-current-status="' . $row->status . '">' . $options . '</select>';
                        if ($row->status === 'approved and forwarded' && $forwardedToEmployeeName) {
                            $selectHtml .= '<br><small>(Forwarded to: ' . $forwardedToEmployeeName . (' (' . $forwardedToEmployeeDepartment . ')') . ')</small>';
                        }
                        return $selectHtml;
                    }
                })
                ->addColumn('action', function ($row) {
                    $buttons = '';
                    // View Button
                    $buttons .= ' <button class="btn btn-sm btn-primary view-document-request" data-id="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#viewDocumentRequestModal"><i class="fas fa-eye"></i></button>';

                    // Only allow delete for the user who created it or admin
                    if ($row->user_id == Auth::id() || Auth::user()->user_type === 'admin') {
                        $buttons .= ' <button class="btn btn-sm btn-danger delete-document-request" data-id="' . $row->id . '" data-bs-toggle="modal" data-bs-target="#deleteDocumentRequestModal"><i class="fas fa-trash"></i></button>';
                    }
                    return $buttons;
                })
                ->rawColumns(['document_type', 'status', 'action'])
                ->make(true);
        }


        return view('requests.document.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'remarks' => 'nullable|string',
            'document_type' => 'required|string',
        ]);

        // Determine the DocumentType based on whether an ID or a new name was submitted
        $documentTypeInput = $request->document_type;
        $documentType = null;

        if (is_numeric($documentTypeInput)) {
            // If it's a numeric value, assume it's an ID of an existing document type
            $documentType = \App\Models\DocumentType::find($documentTypeInput);
        }

        // If not found by ID, or if it was a non-numeric string (new tag)
        if (!$documentType) {
            $documentTypeName = trim($documentTypeInput);

            // Try to find an existing document type case-insensitively by name
            $documentType = \App\Models\DocumentType::whereRaw('LOWER(name) = ?', [strtolower($documentTypeName)])->first();

            // If still not found, create a new one with the lowercased name
            if (!$documentType) {
                $documentType = \App\Models\DocumentType::create(['name' => strtolower($documentTypeName)]);
            }
        }

        DocumentRequest::create([
            'user_id' => Auth::id(),
            'remarks' => $request->remarks,
            'document_type_id' => $documentType->id,
            'requested_date' => now(),
            'status' => 'pending',
        ]);

        return redirect()->route('document-requests.index')->with('success', 'Document request submitted successfully.');
    }

    public function show(DocumentRequest $documentRequest)
    {
        $documentRequest->load(['user', 'documentType']); // Eager-load documentType
        return response()->json($documentRequest);
    }

    public function destroy(DocumentRequest $documentRequest)
    {
        if ($documentRequest->user_id != Auth::id() && Auth::user()->user_type !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $documentRequest->delete();

        return redirect()->route('document-requests.index')->with('success', 'Document request deleted successfully.');
    }

    public function changeStatus(Request $request, DocumentRequest $documentRequest)
    {


        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected,processed,forwarded,approved and forwarded',
            'forwarded_to_employee_id' => 'nullable|exists:users,id',
        ]);

        $documentRequest->status = $request->status;

        if ($request->status === 'approved and forwarded') {
            $documentRequest->forwarded_to_employee_id = $request->forwarded_to_employee_id;
        } else {
            $documentRequest->forwarded_to_employee_id = null;
        }

        $documentRequest->save();
        return response()->json(['message' => 'Document request status updated successfully.']);
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

    public function searchDocumentTypes(Request $request)
    {
        $term = $request->input('q');
        $documentTypes = \App\Models\DocumentType::where('name', 'like', '%' . $term . '%')->get();

        return response()->json([
            'data' => $documentTypes->map(function ($documentType) {
                return ['id' => $documentType->id, 'text' => $documentType->name];
            }),
            'total' => $documentTypes->count(),
        ]);
    }

    // Temporary debug method to check DocumentType data
    public function debugDocumentTypes()
    {
        $documentTypes = \App\Models\DocumentType::all();
        return response()->json($documentTypes);
    }

    public function getCalendarEvents(Request $request)
    {
        $query = DocumentRequest::with(['user', 'documentType']);

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

        // Apply filters
        if ($request->employee_id) {
            $query->where('user_id', $request->employee_id);
        }
        if ($request->document_type) {
            $query->whereHas('documentType', function ($q) use ($request) {
                $q->where('name', $request->document_type);
            });
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->start) {
            $query->whereDate('requested_date', '>=', $request->start);
        }
        if ($request->end) {
            $query->whereDate('requested_date', '<=', $request->end);
        }


        $documents = $query->get();
        $events = [];

        foreach ($documents as $doc) {
            $title = $doc->user->name . ' - ' . ucfirst(str_replace('_', ' ', $doc->documentType->name));
            if ($request->has('my_requests')) {
                $title = ucfirst(str_replace('_', ' ', $doc->documentType->name));
            }

            $colorClass = 'bg-info';
            $docTypeName = strtolower($doc->documentType->name);
            if (str_contains($docTypeName, 'noc')) {
                $colorClass = 'bg-primary';
            } elseif (str_contains($docTypeName, 'salary')) {
                $colorClass = 'bg-success';
            } elseif (str_contains($docTypeName, 'experience')) {
                $colorClass = 'bg-warning';
            }

            $events[] = [
                'id' => $doc->id,
                'title' => $title,
                'start' => $doc->requested_date,
                'className' => $colorClass,
                'description' => $doc->remarks,
                'extendedProps' => [
                    'status' => $doc->status,
                    'type' => $doc->documentType->name
                ]
            ];
        }
        return response()->json($events);
    }
}
