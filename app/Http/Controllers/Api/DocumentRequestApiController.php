<?php

namespace App\Http\Controllers\Api;

use OpenApi\Annotations as OA;

use App\Http\Controllers\Controller;
use App\Models\DocumentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;




/**
 * @OA\Schema(
 *     schema="DocumentRequest",
 *     title="DocumentRequest",
 *     description="Document Request model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the document request"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the user who made the request"
 *     ),
 *     @OA\Property(
 *         property="remarks",
 *         type="string",
 *         nullable=true,
 *         description="Remarks for the document request"
 *     ),
 *     @OA\Property(
 *         property="document_type",
 *         type="string",
 *         description="Type of document (e.g., NOC, salary_slip)"
 *     ),
 *     @OA\Property(
 *         property="requested_date",
 *         type="string",
 *         format="date",
 *         description="Date the document was requested"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Status of the document request (pending, approved, rejected, processed)"
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
 *     schema="DocumentRequestPaginatedResponse",
 *     title="DocumentRequestPaginatedResponse",
 *     description="Paginated list of document requests",
 *     @OA\Property(property="current_page", type="integer"),
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/DocumentRequest")),
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
class DocumentRequestApiController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/document-requests",
     *      operationId="getDocumentRequestsList",
     *      tags={"Document Requests"},
     *      summary="Get list of document requests",
     *      description="Returns list of document requests",
     *      @OA\Parameter(
     *          name="my_requests",
     *          description="Filter by current user's requests",
     *          in="query",
     *          @OA\Schema(
     *              type="boolean"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="employee_id",
     *          description="Filter by employee ID",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="document_type",
     *          description="Filter by document type",
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          description="Filter by status",
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="start_date",
     *          description="Filter by start date (YYYY-MM-DD)",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              format="date"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="end_date",
     *          description="Filter by end date (YYYY-MM-DD)",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              format="date"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          description="The page number to retrieve.",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/DocumentRequestPaginatedResponse")
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
    public function index(Request $request)
    {
        $query = DocumentRequest::query();

         if ($request->my_requests == 'true') {                 
            $query->where('user_id', Auth::id());
        } else {
            if (Auth::user()->employee) {
                $currentEmployeeId = Auth::id();
                $currentUserId = Auth::user()->employee->user_id;
                $reportingEmployeeUserIds = \App\Models\Employee::where('reporting_to', $currentEmployeeId)->pluck('user_id');
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
            $query->where('document_type', $request->document_type);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('requested_date', [$request->start_date, $request->end_date]);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $documentRequests = $query->paginate(10);

        // insert an additional index named forwarded_to_employee 
        $documentRequests->getCollection()->transform(function ($item) {
            $item->document_type = ucfirst(str_replace('_', ' ', $item->documentType->name));
            $item->user_name = $item->user ? $item->user->name : 'N/A';
            $item->forwarded_to_employee = $item->forwardedToEmployee ? $item->forwardedToEmployee->name : 'N/A';
            //remove the user relation to reduce payload
            unset($item->user);
            unset($item->documentType);
            unset($item->forwardedToEmployee);
            return $item;
        });

        return response()->json([
            'current_page' => $documentRequests->currentPage(),
            'data' => $documentRequests->items(),
            'first_page_url' => $documentRequests->url(1),
            'from' => $documentRequests->firstItem(),
            'last_page' => $documentRequests->lastPage(),
            'last_page_url' => $documentRequests->url($documentRequests->lastPage()),
            'next_page_url' => $documentRequests->nextPageUrl(),
            'path' => $documentRequests->path(),
            'per_page' => $documentRequests->perPage(),
            'prev_page_url' => $documentRequests->previousPageUrl(),
            'to' => $documentRequests->lastItem(),
            'total' => $documentRequests->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *      path="/document-requests",
     *      operationId="storeDocumentRequest",
     *      tags={"Document Requests"},
     *      summary="Store new document request",
     *      description="Stores a new document request and returns the created document request",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"document_type"},
     *              @OA\Property(property="remarks", type="string", nullable=true, example="Need this for visa application"),
     *              @OA\Property(property="document_type", type="string", example="NOC", enum={"NOC", "salary_slip"})
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/DocumentRequest")
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

        $documentRequest = DocumentRequest::create([
            'user_id' => Auth::id(),
            'remarks' => $request->remarks,
            'document_type_id' => $documentType->id,
            'requested_date' => now(),
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Document request submitted successfully.', 'data' => $documentRequest], 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *      path="/document-requests/{id}",
     *      operationId="getDocumentRequestById",
     *      tags={"Document Requests"},
     *      summary="Get document request information",
     *      description="Returns document request data",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of document request to return",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/DocumentRequest")
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
    public function show(DocumentRequest $documentRequest)
    {
        $documentRequest->load('user');
        return response()->json($documentRequest);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Post(
     *      path="/document-requests/{id}",
     *      operationId="updateDocumentRequest",
     *      tags={"Document Requests"},
     *      summary="Update existing document request",
     *      description="Updates a document request and returns the updated document request",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of document request to update",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"document_type", "status"},
     *              @OA\Property(property="remarks", type="string", nullable=true, example="Updated remarks"),
     *              @OA\Property(property="document_type", type="string", example="salary_slip", enum={"NOC", "salary_slip"}),
     *              @OA\Property(property="status", type="string", example="approved", enum={"pending", "approved", "rejected", "processed"})
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/DocumentRequest")
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
    public function update(Request $request, DocumentRequest $documentRequest)
    {
        // var_dump($request->all());
        $request->validate([
            'remarks' => 'nullable|string',
            'document_type' => 'required|string',
            'status' => 'required|in:pending,approved,rejected,processed',
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

        $documentRequest->update([
            'remarks' => $request->remarks,
            'document_type_id' => $documentType->id,
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'Document request updated successfully.', 'data' => $documentRequest]);
    }

    /**
     * Handle the update from a POST request to work around form method limitations.
     */
    public function updateFromPost(Request $request, DocumentRequest $documentRequest)
    {
        return $this->update($request, $documentRequest);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *      path="/document-requests/{id}",
     *      operationId="deleteDocumentRequest",
     *      tags={"Document Requests"},
     *      summary="Delete existing document request",
     *      description="Deletes a document request",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of document request to delete",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(@OA\Property(property="message", type="string", example="Document request deleted successfully."))
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
    public function destroy(DocumentRequest $documentRequest)
    {
        if ($documentRequest->user_id != Auth::id() && Auth::user()->user_type !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $documentRequest->delete();

        return response()->json(['message' => 'Document request deleted successfully.']);
    }

    /**
     * Update the status of the specified resource.
     *
     * @OA\Put(
     *      path="/document-requests/{id}/change-status",
     *      operationId="changeDocumentRequestStatus",
     *      tags={"Document Requests"},
     *      summary="Change document request status",
     *      description="Changes the status of a document request",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of document request to change status",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          description="The new status for the document request.",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              enum={"pending", "approved", "rejected", "processed", "approved and forwarded"}
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
     *          @OA\JsonContent(@OA\Property(property="message", type="string", example="Document request status updated successfully."))
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
    public function changeStatus(Request $request, DocumentRequest $documentRequest)
    {
        // if (Auth::user()->user_type !== 'admin' && !(Auth::user()->employee && Auth::user()->employee->is_manager) && !($documentRequest->forwarded_to_employee_id === Auth::id())) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected,processed,approved and forwarded',
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
}