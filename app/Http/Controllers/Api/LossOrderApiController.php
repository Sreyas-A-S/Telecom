<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LossOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Yajra\DataTables\Facades\DataTables;

class LossOrderApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $lossOrders = LossOrder::all();
        return response()->json($lossOrders);
    }

    /**
     * Returns data for DataTables.
     */
    public function datatable(Request $request)
    {
        $query = LossOrder::with(['dealership', 'product']);

        // Apply filters
        if ($request->filled('month_year')) {
            $query->where('month', $request->month_year);
        }
        if ($request->filled('dealership_id')) {
            $query->where('dealership_id', $request->dealership_id);
        }
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }
        if ($request->filled('model_name')) {
            $query->where('model', 'like', '%' . $request->model_name . '%');
        }

        return DataTables::of($query)
            ->addColumn('sl_no', function ($row) {
                static $index = 0;
                return ++$index;
            })
            ->orderColumn('sl_no', function ($query, $order) {
                $query->orderBy('id', $order);
            })
            ->addColumn('dealership_name', function (LossOrder $lossOrder) {
                return $lossOrder->dealership->name ?? 'N/A';
            })
            ->addColumn('product_info', function (LossOrder $lossOrder) {
                return ($lossOrder->product->name ?? 'N/A') . ' ' . ($lossOrder->model ?? '');
            })
            ->addColumn('customer_location', function (LossOrder $lossOrder) {
                return $lossOrder->district . ', ' . $lossOrder->customer;
            })
            ->addColumn('segment_badge', function (LossOrder $lossOrder) {
                $badgeClass = $lossOrder->segment == 'Rented' ? 'badge-primary' : 'badge-info';
                return '<span class="badge ' . $badgeClass . '">' . $lossOrder->segment . '</span>';
            })
            ->addColumn('participation_badge', function (LossOrder $lossOrder) {
                $badgeClass = $lossOrder->participation == 'Yes' ? 'badge-success' : 'badge-danger';
                return '<span class="badge ' . $badgeClass . '">' . $lossOrder->participation . '</span>';
            })
            ->addColumn('remarks_display', function (LossOrder $lossOrder) {
                $remarks = $lossOrder->remarks;
                if (strlen($remarks) > 50) {
                    $truncated = substr($remarks, 0, 50) . '...';
                    return '<span class="remarks-truncated">' . $truncated . '</span>' .
                           '<span class="remarks-full d-none">' . $remarks . '</span>' .
                           ' <a href="#" class="read-more-toggle">Read More</a>';
                }
                return $remarks;
            })
            ->addColumn('actions', function (LossOrder $lossOrder) {
                $editUrl = route('api.loss-orders.show', $lossOrder->id);
                $deleteUrl = route('api.loss-orders.destroy', $lossOrder->id);
                return '<div class="d-flex">' .
                       '<button class="btn btn-primary shadow btn-xs sharp me-1 edit" data-id="' . $lossOrder->id . '"><i class="fa fa-pencil"></i></button>' .
                       '<button class="btn btn-danger shadow btn-xs sharp delete" data-id="' . $lossOrder->id . '"><i class="fa fa-trash"></i></button>' .
                       '</div>';
            })
            ->rawColumns(['segment_badge', 'participation_badge', 'remarks_display', 'actions'])
            ->make(true);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'month' => 'required|date_format:Y-m',
            'dealership_id' => 'required|exists:dealerships,id',
            'product_id' => 'required|exists:products,id',
            'tonnage' => 'nullable|numeric',
            'model' => 'nullable|string|max:255',
            'customer' => 'nullable|string|max:255',
            'segment' => 'nullable|string|in:Rented,Captive',
            'application' => 'nullable|string|max:255',
            'financier' => 'nullable|string|max:255',
            'district' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'participation' => 'nullable|string|in:Yes,No',
            'reasons_for_loss' => 'nullable|string',
            'remarks' => 'nullable|string',
            'engineer_name' => 'nullable|string|max:255',
        ]);

        $lossOrder = LossOrder::create($validatedData);
        return response()->json(['message' => 'Loss Order created successfully.', 'loss_order' => $lossOrder], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(LossOrder $lossOrder)
    {
        return response()->json($lossOrder);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LossOrder $lossOrder)
    {
        $validatedData = $request->validate([
            'month' => 'required|date_format:Y-m',
            'dealership_id' => 'required|exists:dealerships,id',
            'product_id' => 'required|exists:products,id',
            'tonnage' => 'nullable|numeric',
            'model' => 'nullable|string|max:255',
            'customer' => 'nullable|string|max:255',
            'segment' => 'nullable|string|in:Rented,Captive',
            'application' => 'nullable|string|max:255',
            'financier' => 'nullable|string|max:255',
            'district' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'participation' => 'nullable|string|in:Yes,No',
            'reasons_for_loss' => 'nullable|string',
            'remarks' => 'nullable|string',
            'engineer_name' => 'nullable|string|max:255',
        ]);

        $lossOrder->update($validatedData);
        return response()->json(['message' => 'Loss Order updated successfully.', 'loss_order' => $lossOrder]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LossOrder $lossOrder)
    {
        $lossOrder->delete();
        return response()->json(['message' => 'Loss Order deleted successfully.'], Response::HTTP_NO_CONTENT);
    }
}
