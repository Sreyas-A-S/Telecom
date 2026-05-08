<?php

namespace App\Http\Controllers\Api;

use OpenApi\Annotations as OA;

use App\Http\Controllers\Controller;
use App\Models\LoanRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



/**
 * @OA\Schema(
 *     schema="LoanRequest",
 *     title="LoanRequest",
 *     description="Loan Request model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the loan request"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the user who made the request"
 *     ),
 *     @OA\Property(
 *         property="amount",
 *         type="number",
 *         format="float",
 *         description="Amount of the loan"
 *     ),
 *     @OA\Property(
 *         property="requested_on",
 *         type="string",
 *         format="date",
 *         description="Date the loan was requested"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Status of the loan request (pending, approved, rejected, processed)"
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
 *     schema="LoanRequestPaginatedResponse",
 *     title="LoanRequestPaginatedResponse",
 *     description="Paginated list of loan requests",
 *     @OA\Property(property="current_page", type="integer"),
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/LoanRequest")),
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
class LoanRequestApiController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/loan-requests",
     *      operationId="getLoanRequestsList",
     *      tags={"Loan Requests"},
     *      summary="Get list of loan requests",
     *      description="Returns list of loan requests",
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
     *          @OA\JsonContent(ref="#/components/schemas/LoanRequestPaginatedResponse")
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
        $query = LoanRequest::with(['user', 'forwardedToEmployee.employee.department']);

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
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('requested_on', [$request->start_date, $request->end_date]);
        }

        $loanRequests = $query->paginate(10);

        $loanRequests->getCollection()->transform(function ($item) {
            $item->user_name = $item->user ? $item->user->name : 'N/A';
            $item->forwarded_to_employee = $item->forwardedToEmployee ? $item->forwardedToEmployee->name : 'N/A';
            //remove the user relation to reduce payload
            unset($item->user);
            unset($item->forwardedToEmployee);
            return $item;
        });
        
        return response()->json([
            'current_page' => $loanRequests->currentPage(),
            'data' => $loanRequests->items(),
            'first_page_url' => $loanRequests->url(1),
            'from' => $loanRequests->firstItem(),
            'last_page' => $loanRequests->lastPage(),
            'last_page_url' => $loanRequests->url($loanRequests->lastPage()),
            'next_page_url' => $loanRequests->nextPageUrl(),
            'path' => $loanRequests->path(),
            'per_page' => $loanRequests->perPage(),
            'prev_page_url' => $loanRequests->previousPageUrl(),
            'to' => $loanRequests->lastItem(),
            'total' => $loanRequests->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *      path="/loan-requests",
     *      operationId="storeLoanRequest",
     *      tags={"Loan Requests"},
     *      summary="Store new loan request",
     *      description="Stores a new loan request and returns the created loan request",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"amount"},
     *              @OA\Property(property="amount", type="number", format="float", example="1000.00")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/LoanRequest")
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
            'amount' => 'required|numeric|min:0',
        ]);

        $loanRequest = LoanRequest::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'requested_on' => now(),
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Loan request submitted successfully.', 'data' => $loanRequest], 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *      path="/loan-requests/{id}",
     *      operationId="getLoanRequestById",
     *      tags={"Loan Requests"},
     *      summary="Get loan request information",
     *      description="Returns loan request data",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of loan request to return",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/LoanRequest")
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
    public function show(LoanRequest $loanRequest)
    {
        $loanRequest->load('user');
        return response()->json($loanRequest);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *      path="/loan-requests/{id}",
     *      operationId="updateLoanRequest",
     *      tags={"Loan Requests"},
     *      summary="Update existing loan request",
     *      description="Updates a loan request and returns the updated loan request",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of loan request to update",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"amount", "status"},
     *              @OA\Property(property="amount", type="number", format="float", example="1500.00"),
     *              @OA\Property(property="status", type="string", example="approved", enum={"pending", "approved", "rejected", "processed"})
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/LoanRequest")
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
    public function update(Request $request, LoanRequest $loanRequest)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,approved,rejected,processed',
        ]);

        $loanRequest->update([
            'amount' => $request->amount,
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'Loan request updated successfully.', 'data' => $loanRequest]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *      path="/loan-requests/{id}",
     *      operationId="deleteLoanRequest",
     *      tags={"Loan Requests"},
     *      summary="Delete existing loan request",
     *      description="Deletes a loan request",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of loan request to delete",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
    *          @OA\JsonContent(@OA\Property(property="message", type="string", example="Loan request deleted successfully."))
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
    public function destroy($id)
    {
        $loanRequest = LoanRequest::find($id);

        if (!$loanRequest) {
            return response()->json(['message' => 'Loan request not found.'], 404);
        }

        if ($loanRequest->user_id != Auth::id() && Auth::user()->user_type !== 'admin') {
            return response()->json(['message' => 'Unauthorized to delete this loan request.'], 403);
        }

        $loanRequest->delete();

        return response()->json(['message' => 'Loan request deleted successfully.'], 200);
    }

    /**
     * Update the status of the specified resource.
     *
     * @OA\Put(
     *      path="/loan-requests/{id}/change-status",
     *      operationId="changeLoanRequestStatus",
     *      tags={"Loan Requests"},
     *      summary="Change loan request status",
     *      description="Changes the status of a loan request",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of loan request to change status",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          description="The new status for the loan request.",
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
     *          @OA\JsonContent(@OA\Property(property="message", type="string", example="Loan request status updated successfully."))
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
    public function changeStatus(Request $request, LoanRequest $loanRequest)
    {
        // if (Auth::user()->user_type !== 'admin' && !(Auth::user()->employee && Auth::user()->employee->is_manager) && !($loanRequest->forwarded_to_employee_id === Auth::id())) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $request->validate([
            'status' => 'required|string|in:pending,approved,rejected,processed,approved and forwarded',
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
}