<?php

namespace App\Http\Controllers\Api;

use OpenApi\Annotations as OA;


use App\Http\Controllers\Controller;
use App\Models\ExpenseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ExpenseRequestApiController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/expense-requests",
     *      operationId="getExpenseRequestsList",
     *      tags={"Expense Requests"},
     *      summary="Get list of expense requests",
     *      description="Returns list of expense requests",
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
     *          name="expense_type",
     *          description="Filter by expense type",
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
     *          @OA\JsonContent(ref="#/components/schemas/ExpenseRequestPaginatedResponse")
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
        $query = ExpenseRequest::with(['user', 'forwardedToEmployee.employee.department']);

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
        if ($request->filled('expense_type')) {
            $query->where('expense_type', $request->expense_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }


        $expenseRequests = $query->paginate(10);

        $expenseRequests->getCollection()->transform(function ($item) {
            $item->user_name = $item->user ? $item->user->name : 'N/A';
            $item->forwarded_to_employee = $item->forwardedToEmployee ? $item->forwardedToEmployee->name : 'N/A';
            $item->approved_amount = (int) $item->approved_amount;

            //remove the user relation to reduce payload
            unset($item->user);
            unset($item->forwardedToEmployee);
            return $item;
        });

        return response()->json([
            'current_page' => $expenseRequests->currentPage(),
            'data' => $expenseRequests->items(),
            'first_page_url' => $expenseRequests->url(1),
            'from' => $expenseRequests->firstItem(),
            'last_page' => $expenseRequests->lastPage(),
            'last_page_url' => $expenseRequests->url($expenseRequests->lastPage()),
            'next_page_url' => $expenseRequests->nextPageUrl(),
            'path' => $expenseRequests->path(),
            'per_page' => $expenseRequests->perPage(),
            'prev_page_url' => $expenseRequests->previousPageUrl(),
            'to' => $expenseRequests->lastItem(),
            'total' => $expenseRequests->total(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *      path="/expense-requests",
     *      operationId="storeExpenseRequest",
     *      tags={"Expense Requests"},
     *      summary="Store new expense request",
     *      description="Stores a new expense request and returns the created expense request",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"expense_type", "amount", "date"},
     *                  @OA\Property(property="expense_type", type="string", example="travel"),
     *                  @OA\Property(property="amount", type="number", format="float", example="100.50"),
     *                  @OA\Property(property="date", type="string", format="date", example="2025-10-07"),
     *                  @OA\Property(property="description", type="string", nullable=true, example="Lunch with client"),
     *                  @OA\Property(property="images[]", type="array", @OA\Items(type="string", format="binary"), nullable=true)
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/ExpenseRequest")
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
        $dataToValidate = $request->all();
        unset($dataToValidate['images']);
        $dataToValidate['images'] = $request->file('images');

        $validator = Validator::make($dataToValidate, [
            'expense_type' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'images' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!is_array($value)) {
                        $value = [$value];
                    }
                    foreach ($value as $file) {
                        if (!$file instanceof \Illuminate\Http\UploadedFile) {
                            $fail('The ' . $attribute . ' must be a valid image file or an array of image files.');
                            return;
                        }
                        $validator = \Illuminate\Support\Facades\Validator::make(
                            ['image' => $file],
                            ['image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048']
                        );
                        if ($validator->fails()) {
                            $fail('The ' . $attribute . ' must be a valid image file or an array of image files.');
                            return;
                        }
                    }
                },
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        if ($request->hasFile('images')) {
            $images = $request->file('images');
            if (!is_array($images)) {
                $images = [$images];
            }
            $imagePaths = [];
            foreach ($images as $image) {
                $filename = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('storage/expense_images'), $filename);
                $imagePaths[] = 'expense_images/' . $filename;
            }
            $validatedData['image'] = $imagePaths; // Set the image paths in validatedData
        } else {
            $validatedData['image'] = null; // Ensure image is null if no file is uploaded
        }

        $expenseRequest = ExpenseRequest::create([
            'user_id' => Auth::id(),
            'expense_type' => $validatedData['expense_type'],
            'amount' => $validatedData['amount'],
            'date' => $validatedData['date'],
            'description' => $validatedData['description'],
            'image' => $validatedData['image'], // Use validatedData['image']
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Expense request submitted successfully.', 'data' => $expenseRequest], 201);
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *      path="/expense-requests/{id}",
     *      operationId="getExpenseRequestById",
     *      tags={"Expense Requests"},
     *      summary="Get expense request information",
     *      description="Returns expense request data",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of expense request to return",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/ExpenseRequest")
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
    public function show(ExpenseRequest $expenseRequest)
    {
        $expenseRequest->load('user.employee.reportingTo');
        return response()->json($expenseRequest);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Post(
     *      path="/expense-requests/{id}",
     *      operationId="updateExpenseRequest",
     *      tags={"Expense Requests"},
     *      summary="Update existing expense request",
     *      description="Updates an expense request and returns the updated expense request. Ensure 'expense_type' and 'amount' are correctly provided in the multipart/form-data request.",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of expense request to update",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          description="Ensure the request is sent as multipart/form-data.",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"expense_type", "amount", "date"},
     *                  @OA\Property(property="expense_type", type="string", example="food"),
     *                  @OA\Property(property="amount", type="number", format="float", example="50.25"),
     *                  @OA\Property(property="date", type="string", format="date", example="2025-10-07"),
     *                  @OA\Property(property="description", type="string", nullable=true, example="Dinner with client"),
     *                  @OA\Property(property="images[]", type="array", @OA\Items(type="string", format="binary"), nullable=true),
     *                  @OA\Property(property="existing_images", type="string", description="JSON-encoded string of existing image paths to keep")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/ExpenseRequest")
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
    public function update(Request $request, ExpenseRequest $expenseRequest)
    {
        // Normalize JSON fields
        $request->merge([
            'existing_images' => json_decode($request->input('existing_images', '[]'), true) ?: [],
        ]);

        $rules = [
            'expense_type' => 'sometimes|required|string',
            'amount' => 'sometimes|required|numeric|min:0',
            'date' => 'sometimes|required|date',
            'images' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!is_array($value)) {
                        $value = [$value];
                    }
                    foreach ($value as $file) {
                        if (!$file instanceof \Illuminate\Http\UploadedFile) {
                            $fail('The ' . $attribute . ' must be a valid image file or an array of image files.');
                            return;
                        }
                        $validator = \Illuminate\Support\Facades\Validator::make(
                            ['image' => $file],
                            ['image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048']
                        );
                        if ($validator->fails()) {
                            $fail('The ' . $attribute . ' must be a valid image file or an array of image files.');
                            return;
                        }
                    }
                },
            ],
            'existing_images' => 'nullable|array',
            'existing_images.*' => 'string',
        ];

        $dataToValidate = $request->all();
        unset($dataToValidate['images']);
        $dataToValidate['images'] = $request->file('images');

        $validator = Validator::make($dataToValidate, $rules);

        if ($validator->fails()) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        $currentImages = $expenseRequest->image ?? [];
        $newImages = [];

        // Save new uploaded images
        if ($request->hasFile('images')) {
            $images = $request->file('images');
            if (!is_array($images)) {
                $images = [$images];
            }
            foreach ($images as $image) {
                $filename = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('storage/expense_images'), $filename);
                $newImages[] = 'expense_images/' . $filename;
            }
        }

        // If existing_images not sent, keep all current ones
        $existingImages = $request->input('existing_images', $currentImages);

        // Merge and ensure unique entries
        $finalImages = array_values(array_unique(array_merge($existingImages, $newImages)));

        // Delete only removed images
        foreach ($currentImages as $imagePath) {
            if (!in_array($imagePath, $finalImages)) {
                if (file_exists(public_path('storage/' . $imagePath))) {
                    unlink(public_path('storage/' . $imagePath));
                }
            }
        }

        $validatedData['image'] = $finalImages;
        unset($validatedData['images']); // Unset the images array
        unset($validatedData['existing_images']); // Unset the existing_images array

        $expenseRequest->update($validatedData);

        return response()->json(['message' => 'Expense request updated successfully.', 'data' => $expenseRequest]);
    }



    /**

     * Handle the update from a POST request to work around form method limitations.

     */

    public function updateFromPost(Request $request, ExpenseRequest $expenseRequest)

    {

        return $this->update($request, $expenseRequest);
    }



    /**

     * Remove the specified resource from storage.

     *
     * @OA\Delete(
     *      path="/expense-requests/{id}",
     *      operationId="deleteExpenseRequest",
     *      tags={"Expense Requests"},
     *      summary="Delete existing expense request",
     *      description="Deletes an expense request",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of expense request to delete",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(@OA\Property(property="message", type="string", example="Expense request deleted successfully."))
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
    public function destroy(ExpenseRequest $expenseRequest)
    {
        // if ($expenseRequest->user_id != Auth::id() && Auth::user()->user_type !== 'admin') {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        if ($expenseRequest->image) {
            foreach ($expenseRequest->image as $imagePath) {
                if (file_exists(public_path('storage/' . $imagePath))) {
                    unlink(public_path('storage/' . $imagePath));
                }
            }
        }

        $expenseRequest->delete();

        return response()->json(['message' => 'Expense request deleted successfully.']);
    }

    /**
     * Update the status of the specified resource.
     *
    /**
     * Update the status of the specified resource.
     *
     * @OA\Put(
     *      path="/expense-requests/{id}/change-status",
     *      operationId="changeExpenseRequestStatus",
     *      tags={"Expense Requests"},
     *      summary="Change expense request status",
     *      description="Changes the status of an expense request",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of expense request to change status",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"status"},
     *              @OA\Property(
     *                  property="status",
     *                  type="string",
     *                  enum={"pending", "approved", "rejected", "processed", "approved and forwarded"},
     *                  description="The new status for the expense request."
     *              ),
     *              @OA\Property(
     *                  property="forwarded_to_employee_id",
     *                  type="integer",
     *                  nullable=true,
     *                  description="ID of the employee to forward the request to. Required when status is 'approved and forwarded'."
     *              ),
     *              @OA\Property(
     *                  property="approved_amount",
     *                  type="number",
     *                  format="float",
     *                  nullable=true,
     *                  description="Approved amount. Required when status is 'approved' or 'approved and forwarded'."
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(@OA\Property(property="message", type="string", example="Expense request status updated successfully."))
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
    public function changeStatus(Request $request, ExpenseRequest $expenseRequest)
    {
        // if (Auth::user()->user_type !== 'admin' && !(Auth::user()->employee && Auth::user()->employee->subordinates->isNotEmpty()) && !($expenseRequest->forwarded_to_employee_id === Auth::id())) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

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

        return response()->json(['message' => 'Expense request status updated successfully.']);
    }
}
