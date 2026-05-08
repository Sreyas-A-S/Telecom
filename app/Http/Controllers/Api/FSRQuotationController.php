<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FSRQuotation;
use App\Models\FSRReport;
use App\Models\Part;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

/**
 * @group FSR Quotation Management
 *
 * APIs for managing FSR (Field Service Report) Quotations
 */
class FSRQuotationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/fsr-quotations",
     *     summary="Display a listing of FSR Quotations",
     *     tags={"FSR Quotations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         @OA\Schema(
     *             type="string",
     *             enum={"pending", "approved", "rejected"}
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="from_date",
     *         in="query",
     *         description="Filter by from date",
     *         @OA\Schema(
     *             type="string",
     *             format="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="to_date",
     *         in="query",
     *         description="Filter by to date",
     *         @OA\Schema(
     *             type="string",
     *             format="date"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="fsr_id",
     *         in="query",
     *         description="Filter by FSR Report ID",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="quotations", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     *
     * Display a listing of FSR Quotations.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        // Validate request parameters
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'fsr_id' => 'nullable|exists:fsr_reports,id',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        // Check if user has permission to view quotations



        $query = FSRQuotation::with([
            'fsrReport.client',
            'part',
            'approver.employee',
            'creator.employee'
        ])
            ->when($request->status, function ($q) use ($request) {
                return $q->where('status', $request->status);
            })
            ->when($request->from_date, function ($q) use ($request) {
                return $q->whereDate('created_at', '>=', $request->from_date);
            })
            ->when($request->to_date, function ($q) use ($request) {
                return $q->whereDate('created_at', '<=', $request->to_date);
            })
            ->when($request->fsr_id, function ($q) use ($request) {
                return $q->where('fsr_id', $request->fsr_id);
            });

        // If user the user has no employee relation or no dealership id, restrict access
        if (!Auth::user()->employee || !Auth::user()->employee->department_id) {
            $query->whereHas('fsrReport', function ($q) {
                $q->where('department_id', Auth::user()->employee->department_id);
            });
        }


        $quotations = $query->latest()->paginate($request->per_page ?? 10);

        return response()->json([
            'quotations' => $quotations,
            'message' => 'FSR Quotations retrieved successfully'
        ]);
    }

    /**
     * @OA\Post(
     *     path="/fsr-quotations",
     *     summary="Store a newly created FSR Quotation",
     *     tags={"FSR Quotations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"fsr_id", "part_id", "quantity", "unit_price"},
     *             @OA\Property(property="fsr_id", type="integer"),
     *             @OA\Property(property="part_id", type="integer"),
     *             @OA\Property(property="quantity", type="integer"),
     *             @OA\Property(property="unit_price", type="number", format="float"),
     *             @OA\Property(property="notes", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="FSR Quotation created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="quotation", ref="#/components/schemas/FSRQuotation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     *
     * Store a newly created FSR Quotation.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Check permission


            // Validate request
            $validated = $request->validate([
                'fsr_id' => 'required|exists:fsr_reports,id',
                'part_id' => 'required|exists:parts,id',
                'quantity' => 'required|integer|min:1',
                'unit_price' => 'required|numeric|min:0',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Verify FSR Report status
            $fsrReport = FSRReport::findOrFail($validated['fsr_id']);
            if (!in_array($fsrReport->status, ['draft', 'pending'])) {
                throw ValidationException::withMessages([
                    'error' => "Cannot add quotation to FSR Report in {$fsrReport->status} status"
                ]);
            }

            // Verify part stock
            $part = Part::findOrFail($validated['part_id']);
            if ($part->stock < $validated['quantity']) {
                throw ValidationException::withMessages([
                    'error' => "Insufficient stock. Available: {$part->stock}"
                ]);
            }

            $fsrQuotation = FSRQuotation::create([
                'fsr_id' => $validated['fsr_id'],
                'part_id' => $validated['part_id'],
                'quantity' => $validated['quantity'],
                'unit_price' => $validated['unit_price'],
                'total_price' => $validated['quantity'] * $validated['unit_price'],
                'notes' => $validated['notes'],
                'status' => 'pending',
                'created_by_user_id' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'FSR Quotation created successfully',
                'quotation' => $fsrQuotation->load(['fsrReport.client', 'part', 'creator.employee'])
            ], 201);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('FSR Quotation creation failed: ' . $e->getMessage());
            return response()->json(['message' => 'Error creating FSR Quotation'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/fsr-quotations/{fsrQuotation}",
     *     summary="Display the specified FSR Quotation",
     *     tags={"FSR Quotations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="fsrQuotation",
     *         in="path",
     *         required=true,
     *         description="ID of the FSR Quotation",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="quotation", ref="#/components/schemas/FSRQuotation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     *
     * Display the specified FSR Quotation.
     *
     * @param FSRQuotation $fsrQuotation
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(FSRQuotation $fsrQuotation)
    {


        // If not admin/manager, verify department access
        if (!Auth::user()->employee?->role?->hasPermission('view-all-fsr-quotations')) {
            if ($fsrQuotation->fsrReport->department_id !== Auth::user()->employee->department_id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        return response()->json([
            'quotation' => $fsrQuotation->load([
                'fsrReport.client',
                'part',
                'approver.employee',
                'creator.employee'
            ])
        ]);
    }

    /**
     * @OA\Put(
     *     path="/fsr-quotations/{fsrQuotation}/approve",
     *     summary="Approve the specified FSR Quotation",
     *     tags={"FSR Quotations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="fsrQuotation",
     *         in="path",
     *         required=true,
     *         description="ID of the FSR Quotation",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="remarks", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FSR Quotation approved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="quotation", ref="#/components/schemas/FSRQuotation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     *
     * Approve the specified FSR Quotation.
     *
     * @param Request $request
     * @param FSRQuotation $fsrQuotation
     * @return \Illuminate\Http\JsonResponse
     */
    public function approve(Request $request, FSRQuotation $fsrQuotation)
    {


        $validated = $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);

        // Verify current status
        if ($fsrQuotation->status !== 'pending') {
            throw ValidationException::withMessages([
                'error' => "Cannot approve quotation in {$fsrQuotation->status} status"
            ]);
        }

        // Verify stock availability
        if ($fsrQuotation->part->stock < $fsrQuotation->quantity) {
            throw ValidationException::withMessages([
                'error' => "Insufficient stock. Required: {$fsrQuotation->quantity}, Available: {$fsrQuotation->part->stock}"
            ]);
        }

        $fsrQuotation->update([
            'status' => 'approved',
            'approved_by_user_id' => Auth::id(),
            'approved_at' => now(),
            'remarks' => $validated['remarks'],
        ]);

        $this->updateFSRReportStatus($fsrQuotation->fsrReport);

        // Optionally, update part stock here if needed
        // $fsrQuotation->part->decrement('stock', $fsrQuotation->quantity);

        DB::commit();

        return response()->json([
            'message' => 'FSR Quotation approved successfully',
            'quotation' => $fsrQuotation->fresh()->load([
                'fsrReport.client',
                'part',
                'approver.employee',
                'creator.employee'
            ])
        ]);
    }

    /**
     * @OA\Put(
     *     path="/fsr-quotations/{fsrQuotation}/reject",
     *     summary="Reject the specified FSR Quotation",
     *     tags={"FSR Quotations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="fsrQuotation",
     *         in="path",
     *         required=true,
     *         description="ID of the FSR Quotation",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"remarks"},
     *             @OA\Property(property="remarks", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FSR Quotation rejected successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="quotation", ref="#/components/schemas/FSRQuotation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     *
     * Reject the specified FSR Quotation.
     *
     * @param Request $request
     * @param FSRQuotation $fsrQuotation
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject(Request $request, FSRQuotation $fsrQuotation)
    {


        $validated = $request->validate([
            'remarks' => 'required|string|max:1000',
        ]);

        if ($fsrQuotation->status !== 'pending') {
            throw ValidationException::withMessages([
                'error' => "Cannot reject quotation in {$fsrQuotation->status} status"
            ]);
        }

        $fsrQuotation->update([
            'status' => 'rejected',
            'approved_by_user_id' => Auth::id(),
            'approved_at' => now(),
            'remarks' => $validated['remarks'],
        ]);

        $this->updateFSRReportStatus($fsrQuotation->fsrReport);

        DB::commit();

        return response()->json([
            'message' => 'FSR Quotation rejected successfully',
            'quotation' => $fsrQuotation->fresh()->load([
                'fsrReport.client',
                'part',
                'approver.employee',
                'creator.employee'
            ])
        ]);
    }

    /**
     * @OA\Put(
     *     path="/fsr-quotations/{fsrQuotation}",
     *     summary="Update the specified FSR Quotation",
     *     tags={"FSR Quotations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="fsrQuotation",
     *         in="path",
     *         required=true,
     *         description="ID of the FSR Quotation",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"quantity", "unit_price"},
     *             @OA\Property(property="quantity", type="integer"),
     *             @OA\Property(property="unit_price", type="number", format="float"),
     *             @OA\Property(property="notes", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FSR Quotation updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="quotation", ref="#/components/schemas/FSRQuotation")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     *
     * Update the specified FSR Quotation.
     *
     * @param Request $request
     * @param FSRQuotation $fsrQuotation
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, FSRQuotation $fsrQuotation)
    {


        // Only allow updates for pending quotations
        if ($fsrQuotation->status !== 'pending') {
            throw ValidationException::withMessages([
                'error' => "Cannot update quotation in {$fsrQuotation->status} status"
            ]);
        }

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Verify stock if quantity is being increased
        if ($validated['quantity'] > $fsrQuotation->quantity) {
            $additionalQuantity = $validated['quantity'] - $fsrQuotation->quantity;
            if ($fsrQuotation->part->stock < $additionalQuantity) {
                throw ValidationException::withMessages([
                    'error' => "Insufficient stock for quantity increase. Available: {$fsrQuotation->part->stock}"
                ]);
            }
        }

        $fsrQuotation->update([
            'quantity' => $validated['quantity'],
            'unit_price' => $validated['unit_price'],
            'total_price' => $validated['quantity'] * $validated['unit_price'],
            'notes' => $validated['notes'],
            'updated_by_user_id' => Auth::id(),
        ]);

        DB::commit();

        return response()->json([
            'message' => 'FSR Quotation updated successfully',
            'quotation' => $fsrQuotation->fresh()->load([
                'fsrReport.client',
                'part',
                'approver.employee',
                'creator.employee'
            ])
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/fsr-quotations/{fsrQuotation}",
     *     summary="Remove the specified FSR Quotation",
     *     tags={"FSR Quotations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="fsrQuotation",
     *         in="path",
     *         required=true,
     *         description="ID of the FSR Quotation",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="FSR Quotation deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     *
     * Remove the specified FSR Quotation.
     *
     * @param FSRQuotation $fsrQuotation
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(FSRQuotation $fsrQuotation)
    {


        // Only allow deletion of pending quotations
        if ($fsrQuotation->status !== 'pending') {
            throw ValidationException::withMessages([
                'error' => "Cannot delete quotation in {$fsrQuotation->status} status"
            ]);
        }

        $fsrQuotation->delete();

        DB::commit();

        return response()->json([
            'message' => 'FSR Quotation deleted successfully'
        ]);
    }

    private function updateFSRReportStatus(FSRReport $fsrReport)
    {
        $allQuotations = $fsrReport->partQuotations;
        $approvedCount = $allQuotations->where('status', 'approved')->count();
        $rejectedCount = $allQuotations->where('status', 'rejected')->count();
        $pendingCount = $allQuotations->where('status', 'pending')->count();

        if ($pendingCount === 0) {
            if ($approvedCount === $allQuotations->count()) {
                $fsrReport->status = 'approved';
            } else if ($rejectedCount === $allQuotations->count()) {
                $fsrReport->status = 'rejected';
            } else {
                $fsrReport->status = 'partially approved';
            }
        } else {
            if ($approvedCount > 0 || $rejectedCount > 0) {
                $fsrReport->status = 'partially approved';
            }
        }

        $fsrReport->save();
    }
}
