<?php

namespace App\Http\Controllers;


use App\Exports\LeadsExport;
use App\Models\Dealership;

use App\Models\State;
use App\Models\LeadSource;
use App\Models\LeadCategory;
use App\Models\Employee;
use App\Models\Lead;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class PipelineController extends Controller
{
    public function index()
    {
        if (!checkMenu(Session::get('role_id'), 10, 'read')) {
            return redirect()->back()->with('error', 'You do not have permission to view the pipeline.');
        }
        $dealerships = Dealership::all();
        $keralaState = State::where('name', 'Kerala')->first();
        $keralaDistricts = $keralaState ? $keralaState->districts()->get() : collect();
        $leadSources = LeadSource::all();
        $leadCategories = LeadCategory::all();
        $employees = Employee::all();
        $products = Product::all();
        return view('pipelines.index', compact('dealerships', 'keralaDistricts', 'leadSources', 'leadCategories', 'employees', 'products'));
    }

    public function getDataTableData(Request $request)
    {
        $data = Lead::with(['leadSource', 'leadCategory', 'product', 'dealership', 'employee'])->select('leads.*');

        if ($request->filled('dealership_id')) {
            $data->where('dealership_id', $request->dealership_id);
        }
        if ($request->filled('lead_source_id')) {
            $data->where('lead_source_id', $request->lead_source_id);
        }
        if ($request->filled('lead_category_id')) {
            $data->where('lead_category_id', $request->lead_category_id);
        }
        if ($request->filled('status')) {
            $data->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $data->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $data->whereDate('created_at', '<=', $request->end_date);
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('dealer_info', function ($row) {
                return $row->dealership ? $row->dealership->name : 'N/A';
            })
            ->addColumn('customer_info', function ($row) {
                $phone = $row->phone_number ? '<div class="text-muted">' . $row->phone_number . '</div>' : '';
                $email = $row->email ? '<div class="text-muted">' . $row->email . '</div>' : '';
                return $row->salutation . ' ' . $row->name . $phone . $email;
            })
            ->filterColumn('customer_info', function ($query, $keyword) {
                $query->where(function ($q) use ($keyword) {
                    $q->orWhere('leads.name', 'like', "%{$keyword}%")
                        ->orWhere('leads.email', 'like', "%{$keyword}%")
                        ->orWhere('leads.phone_number', 'like', "%{$keyword}%");
                });
            })
            ->orderColumn('customer_info', function ($query, $order) {
                $query->orderBy('leads.name', $order);
            })
            ->addColumn('product_info', function ($row) {
                $productName = $row->product ? $row->product->name : 'N/A';
                $productModelName = $row->productModel ? ' - ' . $row->productModel->name : '';
                return $productName . $productModelName;
            })
            ->addColumn('location', function ($row) {
                return $row->location ?? 'N/A';
            })
            ->addColumn('lead_value_display', function ($row) {
                return $row->lead_value ? '₹' . number_format($row->lead_value, 2) : 'N/A';
            })
            ->addColumn('allow_follow_up_display', function ($row) {
                return $row->allow_follow_up ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>';
            })
            ->addColumn('lead_type', function ($row) {
                return $row->type ?? 'N/A';
            })
            ->addColumn('login_status_display', function ($row) {
                return $row->login_status ?? 'N/A';
            })
            ->addColumn('lead_stage', function ($row) {
                return $row->stage ?? 'N/A';
            })
            ->addColumn('billing_display', function ($row) {
                return $row->billing ?? 'N/A';
            })
            ->addColumn('remarks_display', function ($row) {
                $remarks = $row->remarks ?? 'N/A';
                $maxLength = 50; // Maximum length before truncating
    
                if (strlen($remarks) > $maxLength) {
                    $truncated = substr($remarks, 0, $maxLength);
                    return '<span class="remarks-truncated">' . $truncated . '...</span>' .
                        '<span class="remarks-full" style="display:none;">' . $remarks . '</span>' .
                        '<a href="javascript:void(0)" class="read-more-link" data-full-text="' . htmlspecialchars($remarks) . '">Read More</a>';
                }
                return $remarks;
            })
            ->addColumn('assigned_employee_name', function ($row) {
                return $row->employee ? $row->employee->name : 'Unassigned';
            })
            ->addColumn('probability_badge', function ($row) {
                $class = 'bg-secondary';
                if ($row->chance_of_success >= 75)
                    $class = 'bg-success';
                else if ($row->chance_of_success >= 50)
                    $class = 'bg-primary';
                else if ($row->chance_of_success >= 30)
                    $class = 'bg-warning';
                else
                    $class = 'bg-danger';
                return '<span class="badge ' . $class . '">' . ($row->chance_of_success ?? 0) . '%</span>';
            })
            ->addColumn('type_badge', function ($row) {
                return $row->leadSource ? '<span class="badge bg-light text-dark">' . $row->leadSource->name . '</span>' : 'N/A';
            })
            ->addColumn('stage_badge', function ($row) {
                return $row->leadCategory ? '<span class="badge bg-dark">' . $row->leadCategory->name . '</span>' : 'N/A';
            })
            ->addColumn('status_badge', function ($row) {
                return '<span class="badge bg-info">' . $row->status . '</span>';
            })
            ->addColumn('actions', function ($row) {
                $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                $btn .= '<li class="view"><a title="View" href="' . route('leads.profile', $row->id) . '"><i class="icon-eye"></i></a></li>';
                $btn .= '<li class="edit"><a href="javascript:void(0)" title="Edit" data-id="' . $row->id . '" class="edit-lead-btn"><i class="icon-pencil-alt"></i></a></li>';
                $btn .= '<li class="pdf"><a href="' . route('pipelines.export-pdf-row', $row->id) . '" title="Export PDF" target="_blank"><i class="icon-printer"></i></a></li>';
                $btn .= '<li class="delete"><a title="Delete" href="javascript:void(0)" data-id="' . $row->id . '" data-lead-name="' . $row->name . '" class="delete-lead-btn"><i class="icon-trash"></i></a></li>';
                $btn .= '</ul>';
                return $btn;
            })
            ->rawColumns(['actions', 'dealer_info', 'customer_info', 'probability_badge', 'type_badge', 'stage_badge', 'status_badge', 'allow_follow_up_display', 'assigned_employee_name', 'remarks_display'])
            ->filter(function ($query) use ($request) {
                if ($request->has('search.value') && !empty($request->input('search.value'))) {
                    $keyword = $request->input('search.value');
                    $query->where(function ($q) use ($keyword) {
                        $q->orWhereHas('dealership', function ($q) use ($keyword) {
                            $q->where('name', 'like', "%{$keyword}%");
                        })
                            ->orWhereRaw("LOWER(CONCAT(leads.salutation, ' ', leads.name)) LIKE ?", ["%{$keyword}%"]) // Search salutation + name
                            ->orWhere('leads.location', 'like', "%{$keyword}%") // Search location
                            ->orWhereHas('productModel', function ($q) use ($keyword) {
                                $q->where('name', 'like', "%{$keyword}%");
                            }) // Search product model name
                            ->orWhere('leads.lead_value', 'like', "%{$keyword}%")
                            ->orWhere('leads.allow_follow_up', 'like', "%{$keyword}%")
                            ->orWhere('leads.type', 'like', "%{$keyword}%")
                            ->orWhere('leads.login_status', 'like', "%{$keyword}%")
                            ->orWhere('leads.stage', 'like', "%{$keyword}%")
                            ->orWhere('leads.billing', 'like', "%{$keyword}%")
                            ->orWhere('leads.remarks', 'like', "%{$keyword}%")
                            ->orWhereHas('employee', function ($q) use ($keyword) {
                                $q->where('name', 'like', "%{$keyword}%");
                            })
                            ->orWhere('leads.financier', 'like', "%{$keyword}%");
                    });
                }
            })
            ->make(true);
    }





    public function exportExcel(Request $request)
    {
        if (!checkMenu(Session::get('role_id'), 10, 'read')) {
            return redirect()->back()->with('error', 'You do not have permission to export leads.');
        }

        $filters = $request->all();
        return Excel::download(new LeadsExport($filters), 'pipelines.xlsx');
    }


    public function exportPdfRow($id)
    {
        if (!checkMenu(Session::get('role_id'), 10, 'read')) {
            return redirect()->back()->with('error', 'You do not have permission to export leads.');
        }

        $lead = Lead::with(['leadSource', 'leadCategory', 'product', 'dealership', 'employee', 'productModel'])->findOrFail($id);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pipelines.pdf', compact('lead'));
        return $pdf->download('pipeline_' . $lead->id . '.pdf');
    }
}
