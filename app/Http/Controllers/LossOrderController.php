<?php

namespace App\Http\Controllers;

use App\Models\Dealership; // Added
use App\Models\LossOrder; // Added
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables; // Added
use Illuminate\Support\Facades\Session; // Added
use Maatwebsite\Excel\Facades\Excel; // Added
use App\Exports\LossOrdersExport; // Added
use Illuminate\Support\Facades\Auth;
use App\Models\State; // Added
use App\Models\District; // Added
use App\Models\Product;

use App\Models\ProductModel;
use Illuminate\Support\Facades\Log;

class LossOrderController extends Controller
{



    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!checkMenu(Session::get('role_id'), 9, 'read')) {
            return redirect()->back()->with('error', 'You do not have permission to view loss orders.');
        }
        $dealerships = Dealership::where('brand', 1)->get();

        $user = Auth::user();
        if ($user) {
            $user->load('employee');
        }

        // Fetch Kerala districts
        $keralaState = State::where('name', 'Kerala')->first();
        $keralaDistricts = $keralaState ? $keralaState->districts()->get(['id', 'name']) : collect();

        $products = Product::all();

        return view('loss-orders.index', compact('dealerships', 'keralaDistricts', 'products'));
    }

    /**
     * Export data to Excel.
     */
    public function exportExcel(Request $request)
    {
        //either the menu permission is vailable or the user_type is admin
        if (!checkMenu(Session::get('role_id'), 9, 'read')) {
            return redirect()->back()->with('error', 'You do not have permission to export loss orders.');
        }

        // Get filter parameters from the request
        $monthYear = $request->query('month_year');
        $dealershipId = $request->query('dealership_id');
        $productId = $request->query('product_id'); // passed as 'product_id' ID from frontend
        $modelName = $request->query('model_name');
        $searchValue = $request->query('search_value');
        $start = $request->query('start');
        $length = $request->query('length');

        return Excel::download(new LossOrdersExport($monthYear, $dealershipId, $productId, $modelName, $searchValue, $start, $length), 'loss_orders.xlsx');
    }

    /**
     * Export single loss order to PDF.
     */
    public function exportPdf(LossOrder $lossOrder)
    {
        $lossOrder->load('dealership');
        $pdf = app('dompdf.wrapper');
        $pdf->loadView('loss-orders.pdf', compact('lossOrder'));
        return $pdf->download('loss-order-details-' . $lossOrder->id . '.pdf');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // This method is not directly used as we have a tabbed interface
    }

    /**
     * Get data for Datatable.
     */
    public function getDataTableData(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 9, 'read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = LossOrder::with(['dealership']);

        $user = Auth::user();
        if ($user && $user->employee && $user->employee->dealership_id && $user->user_type !== 'admin') {
            $data->where('loss_orders.dealership_id', $user->employee->dealership_id);
        }


        if ($request->has('month_year') && !empty($request->month_year)) {
            $keyword = $request->month_year;
            $data->where('loss_orders.month', $keyword);
        }

        if ($request->has('dealership_id') && !empty($request->dealership_id)) {
            $data->where('loss_orders.dealership_id', $request->dealership_id);
        }

        if ($request->has('product_name') && !empty($request->product_name)) {
            $data->where('loss_orders.product_name', 'like', '%' . $request->product_name . '%');
        }

        if ($request->has('model_name') && !empty($request->model_name)) {
            $data->where('loss_orders.product_model_name', 'like', '%' . $request->model_name . '%');
        }

        if ($request->has('search') && is_array($request->input('search')) && !empty($request->input('search.value'))) {
            $keyword = $request->input('search.value');
            $data->where(function ($q) use ($keyword) {
                $q->whereHas('dealership', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                })
                    ->orWhere('loss_orders.product_name', 'like', "%{$keyword}%")
                    ->orWhere('loss_orders.product_model_name', 'like', "%{$keyword}%")
                    ->orWhere('loss_orders.tonnage', 'like', "%{$keyword}%")
                    ->orWhere('loss_orders.customer', 'like', "%{$keyword}%")
                    ->orWhere('loss_orders.segment', 'like', "%{$keyword}%")
                    ->orWhere('loss_orders.application', 'like', "%{$keyword}%")
                    ->orWhere('loss_orders.financier', 'like', "%{$keyword}%")
                    ->orWhere('loss_orders.district', 'like', "%{$keyword}%")
                    ->orWhere('loss_orders.category', 'like', "%{$keyword}%")
                    ->orWhere('loss_orders.participation', 'like', "%{$keyword}%")
                    ->orWhere('loss_orders.reasons_for_loss', 'like', "%{$keyword}%")
                    ->orWhere('loss_orders.remarks', 'like', "%{$keyword}%")
                    ->orWhere('loss_orders.engineer_name', 'like', "%{$keyword}%")
                    ->orWhere('loss_orders.month', 'like', "%{$keyword}%");
            });
        }

        // dd($data->toSql(), $data->getBindings());

        $start = $request->input('start', 0);
        return DataTables::of($data)
            ->addColumn('sl_no', function ($row) use (&$start) {
                return ++$start;
            })
            ->orderColumn('sl_no', function ($query, $order) {
                $query->orderBy('id', $order);
            })
            ->addColumn('dealership_name', function (LossOrder $lossOrder) {
                return $lossOrder->dealership->name ?? 'N/A';
            })
            ->addColumn('product_info', function (LossOrder $lossOrder) {
                $productName = $lossOrder->product_name ?? 'N/A';
                $productModelName = $lossOrder->product_model_name ?? '';
                $modelSeriesName = $lossOrder->model_series_name ?? '';

                $info = $productName;
                if ($productModelName) {
                    $info .= ' (' . $productModelName . ')';
                }
                if ($modelSeriesName) {
                    $info .= ' [' . $modelSeriesName . ']';
                }
                return $info;
            })
            ->addColumn('customer_location', function (LossOrder $lossOrder) {
                return $lossOrder->customer . ' (' . $lossOrder->district . ')';
            })
            ->addColumn('segment_badge', function ($row) {
                $badgeClass = '';
                if ($row->segment == 'Rented') {
                    $badgeClass = 'bg-info';
                } elseif ($row->segment == 'Captive') {
                    $badgeClass = 'bg-success';
                }
                return '<span class="badge ' . $badgeClass . '">' . $row->segment . '</span>';
            })
            ->addColumn('participation_badge', function ($row) {
                $badgeClass = '';
                if ($row->participation == 'Yes') {
                    $badgeClass = 'bg-primary';
                } elseif ($row->participation == 'No') {
                    $badgeClass = 'bg-danger';
                }
                return '<span class="badge ' . $badgeClass . '">' . $row->participation . '</span>';
            })
            ->addColumn('remarks_display', function ($row) {
                $remarks = $row->remarks;
                $limit = 50;
                if (strlen($remarks) > $limit) {
                    $truncated = substr($remarks, 0, $limit);
                    return '<span class="remarks-truncated">' . htmlspecialchars($truncated) . '...</span>' .
                        '<span class="remarks-full" style="display:none;">' . htmlspecialchars($remarks) . '</span>' .
                        ' <a href="#" class="read-more-link">Read More</a>';
                }
                return htmlspecialchars($remarks);
            })
            ->addColumn('actions', function ($row) {
                $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">' .
                    '<li class="view"><a href="#" title="View" data-bs-toggle="modal" data-bs-target="#viewLossOrderModal" data-id="' . $row->id . '"><i class="icon-eye"></i></a></li>' .
                    '<li class="edit"><a href="#" title="Edit" data-bs-toggle="modal" data-bs-target="#editLossOrderModal" data-id="' . $row->id . '"><i class="icon-pencil-alt"></i></a></li>' .
                    '<li class="pdf"><a href="' . route('loss-orders.export.pdf', $row->id) . '" title="Download PDF" target="_blank"><i class="fa fa-file-pdf-o"></i></a></li>' .
                    '<li class="delete"><a title="Delete" href="#" data-bs-toggle="modal" data-bs-target="#deleteLossOrderModal" data-id="' . $row->id . '"><i class="icon-trash"></i></a></li>' .
                    '</ul>';
                return $btn;
            })
            ->rawColumns(['actions', 'segment_badge', 'participation_badge', 'remarks_display'])
            ->toJson();
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 9, 'create') && Auth::user()->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'month' => 'required|string|max:255',
            'selected_dealership_id' => 'required|integer|exists:dealerships,id',
            'product_name' => 'required|string|max:255',
            'tonnage' => 'nullable|numeric',
            'product_model_name' => 'nullable|string|max:255',
            'model_series_name' => 'nullable|string|max:255',
            'customer' => 'nullable|string|max:255',
            'segment' => 'nullable|in:Rented,Captive',
            'application' => 'nullable|string|max:255',
            'financier' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'participation' => 'nullable|in:Yes,No',
            'reasons_for_loss' => 'nullable|string',
            'remarks' => 'nullable|string',
            'engineer_name' => 'nullable|string|max:255',
        ]);

        // Assign the user's dealership_id or selected_dealership_id
        $user = Auth::user();
        if ($user && $user->employee && $user->employee->dealership_id) {
            $validatedData['dealership_id'] = $user->employee->dealership_id;
        } else {
            $validatedData['dealership_id'] = $validatedData['selected_dealership_id'];
        }
        unset($validatedData['selected_dealership_id']);

        $lossOrder = LossOrder::create($validatedData);

        log_action('Loss Order created: ' . $lossOrder->id . ' for ' . $lossOrder->customer);

        return response()->json(['message' => 'Loss Order created successfully.']);
    }

    /**
     * Display the specified resource.
     */
    public function show(LossOrder $lossOrder)
    {
        if (!checkMenu(Session::get('role_id'), 9, 'read')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($lossOrder->load('dealership'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LossOrder $lossOrder)
    {
        if (!checkMenu(Session::get('role_id'), 9, 'update')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($lossOrder->load('dealership'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LossOrder $lossOrder)
    {
        if (!checkMenu(Session::get('role_id'), 9, 'update') && Auth::user()->user_type !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validatedData = $request->validate([
            'month' => 'required|string|max:255',
            'dealership_id' => 'required|integer|exists:dealerships,id',
            'product_name' => 'required|string|max:255',
            'tonnage' => 'nullable|numeric',
            'product_model_name' => 'nullable|string|max:255',
            'model_series_name' => 'nullable|string|max:255',
            'customer' => 'nullable|string|max:255',
            'segment' => 'nullable|in:Rented,Captive',
            'application' => 'nullable|string|max:255',
            'financier' => 'nullable|string|max:255',
            'district' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'participation' => 'nullable|in:Yes,No',
            'reasons_for_loss' => 'nullable|string',
            'remarks' => 'nullable|string',
            'engineer_name' => 'nullable|string|max:255',
        ]);

        $lossOrder->update($validatedData);

        log_action('Loss Order updated: ' . $lossOrder->id . ' for ' . $lossOrder->customer);

        return response()->json(['message' => 'Loss Order updated successfully.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LossOrder $lossOrder)
    {

        if (!checkMenu(Session::get('role_id'), 9, 'delete')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $lossOrderId = $lossOrder->id;
        $lossOrderCustomer = $lossOrder->customer;
        $lossOrder->delete();

        log_action('Loss Order deleted: ' . $lossOrderId . ' for ' . $lossOrderCustomer);

        return response()->json(['message' => 'Loss Order deleted successfully.']);
    }
}
