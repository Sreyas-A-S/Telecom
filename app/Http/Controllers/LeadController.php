<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Employee;
use App\Models\LeadSource;
use App\Models\LeadCategory;
use App\Models\Product;
use App\Models\Category;
use App\Models\Followup;
use App\Models\Tax;
use App\Models\SubCategory;
use App\Models\Agent;
use App\Models\Dealership;
use App\Models\State;
use App\Models\ProductModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LeadsExport;
use App\Models\Client;
use App\Models\LossOrder;
use App\Models\District;
use App\Models\ModelSeries;
use App\Models\ClientProduct;
use Illuminate\Support\Facades\Storage;
use OpenApi\Annotations as OA;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\TaskOverviewExport;


class LeadController extends Controller
{

    public function index(Request $request)
    {

        if (!checkMenu(Session::get('role_id'), 5, 'read') && !checkMenu(Session::get('role_id'), 12, 'read')) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            } else {
                abort(403);
            }
        }

        if ($request->ajax()) {
            /** @var \App\Models\User $user */
            $user = Auth::user();


            $isEmployee = ($user && $user->user_type === 'employee');
            if ($isEmployee) {
                $user->load('employee.role');
            }


            $isLeadManager = ($isEmployee && $user->employee && $user->employee->role && ($user->employee->role->role == 'sales_manager' || $user->employee->role->role == 'Sales Manager'));


            $data = Lead::with(['agent', 'leadSource', 'leadCategory', 'product', 'productModel', 'followups', 'dealership', 'client', 'employee', 'items.product', 'items.productModel'])->select('leads.*')->orderBy('created_at', 'desc');


            if ($isLeadManager && $isEmployee && $user->employee && $user->employee->dealership_id !== null) {
                $data->where('dealership_id', $user->employee->dealership_id);
            }


            if ($request->filled('employee_assignment_status')) {
                if ($request->input('employee_assignment_status') === 'assigned') {
                    $data->whereNotNull('employee_id');


                    if (!$isLeadManager && $isEmployee && $user->employee) {
                        $data->where('employee_id', $user->employee->id);
                    }
                } elseif ($request->input('employee_assignment_status') === 'unassigned') {
                    $data->whereNull('employee_id');


                    if ($isEmployee && $user->employee && $user->employee->dealership_id !== null) {
                        $data->where('dealership_id', $user->employee->dealership_id);
                    }
                }
            }

            if ($request->filled('followup_filter')) {
                if ($request->input('followup_filter') === 'today') {
                    $data->whereHas('followups', function ($query) {
                        $query->whereDate('next_follow_up_date', today());
                    });
                }
            }

            if ($request->filled('status')) {
                $data->where('status', $request->input('status'));
            }

            if ($request->filled('has_followup')) {
                if ($request->input('has_followup') === 'no') {
                    $data->whereHas('followups');
                } elseif ($request->input('has_followup') === 'yes') {
                    $data->whereDoesntHave('followups');
                }
            }


            if ($request->filled('lead_category_id')) {
                $data->where('lead_category_id', $request->input('lead_category_id'));
            }

            if ($request->filled('lead_source_id')) {
                $data->where('lead_source_id', $request->input('lead_source_id'));
            }

            if ($request->filled('from_date') || $request->filled('to_date')) {
                $data->whereHas('followups', function ($query) use ($request) {
                    if ($request->filled('from_date')) {
                        $query->whereDate('next_follow_up_date', '>=', $request->input('from_date'));
                    }
                    if ($request->filled('to_date')) {
                        $query->whereDate('next_follow_up_date', '<=', $request->input('to_date'));
                    }
                });
            }

            if ($request->filled('dealership_id')) {
                $data->where('dealership_id', $request->input('dealership_id'));
            }

            $dataTable = DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('name', function ($row) {
                    return [
                        'salutation' => $row->salutation,
                        'name' => $row->name,
                        'company' => $row->company,
                        'email' => $row->email,
                        'phone_number' => $row->phone_number
                    ];
                })
                ->orderColumn('name', function ($query, $order) {
                    $query->orderBy('name', $order);
                })

                ->addColumn('agent_data', function ($row) {
                    return [
                        'agent_name' => $row->agent ? $row->agent->name : 'N/A',
                        'agent_profile_pic' => ($row->agent && isset($row->agent->profile_pic)) ? $row->agent->profile_pic : null,
                        'leadSource' => $row->leadSource // Pass the full leadSource object
                    ];
                })
                ->addColumn('product', function ($row) {
                    return [
                        'primary_product' => $row->product,
                        'primary_product_model' => $row->productModel,
                        'lead_value' => $row->lead_value,
                        'items' => $row->items // Include all items
                    ];
                })
                ->addColumn('quantity', function ($row) {
                    return $row->quantity ?? 'N/A';
                })
                ->addColumn('financier', function ($row) {
                    return $row->financier ?? 'N/A';
                })
                ->addColumn('type', function ($row) {
                    return $row->type ?? 'N/A';
                })
                ->addColumn('login_status', function ($row) {
                    return $row->login_status ?? 'N/A';
                })
                ->addColumn('stage', function ($row) {
                    return $row->stage ?? 'N/A';
                })
                ->addColumn('remarks', function ($row) {
                    return $row->remarks ?? 'N/A';
                })
                ->orderColumn('product', function ($query, $order) {
                    $query->orderBy('lead_value', $order);
                })

                ->addColumn('is_client', function ($row) {
                    return $row->client_id !== null;
                })
                ->addColumn('latest_followup_date', function ($row) {
                    $latestFollowup = $row->followups->sortByDesc('created_at')->first();
                    return $latestFollowup ? $latestFollowup->next_follow_up_date : '';
                })
                ->orderColumn('latest_followup_date', function ($query, $order) {
                    $query->leftJoin('followups', function ($join) {
                        $join->on('leads.id', '=', 'followups.lead_id')
                            ->whereRaw('followups.id = (SELECT MAX(id) FROM followups WHERE lead_id = leads.id)');
                    })->orderBy('followups.next_follow_up_date', $order);
                })
                ->addColumn('leadCategory.name', function ($row) {
                    return $row->leadCategory ? $row->leadCategory->name : 'N/A';
                })


                ->addColumn('allow_follow_up', function ($row) {
                    return $row->allow_follow_up;
                })
                ->addColumn('status', function ($row) {
                    return $row->status;
                })
                ->addColumn('assigned_employee', function ($row) {
                    if ($row->employee) {
                        $profilePic = $row->employee->profile_pic ? Storage::url($row->employee->profile_pic) : asset('admin/assets/images/avtar/4.jpg');
                        $row->employee = '<span class="d-flex align-items-center"><img src="' . $profilePic . '" class="rounded-circle me-2" width="24" height="24" style="object-fit: cover;">' . $row->employee->name . '</span>';
                        return $row->employee;
                    }
                    return 'N/A';
                })->rawColumns(['assigned_employee', 'action'])

                ->addColumn('chance_of_success', function ($row) {
                    return (int)($row->chance_of_success ?? 0);
                })
                ->orderColumn('chance_of_success', function ($query, $order) {
                    $query->orderBy('chance_of_success', $order);
                })
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                    $btn .= '<li class="call"><a href="javascript:void(0)" title="Call Lead" onclick="window.exotelService.dialLead(\'' . $row->phone_number . '\', ' . $row->id . ')"><i class="icon-headphone" style="color: #51bb25;"></i></a></li>';
                    $btn .= '<li class="view"><a title="View" href="/leads/' . $row->id . '/profile"><i class="icon-eye"></i></a></li>';
                    $btn .= '<li class="edit"><a href="javascript:void(0)" title="Edit" data-id="' . $row->id . '" class="edit-lead-btn"><i class="icon-pencil-alt"></i></a></li>';
                    $btn .= '<li class="delete"><a title="Delete" href="javascript:void(0)" data-id="' . $row->id . '" data-lead-name="' . $row->name . '" class="delete-lead-btn"><i class="icon-trash"></i></a></li>';
                    $btn .= '</ul>';
                    return $btn;
                });

            $dataTable->filter(function ($query) use ($request) {
                if ($request->has('search.value') && !empty($request->input('search.value'))) {
                    $keyword = $request->input('search.value');
                    $assignment_status = $request->input('employee_assignment_status');

                    if ($assignment_status === 'assigned') {
                        $query->where(function ($q) use ($keyword) {

                            $q->orWhere('salutation', 'like', "%{$keyword}%")
                                ->orWhere('name', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%")
                                ->orWhere('phone_number', 'like', "%{$keyword}%");

                            $q->orWhereHas('client', function ($query) use ($keyword) {
                                $query->where('name', 'like', "%{$keyword}%")
                                    ->orWhere('email', 'like', "%{$keyword}%")
                                    ->orWhere('phone_number', 'like', "%{$keyword}%");
                            });


                            $q->orWhereHasMorph('agent', [Employee::class], function ($morphQuery) use ($keyword) {
                                $morphQuery->where('name', 'like', "%{$keyword}%")
                                    ->orWhere('email', 'like', "%{$keyword}%")
                                    ->orWhere('mobile', 'like', "%{$keyword}%");
                            });
                            $q->orWhereHasMorph('agent', [Agent::class], function ($morphQuery) use ($keyword) {
                                $morphQuery->where('name', 'like', "%{$keyword}%")
                                    ->orWhere('email', 'like', "%{$keyword}%")
                                    ->orWhere('phone_number', 'like', "%{$keyword}%");
                            });
                            $q->orWhereHas('leadSource', function ($q2) use ($keyword) {
                                $q2->where('name', 'like', "%{$keyword}%");
                            });


                            $q->orWhereHas('product', function ($q2) use ($keyword) {
                                $q2->where('name', 'like', "%{$keyword}%");
                            })->orWhere('lead_value', 'like', "%{$keyword}%");


                            $q->orWhereHas('leadCategory', function ($q2) use ($keyword) {
                                $q2->where('name', 'like', "%{$keyword}%");
                            })->orWhereHas('dealership', function ($q2) use ($keyword) {
                                $q2->where('name', 'like', "%{$keyword}%");
                            });


                            $q->orWhere('status', 'like', "%{$keyword}%");
                        });
                    } else { // unassigned
                        $query->where(function ($q) use ($keyword) {

                            $q->orWhere('salutation', 'like', "%{$keyword}%")
                                ->orWhere('name', 'like', "%{$keyword}%")
                                ->orWhere('email', 'like', "%{$keyword}%")
                                ->orWhere('phone_number', 'like', "%{$keyword}%");

                            $q->orWhereHas('client', function ($query) use ($keyword) {
                                $query->where('name', 'like', "%{$keyword}%")
                                    ->orWhere('email', 'like', "%{$keyword}%")
                                    ->orWhere('phone_number', 'like', "%{$keyword}%");
                            });


                            $q->orWhereHasMorph('agent', [Employee::class], function ($morphQuery) use ($keyword) {
                                $morphQuery->where('name', 'like', "%{$keyword}%")
                                    ->orWhere('email', 'like', "%{$keyword}%")
                                    ->orWhere('mobile', 'like', "%{$keyword}%");
                            });
                            $q->orWhereHasMorph('agent', [Agent::class], function ($morphQuery) use ($keyword) {
                                $morphQuery->where('name', 'like', "%{$keyword}%")
                                    ->orWhere('email', 'like', "%{$keyword}%")
                                    ->orWhere('phone_number', 'like', "%{$keyword}%");
                            });
                            $q->orWhereHas('leadSource', function ($q2) use ($keyword) {
                                $q2->where('name', 'like', "%{$keyword}%");
                            });


                            $q->orWhereHas('product', function ($q2) use ($keyword) {
                                $q2->where('name', 'like', "%{$keyword}%");
                            })->orWhere('lead_value', 'like', "%{$keyword}%");


                            $q->orWhereHas('leadCategory', function ($q2) use ($keyword) {
                                $q2->where('name', 'like', "%{$keyword}%");
                            })->orWhereHas('dealership', function ($q2) use ($keyword) {
                                $q2->where('name', 'like', "%{$keyword}%");
                            });
                        });
                    }
                }
            });

            return $dataTable->make(true);
        }

        $agents = Employee::where('is_broker', 1)->get();
        $employees = Employee::all(); // Fetch all employees for assignment
        $designations = Employee::select('designation')->distinct()->get();
        $products = Product::all();
        $leadSources = LeadSource::all();
        $leadCategories = LeadCategory::all();
        $dealerships = Dealership::all();
        $statuses = Lead::select('status')->distinct()->get();
        $keralaState = State::where('name', 'Kerala')->first();
        $keralaDistricts = $keralaState ? $keralaState->districts()->get() : collect();



        $permissions = [
            'can_create' => checkMenu(Session::get('role_id'), 5, 'create'),
            'can_update' => checkMenu(Session::get('role_id'), 5, 'update'),
            'can_delete' => checkMenu(Session::get('role_id'), 5, 'delete'),
        ];

        $showUnassignedLeadsTab = true;

        $user = Auth::user();
        if ($user && $user->user_type === 'employee') {
            $user->load('employee');
            if ($user->employee && $user->employee->dealership_id !== null) {
                $showUnassignedLeadsTab = false;
            }
        }
        $showUnassignedLeadsTab = true;

        return view('leads.index', compact('agents', 'employees', 'products', 'designations', 'leadSources', 'leadCategories', 'dealerships', 'statuses', 'permissions', 'keralaDistricts', 'showUnassignedLeadsTab'));
    }

    public function show(Lead $lead)
    {

        $lead->load('agent', 'leadSource', 'leadCategory', 'product', 'productModel', 'modelSeries', 'dealership', 'items.product', 'items.productModel', 'items.modelSeries');

        // Conditionally set employee_code if the agent is an Employee
        if ($lead->agent_type === \App\Models\Employee::class && $lead->agent) {
            // Directly access employee_code if it exists on the agent (which is an Employee model)
            $lead->agent->employee_code = $lead->agent->employee_code ?? null;
        }
        // dd($lead->lead_value);

        $response = [
            'id' => $lead->id,
            'salutation' => $lead->salutation, // Add this line
            'name' => $lead->name,
            'company' => $lead->company,
            'email' => $lead->email,
            'phone_number' => $lead->phone_number,
            'alternate_contact_number' => $lead->alternate_contact_number,
            'agent' => $lead->agent ? [
                'id' => $lead->agent->id,
                'name' => $lead->agent->name,
                'type' => class_basename($lead->agent_type), // Get just the class name (e.g., 'Employee' or 'Agent')
                'employee_code' => ($lead->agent_type === \App\Models\Employee::class && isset($lead->agent->employee_id)) ? $lead->agent->employee_id : null,
            ] : null,
            'lead_source' => $lead->leadSource,
            'lead_category' => $lead->leadCategory,
            'lead_value' => $lead->lead_value ? (int)$lead->lead_value : null,
            'allow_follow_up' => $lead->allow_follow_up,
            'status' => $lead->status,
            'product' => $lead->product,
            'product_model' => $lead->productModel,
            'product_variant' => $lead->modelSeries,
            'machine_serial_number' => $lead->machine_serial_number,
            'engine_serial_number' => $lead->engine_serial_number,
            'location' => $lead->location,
            'map_location' => $lead->map_location,
            'latitude' => $lead->latitude,
            'longitude' => $lead->longitude,
            'quantity' => $lead->quantity,
            'financier' => $lead->financier,
            'type' => $lead->type,
            'login_status' => $lead->login_status,
            'stage' => $lead->stage,
            'billing' => $lead->billing ? $lead->billing->format('Y-m') : null,
            'remarks' => $lead->remarks,
            'dealership' => $lead->dealership, // Add this line
            'chance_of_success' => $lead->chance_of_success,
            'items' => $lead->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product ? $item->product->name : '',
                    'product_model_id' => $item->product_model_id,
                    'product_model_name' => $item->productModel ? $item->productModel->name : '',
                    'model_series_id' => $item->model_series_id,
                    'model_series_name' => $item->modelSeries ? $item->modelSeries->name : '',
                    'machine_serial_number' => $item->machine_serial_number,
                    'engine_serial_number' => $item->engine_serial_number,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ];
            }),
        ];
        return response()->json($response);
    }
    public function profile(Lead $lead)
    {

        $lead->load('agent', 'leadSource', 'leadCategory', 'product', 'productModel', 'modelSeries', 'dealership');

        $followUpPermissions = [
            'can_read' => checkMenu(Session::get('role_id'), 11, 'read'),
            'can_create' => checkMenu(Session::get('role_id'), 11, 'create'),
            'can_update' => checkMenu(Session::get('role_id'), 11, 'update'),
            'can_delete' => checkMenu(Session::get('role_id'), 11, 'delete'),
        ];

        return view('leads.profile', compact('lead', 'followUpPermissions'));
    }


    public function update(Request $request, Lead $lead = null)
    {
        if (is_null($lead)) {
            $lead = Lead::find($request->input('id'));
            if (!$lead) {
                return response()->json(['message' => 'Lead not found.'], 404);
            }
        }

        $request->validate([
            'salutation' => 'nullable|string',
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'alternate_contact_number' => 'nullable|string|max:20',
            'agent_id' => 'nullable|integer',
            'agent_type' => 'nullable|string|in:App\Models\Employee,App\Models\Agent',
            'lead_source' => 'nullable|string',
            'lead_category' => 'nullable|string',
            'lead_value' => 'nullable|numeric',
            'allow_follow_up' => 'boolean',
            'product' => 'nullable|string',
            'product_model' => 'nullable|string',
            'model_series' => 'nullable|string',
            'machine_serial_number' => 'nullable|string',
            'engine_serial_number' => 'nullable|string',
            'dealership_id' => 'nullable|integer',
            'location' => 'nullable|string',
            'financier' => 'nullable|string',
            'type' => 'nullable|string',
            'login_status' => 'nullable|string',
            'stage' => 'nullable|string',
            'remarks' => 'nullable|string',
            'map_location' => 'nullable|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
        ]);

        $lead->salutation = $request->salutation;
        $lead->name = $request->name;
        $lead->company = $request->company;
        $lead->email = $request->email;
        $lead->phone_number = $request->phone_number;
        $lead->alternate_contact_number = $request->alternate_contact_number;
        if ($request->has('agent_id')) {
            $lead->agent_id = $request->agent_id;
        }
        if ($request->has('agent_type')) {
            if ($request->input('agent_type') === 'App\\Models\\Employee') {
                $lead->agent_type = Employee::class;
            } elseif ($request->input('agent_type') === 'App\\Models\\Agent') {
                $lead->agent_type = Agent::class;
            } else {
                $lead->agent_type = null;
            }
        }
        $lead->lead_value = $request->lead_value;
        $lead->allow_follow_up = $request->allow_follow_up;
        $lead->location = $request->location;
        $lead->machine_serial_number = $request->machine_serial_number;
        $lead->engine_serial_number = $request->engine_serial_number;
        $lead->financier = $request->financier;
        $lead->type = $request->type;
        $lead->login_status = $request->login_status;
        $lead->stage = $request->stage;
        $lead->billing = $request->billing;
        $lead->remarks = $request->remarks;
        $lead->map_location = $request->map_location;
        $lead->latitude = $request->latitude;
        $lead->longitude = $request->longitude;

        $user = Auth::user();
        if ($user && $user->user_type === 'employee') {
            $user->load('employee');
            if ($user->employee && $user->employee->dealership_id !== null) {

                $lead->dealership_id = $user->employee->dealership_id;
            } else {

                $lead->dealership_id = $request->input('dealership_id');
            }
        } else {

            $lead->dealership_id = $request->input('dealership_id');
        }


        if ($request->lead_source) {
            $leadSource = LeadSource::firstOrCreate(['name' => $request->lead_source]);
            $lead->lead_source_id = $leadSource->id;
        }


        if ($request->lead_category) {
            $leadCategory = LeadCategory::firstOrCreate(['name' => $request->lead_category]);
            $lead->lead_category_id = $leadCategory->id;
        }


        if ($request->product) {
            $product = Product::firstOrCreate(['name' => $request->product]);
            $lead->product_id = $product->id;
        }


        if ($request->product_model && $lead->product_id) {
            $productModel = ProductModel::firstOrCreate(
                ['name' => $request->product_model, 'product_id' => $lead->product_id],
                ['description' => '']
            );
            $lead->product_model_id = $productModel->id;
        } else {
            $lead->product_model_id = null;
        }

        if ($request->model_series && $lead->product_model_id) {
            $modelSeries = ModelSeries::firstOrCreate(
                ['name' => $request->model_series, 'product_model_id' => $lead->product_model_id]
            );
            $lead->model_series_id = $modelSeries->id;
        } else {
            $lead->model_series_id = null;
        }

        $lead->save();

        // Handle multiple products
        $items = $request->input('items', []);
        if (empty($items) && $request->product) {
            $items[] = [
                'product_name' => $request->product,
                'product_model_name' => $request->product_model,
                'model_series_name' => $request->model_series,
                'machine_serial_number' => $request->machine_serial_number,
                'engine_serial_number' => $request->engine_serial_number,
                'quantity' => $request->quantity ?? 1,
            ];
        }
        $lead->syncItems($items);

        return response()->json(['message' => 'Lead updated successfully.']);
    }

    public function destroy(Lead $lead)
    {

        $lead->delete();

        return response()->json(['message' => 'Lead deleted successfully.']);
    }

    public function exportExcel(Request $request)
    {
        $filters = [
            'followup_filter' => $request->query('followup_filter'),
            'search_value' => $request->input('search.value') ?? $request->query('search_value'),
            'status' => $request->query('status'),
            'has_followup' => $request->query('has_followup'),
            'lead_category_id' => $request->query('lead_category_id'),
            'lead_source_id' => $request->query('lead_source_id'),
            'dealership_id' => $request->query('dealership_id'),
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
            'employee_assignment_status' => $request->query('employee_assignment_status'),
        ];

        $user = Auth::user();
        if ($user && $user->user_type !== 'admin') {
            $user->load('employee');
            if ($user->employee && $user->employee->dealership_id !== null) {
                $filters['user_dealership_id'] = $user->employee->dealership_id;
            }
        }

        return Excel::download(new LeadsExport($filters), 'leads.xlsx');
    }


    public function searchClientsByPhone(Request $request)
    {
        $search = $request->input('phone_number') ?? $request->input('q');
        $mode = $request->input('mode');
        $user = Auth::user();

        $clients = Client::where(function ($query) use ($search) {
            $query->where('phone_number', 'like', '%' . $search . '%')
                ->orWhere('name', 'like', '%' . $search . '%');
        });

        // Filter by dealership_id if the current user is an employee with a dealership_id
        if ($user && $user->user_type === 'employee') {
            $user->load('employee');
            if ($user->employee && $user->employee->dealership_id !== null) {
                $clients->where(function ($q) use ($user) {
                    $q->where('dealership_id', $user->employee->dealership_id)
                        ->orWhereNull('dealership_id');
                });
            }
        }

        $clients = $clients->select('id', 'salutation', 'name', 'email', 'phone_number')
            ->withCount('leads')
            ->get()
            ->map(function ($client) {
                $client->is_lead = false;
                return $client;
            });

        if ($mode === 'includeLeads') {
            $leadsQuery = Lead::query()->whereNull('client_id')
                ->where(function ($query) use ($search) {
                    $query->where('phone_number', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%');
                });

            if ($user && $user->user_type === 'employee') {
                if ($user->employee && $user->employee->dealership_id !== null) {
                    $leadsQuery->where(function ($q) use ($user) {
                        $q->where('dealership_id', $user->employee->dealership_id)
                            ->orWhereNull('dealership_id');
                    });
                }
            }

            $leads = $leadsQuery->select('id', 'salutation', 'name', 'email', 'phone_number')
                ->get()
                ->map(function ($lead) {
                    $lead->is_lead = true;
                    $lead->leads_count = 0;
                    return $lead;
                });

            $clients = $clients->concat($leads);
        }

        return response()->json($clients);
    }

    public function searchAllClients(Request $request)
    {
        $search = $request->input('phone_number') ?? $request->input('q');

        $clients = Client::where(function ($query) use ($search) {
            $query->where('phone_number', 'like', '%' . $search . '%')
                ->orWhere('name', 'like', '%' . $search . '%');
        })
        ->select('id', 'salutation', 'name', 'email', 'phone_number')
        ->withCount('leads')
        ->limit(20)
        ->get();

        return response()->json($clients);
    }

    public function getClientHistory(Client $client)
    {
        $client->load(['leads.items', 'services', 'products']);

        $clientController = new \App\Http\Controllers\ClientController();
        $exportData = $clientController->getClientExportData($client);

        return response()->json([
            'total_leads' => $client->leads->count(),
            'total_services' => $client->services->count(),
            'total_products' => collect($exportData['uniqueProducts'])->count()
        ]);
    }



    public function store(Request $request, \App\Services\TaskService $taskService)
    {


        $request->validate([
            'salutation' => 'nullable|string',
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'alternate_contact_number' => 'nullable|string|max:20',
            'agent_id' => 'nullable|integer',
            'agent_type' => 'nullable|string|in:App\Models\Agent,App\Models\Employee',
            'lead_source' => 'nullable|string',
            'lead_category' => 'nullable|string',
            'lead_value' => 'nullable|numeric',
            'allow_follow_up' => 'boolean',
            'chance_of_success' => 'nullable|integer|min:0|max:100',
            'product' => 'nullable|string',
            'product_model' => 'nullable|string',
            'model_series' => 'nullable|string',
            'machine_serial_number' => 'nullable|string',
            'engine_serial_number' => 'nullable|string',
            'quantity' => 'nullable|integer|min:1',
            'dealership_id' => 'nullable|integer',
            'location' => 'nullable|string',
            'financier' => 'nullable|string',
            'type' => 'nullable|string',
            'login_status' => 'nullable|string',
            'stage' => 'nullable|string',
            'remarks' => 'nullable|string',
            'map_location' => 'nullable|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'employee_id' => 'nullable|exists:employees,id',
            'due_date' => 'nullable|date',
        ]);


        $lead = new Lead();
        $lead->salutation = $request->salutation;
        $lead->name = $request->name;
        $lead->company = $request->company;
        $lead->email = $request->email;
        $lead->phone_number = $request->phone_number;
        $lead->alternate_contact_number = $request->alternate_contact_number;

        if ($request->filled('agent_id') && $request->filled('agent_type')) {
            $agentId = $request->input('agent_id');
            $agentType = $request->input('agent_type');
            $finalAgentId = null;

            if ($agentType === 'Employee') {
                $employee = Employee::find($agentId);
                if ($employee) {
                    $agent = Agent::firstOrCreate(
                        ['employee_id' => $employee->id],
                        [
                            'name' => $employee->name,
                            'email' => $employee->email,
                            'phone_number' => $employee->mobile,
                            'is_employee' => true,
                        ]
                    );
                    $finalAgentId = $agent->id;
                }
            } else { // agent_type is 'Agent'
                $finalAgentId = $agentId;
            }

            $lead->agent_id = $finalAgentId;
            $lead->agent_type = Agent::class;
        }
        $lead->lead_value = $request->lead_value;
        $lead->allow_follow_up = $request->allow_follow_up;
        $lead->chance_of_success = $request->chance_of_success;
        $lead->location = $request->location;
        $lead->machine_serial_number = $request->machine_serial_number;
        $lead->engine_serial_number = $request->engine_serial_number;
        $lead->quantity = $request->quantity;
        $lead->financier = $request->financier;
        $lead->type = $request->type;
        $lead->login_status = $request->login_status;
        $lead->stage = $request->stage;
        $lead->billing = $request->billing;
        $lead->remarks = $request->remarks;
        $lead->map_location = $request->map_location;
        $lead->latitude = $request->latitude;
        $lead->longitude = $request->longitude;

        $user = Auth::user();
        if ($user && $user->user_type === 'employee') {
            $user->load('employee');
            if ($user->employee && $user->employee->dealership_id !== null) {

                $lead->dealership_id = $user->employee->dealership_id;
            } else {

                $lead->dealership_id = $request->input('dealership_id');
            }
        } else {

            $lead->dealership_id = $request->input('dealership_id');
        }


        if ($user) {
            $lead->user_id = $user->id;
        }


        if ($request->lead_source) {
            $leadSource = LeadSource::firstOrCreate(['name' => $request->lead_source]);

            $lead->lead_source_id = $leadSource->id;
        }


        if ($request->lead_category) {
            $leadCategory = LeadCategory::firstOrCreate(['name' => $request->lead_category]);
            $lead->lead_category_id = $leadCategory->id;
        }


        if ($request->product) {
            $product = Product::firstOrCreate(['name' => $request->product]);
            $lead->product_id = $product->id;
        }


        if ($request->product_model && $lead->product_id) {
            $productModel = ProductModel::firstOrCreate(
                ['name' => $request->product_model, 'product_id' => $lead->product_id],
                ['description' => '']
            );
            $lead->product_model_id = $productModel->id;
        }

        if ($request->model_series && $lead->product_model_id) {
            $modelSeries = ModelSeries::firstOrCreate(
                ['name' => $request->model_series, 'product_model_id' => $lead->product_model_id]
            );
            $lead->model_series_id = $modelSeries->id;
        }

        if ($request->filled('employee_id')) {
            $lead->employee_id = $request->input('employee_id');
        }

        $lead->save();

        // Handle multiple products
        $items = $request->input('items', []);
        if (empty($items) && $request->product) {
            $items[] = [
                'product_name' => $request->product,
                'product_model_name' => $request->product_model,
                'model_series_name' => $request->model_series,
                'machine_serial_number' => $request->machine_serial_number,
                'engine_serial_number' => $request->engine_serial_number,
                'quantity' => $request->quantity ?? 1,
            ];
        }
        $lead->syncItems($items);

        // Link to existing client by phone number if available
        if ($lead->phone_number) {
            $existingClient = Client::where('phone_number', $lead->phone_number)->first();
            if ($existingClient) {
                $lead->client_id = $existingClient->id;
                $lead->save();
            }
        }

        if ($lead->employee_id) {
            $taskService->createTasksForLead($request, $lead);
        }

        return response()->json(['message' => 'Lead created successfully.']);
    }


    public function updateStatus(Request $request, Lead $lead)
    {

        $request->validate([
            'status' => 'required|string',
        ]);

        $lead->status = $request->status;

        if ($lead->status === 'win') {
            $lead->chance_of_success = 100;
        } elseif ($lead->status === 'lost') {
            $lead->chance_of_success = 0;
        } elseif ($lead->status === 'pending') {
            $lead->chance_of_success = 25;
        } elseif ($lead->status === 'in progress') {
            $lead->chance_of_success = 50;
        } elseif ($lead->status === 'positive') {
            $lead->chance_of_success = 75;
        }

        $lead->save();

        $clientId = null;
        if ($lead->status === 'win' && $request->input('convert_to_client', true)) {
            $this->createClientFromLead($request, $lead);
            $clientId = $lead->client_id;
        } elseif ($lead->status === 'lost') {
            $this->createLossOrderFromLead($lead, $request->reason);
        }

        return response()->json([
            'message' => 'Lead status updated successfully.',
            'client_id' => $clientId
        ]);
    }

    public function updateChanceOfSuccess(Request $request, Lead $lead)
    {

        $request->validate([
            'chance_of_success' => 'required|integer|min:0|max:100',
            'reason' => 'nullable|string',
        ]);

        $lead->chance_of_success = $request->chance_of_success;

        if ($lead->chance_of_success == 100) {
            $lead->status = 'win';
        } elseif ($lead->chance_of_success == 0) {
            $lead->status = 'lost';
        } elseif ($lead->chance_of_success == 25) {
            $lead->status = 'pending';
        } elseif ($lead->chance_of_success == 50) {
            $lead->status = 'in progress';
        } elseif ($lead->chance_of_success == 75) {
            $lead->status = 'positive';
        }

        $lead->save();

        $clientId = null;
        if ($lead->chance_of_success == 100 && $request->input('convert_to_client', true)) {
            $this->createClientFromLead($request, $lead);
            $clientId = $lead->client_id;
        } elseif ($lead->chance_of_success == 0) {
            $this->createLossOrderFromLead($lead, $request->reason);
        }

        return response()->json([
            'message' => 'Success rate updated successfully!',
            'client_id' => $clientId
        ]);
    }

    private function createClientFromLead(Request $request, Lead $lead)
    {
        // Update lead with provided details if they exist in request
        if ($request->filled('billing')) {
            $lead->billing = $request->billing;
        }
        if ($request->filled('doc')) {
            $lead->doc = $request->doc;
        }
        $lead->save();

        // Check if client already exists for this lead or by phone number to avoid duplicates
        $client = null;
        if ($lead->client_id) {
            $client = Client::find($lead->client_id);
        }

        if (!$client) {
            $client = Client::where('phone_number', $lead->phone_number)->first();
        }

        if (!$client) {
            $client = new Client();
            $client->salutation = $lead->salutation;
            $client->name = $lead->name;
            $client->email = $lead->email;
            $client->phone_number = $lead->phone_number;
            $client->address = $lead->location; // Mapping location to address
            $client->dealership_id = $lead->dealership_id;
            $client->employee_id = $lead->employee_id; // Assigned employee
            $client->agent_type = $lead->agent_type;
            $client->agent_id = $lead->agent_id;
            $client->lead_source_id = $lead->lead_source_id;
            $client->lead_category_id = $lead->lead_category_id;
            $client->notes = $lead->remarks;
            $client->lead_id = $lead->id; // Link back to original lead
            $client->latitude = $lead->latitude;
            $client->longitude = $lead->longitude;
            $client->gps_location = $lead->latitude . ',' . $lead->longitude;
            $client->save();
        }

        // Update lead with client ID
        $lead->client_id = $client->id;
        $lead->last_status_before_conversion = $lead->status;
        $lead->status = 'win';
        $lead->chance_of_success = 100;
        $lead->save();

        // Link all other leads for this customer to the client
        Lead::where('phone_number', $lead->phone_number)
            ->whereNull('client_id')
            ->update(['client_id' => $client->id]);

        $itemDetails = $request->input('item_details', []);

        // Convert ALL products/machines from the lead items to client products
        $lead->load('items');
        if ($lead->items->isNotEmpty()) {
            foreach ($lead->items as $item) {
                // Determine if this item matches the lead's primary to inherit doc/engine_model info
                $isPrimary = ($item->product_id == $lead->product_id &&
                    $item->product_model_id == $lead->product_model_id &&
                    $item->model_series_id == $lead->model_series_id);

                // Get details for this specific item (array of units)
                $units = $itemDetails[$item->id] ?? [];

                // Update lead item with the first unit's info for reference (optional but good for history)
                if (!empty($units[0])) {
                    $item->update([
                        'machine_serial_number' => $units[0]['machine_serial'] ?? null,
                        'engine_serial_number' => $units[0]['engine_serial'] ?? null,
                        'engine_model' => $units[0]['engine_model'] ?? null,
                    ]);
                }

                // Create a ClientProduct for each unit (based on quantity)
                $quantity = $item->quantity ?? 1;
                for ($i = 0; $i < $quantity; $i++) {
                    $unitData = $units[$i] ?? [];

                    ClientProduct::create([
                        'client_id' => $client->id,
                        'product_id' => $item->product_id,
                        'product_model_id' => $item->product_model_id,
                        'model_series_id' => $item->model_series_id,
                        'machine_serial_number' => $unitData['machine_serial'] ?? null,
                        'engine_serial_number' => $unitData['engine_serial'] ?? null,
                        'engine_model' => $unitData['engine_model'] ?? (($isPrimary && $i === 0) ? ($lead->engine_model ?: null) : null),
                        'doc' => $lead->doc ?: null,
                        'dealership_id' => $lead->dealership_id,
                    ]);
                }
            }
        } elseif ($lead->product_id) {
            // Fallback to primary lead product if no specific items exist
            $quantity = $lead->quantity ?? 1;
            for ($i = 0; $i < $quantity; $i++) {
                ClientProduct::create([
                    'client_id' => $client->id,
                    'product_id' => $lead->product_id,
                    'product_model_id' => $lead->product_model_id,
                    'model_series_id' => $lead->model_series_id,
                    'machine_serial_number' => ($i === 0) ? ($request->machine_serial_number ?: $lead->machine_serial_number) : null,
                    'engine_serial_number' => ($i === 0) ? ($request->engine_serial_number ?: $lead->engine_serial_number) : null,
                    'engine_model' => ($i === 0) ? ($lead->engine_model ?: null) : null,
                    'doc' => $lead->doc ?: null,
                    'dealership_id' => $lead->dealership_id,
                ]);
            }
        }
    }

    private function createLossOrderFromLead(Lead $lead, $reason = null)
    {
        $lead->load(['product', 'productModel', 'modelSeries', 'employee']);

        LossOrder::create([
            'lead_id' => $lead->id,
            'month' => date('Y-m'),
            'dealership_id' => $lead->dealership_id,
            'product_name' => $lead->product->name ?? 'N/A',
            'product_model_name' => $lead->productModel->name ?? null,
            'model_series_name' => $lead->modelSeries->name ?? null,
            'customer' => $lead->name,
            'financier' => $lead->financier,
            'district' => $lead->location,
            'reasons_for_loss' => $reason,
            'remarks' => $lead->remarks,
            'engineer_name' => $lead->employee->name ?? null,
        ]);
    }


    public function storeLeadSource(Request $request)
    {

        $request->validate(['name' => 'required|unique:lead_sources,name']);
        $leadSource = LeadSource::create(['name' => $request->name]);
        return response()->json($leadSource);
    }


    public function storeLeadCategory(Request $request)
    {

        $request->validate(['name' => 'required|unique:lead_categories,name']);
        $leadCategory = LeadCategory::create(['name' => $request->name]);
        return response()->json($leadCategory);
    }


    public function revertConversion(Lead $lead)
    {
        if (!checkMenu(\Illuminate\Support\Facades\Session::get('role_id'), 14, 'create')) {
            return response()->json(['message' => 'You do not have permission to revert conversions.'], 403);
        }

        \Illuminate\Support\Facades\Log::info('Reverting conversion for Lead ID: ' . $lead->id);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $client = $lead->client ?: (\App\Models\Client::find($lead->client_id));

            // 1. Restore Lead status and success rate
            $lead->status = $lead->last_status_before_conversion ?: 'positive';
            $lead->chance_of_success = ($lead->status === 'positive') ? 75 : 50;
            $lead->client_id = null;
            $lead->last_status_before_conversion = null;
            $lead->save();

            // 2. If this lead was the primary creator of the client, delete the client
            if ($client && $client->lead_id == $lead->id) {                // Unlink all other leads first
                \App\Models\Lead::where('client_id', $client->id)->update(['client_id' => null]);

                // Delete client products
                $client->products()->delete();

                // Delete the client
                $client->delete();

                $message = 'Lead #' . $lead->id . ' reverted. Associated client record deleted as this was the primary lead.';
                $redirectUrl = route('leads.profile', $lead->id);
            } else {
                $message = 'Lead #' . $lead->id . ' reverted and unlinked from client successfully.';
                $redirectUrl = null; // Stay on current page
            }

            \Illuminate\Support\Facades\DB::commit();

            return response()->json([
                'message' => $message,
                'redirect_url' => $redirectUrl
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Error reverting conversion: ' . $e->getMessage());
            return response()->json(['message' => 'Error reverting conversion.', 'error' => $e->getMessage()], 500);
        }
    }

    public function assignEmployee(Request $request, Lead $lead, \App\Services\TaskService $taskService)
    {


        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'due_date' => 'nullable|date',
        ]);

        $employee = Employee::find($request->employee_id);

        $lead->employee_id = $employee->id;
        $lead->save();

        // Create task for the assigned employee
        $taskService->createTasksForLead($request, $lead);

        return response()->json(['message' => 'Employee assigned successfully.']);
    }


    public function storeProduct(Request $request)
    {

        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'category' => 'required',
            'sub_category' => 'required',
            'hsn_sac' => 'nullable',
            'unit_type' => 'nullable',
            'description' => 'nullable',
            'tax' => 'nullable',
        ]);

        $product = new Product();
        $product->name = $request->name;
        $product->price = $request->price;
        $product->hsn_sac = $request->hsn_sac;
        $product->description = $request->description;
        $product->unit_type = $request->unit_type;


        if ($request->category) {
            $category = Category::firstOrCreate(['name' => $request->category]);
            $product->category_id = $category->id;
        }


        if ($request->sub_category) {

            $defaultCategory = Category::firstOrCreate(['name' => 'Default']);

            $subCategory = SubCategory::firstOrCreate(
                ['name' => $request->sub_category],
                ['category_id' => $defaultCategory->id]
            );
            $product->sub_category_id = $subCategory->id;
        }


        if ($request->tax) {
            $tax = Tax::where('name', $request->tax)->first();
            if (!$tax) {
                $tax = Tax::create(['name' => $request->tax, 'rate' => 0]);
            }
            $product->tax_id = $tax->id;
        }

        $product->save();

        return response()->json($product);
    }


    public function indexLeadSources()
    {

        $leadSources = LeadSource::all();
        return response()->json(['data' => $leadSources]);
    }

    public function indexLeadCategories()
    {

        $leadCategories = LeadCategory::all();
        return response()->json(['data' => $leadCategories]);
    }


    public function getFollowups(Request $request, Lead $lead)
    {

        if ($request->ajax()) {
            $data = $lead->followups()->with(['user.employee.department'])->orderBy('created_at', 'desc');
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('created_at', function ($row) {
                    return $row->created_at->format('Y-m-d H:i:s');
                })
                ->addColumn('user_info', function ($row) {
                    $user = $row->user;
                    if (!$user) return null;

                    $profilePic = $user->profile_pic ? asset('storage/' . $user->profile_pic) : asset('assets/images/user/1.jpg');
                    $name = $user->name;
                    $dept = $user->employee && $user->employee->department ? $user->employee->department->name : 'N/A';

                    return [
                        'profile_pic' => $profilePic,
                        'name' => $name,
                        'department' => $dept
                    ];
                })
                ->addColumn('action', function ($row) {
                    $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                    $btn .= '<li class="edit"><a href="javascript:void(0)" title="Edit" data-id="' . $row->id . '" class="edit-followup-btn"><i class="icon-pencil-alt"></i></a></li>';
                    $btn .= '<li class="delete"><a title="Delete" href="javascript:void(0)" data-id="' . $row->id . '" class="delete-followup-btn"><i class="icon-trash"></i></a></li>';
                    $btn .= '</ul>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function getFsrReports(Lead $lead)
    {
        $fsrReports = \App\Models\FSRReport::whereHas('task', function ($query) use ($lead) {
            $query->where('lead_id', $lead->id);
        })->with(['task', 'submittedBy'])->get();

        return response()->json(['data' => $fsrReports]);
    }



    public function getLeadTasks(Request $request, Lead $lead)
    {
        if ($request->ajax()) {
            $data = \App\Models\Task::with(['assignedEmployee.department', 'followups.user'])->where('lead_id', $lead->id)->orderBy('created_at', 'desc');

            return DataTables::eloquent($data)
                ->addIndexColumn()
                ->addColumn('assigned_to_info', function ($row) {
                    $employee = $row->assignedEmployee;
                    if (!$employee) return 'N/A';

                    $profilePic = $employee->profile_pic ? asset('storage/' . $employee->profile_pic) : asset('assets/images/user/7.jpg');
                    $dept = $employee->department->name ?? 'N/A';

                    $html = '<div class="d-flex align-items-center">';
                    $html .= '<img class="img-40 rounded-circle me-2" src="' . $profilePic . '" alt="">';
                    $html .= '<div class="flex-grow-1">';
                    $html .= '<div class="fw-bold">' . $employee->name . '</div>';
                    $html .= '<div class="small text-muted">' . $dept . '</div>';
                    $html .= '</div></div>';

                    return $html;
                })
                ->addColumn('time_spent', function ($row) {
                    return $row->getFormattedElapsedTime();
                })
                ->addColumn('task_followups', function ($row) {
                    return $row->followups->map(function ($f) {
                        return [
                            'id' => $f->id,
                            'notes' => $f->notes,
                            'images' => $f->images,
                            'created_at' => $f->created_at->format('d M Y H:i'),
                            'user' => $f->user->name ?? 'N/A'
                        ];
                    })->values()->all();
                })
                ->addColumn('created_at', function ($row) {
                    return $row->created_at->format('Y-m-d H:i:s');
                })
                ->editColumn('status', function ($row) {
                    return strtolower($row->status);
                })
                ->editColumn('due_date', function ($row) {
                    return $row->due_date ? $row->due_date->format('d M Y') : 'N/A';
                })
                ->editColumn('title', function ($row) {
                    return $row->title;
                })
                ->rawColumns(['assigned_to_info'])
                ->make(true);
        }
    }

    public function showTaskOverview(Lead $lead, \App\Models\Task $task)
    {
        // Ensure the task belongs to the lead (optional but recommended for security)
        if ($task->lead_id !== $lead->id) {
            abort(404, 'Task not found for this lead.');
        }

        $task->load(['followups.user', 'taskLogs.employee', 'assignedEmployee', 'fsrReport.partQuotations.part']);

        // Calculate task analytics (Total Time)
        $totalSeconds = $task->getElapsedTimeInSeconds();
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $totalTime = "$hours hrs $minutes mins";

        // Get logs
        $taskLogs = $task->taskLogs()->orderBy('created_at', 'desc')->get();

        return view('leads.task_overview', compact('task', 'totalTime', 'taskLogs', 'lead'));
    }

    public function exportTaskOverviewExcel(Lead $lead, \App\Models\Task $task)
    {
        if ($task->lead_id !== $lead->id) {
            abort(404, 'Task not found for this lead.');
        }

        $task->load(['followups.user', 'taskLogs.employee', 'assignedEmployee', 'fsrReport.partQuotations.part']);

        $totalSeconds = $task->getElapsedTimeInSeconds();
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $totalTime = "$hours hrs $minutes mins";

        $taskLogs = $task->taskLogs()->orderBy('created_at', 'desc')->get();

        return Excel::download(new TaskOverviewExport($task, $totalTime, $taskLogs), 'task_overview_' . $task->id . '.xlsx');
    }

    public function exportTaskOverviewPdf(Lead $lead, \App\Models\Task $task)
    {
        if ($task->lead_id !== $lead->id) {
            abort(404, 'Task not found for this lead.');
        }

        $task->load(['followups.user', 'taskLogs.employee', 'assignedEmployee', 'fsrReport.partQuotations.part']);

        $totalSeconds = $task->getElapsedTimeInSeconds();
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $totalTime = "$hours hrs $minutes mins";

        $taskLogs = $task->taskLogs()->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('leads.pdf-task-overview', compact('task', 'totalTime', 'taskLogs', 'lead'));
        return $pdf->download('task_overview_' . $task->id . '.pdf');
    }

    public function storeFollowup(Request $request, Lead $lead)
    {

        $request->validate([
            'next_follow_up_date' => 'nullable|date',
            'next_follow_up_time' => 'nullable|date_format:H:i',
            'new_status' => 'required|string',
            'remarks' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $nextFollowUpDateTime = null;
        if ($request->next_follow_up_date && $request->next_follow_up_time) {
            $nextFollowUpDateTime = $request->next_follow_up_date . ' ' . $request->next_follow_up_time;
        } else if ($request->next_follow_up_date) {
            $nextFollowUpDateTime = $request->next_follow_up_date;
        }

        $lead->followups()->create([
            'user_id' => auth()->id(),
            'next_follow_up_date' => $nextFollowUpDateTime,
            'new_status' => $request->new_status,
            'remarks' => $request->remarks,
        ]);

        $lead->status = $request->new_status;
        $lead->save();

        if ($lead->status === 'lost') {
            $this->createLossOrderFromLead($lead, $request->reason);
        }

        return response()->json(['message' => 'Follow up added successfully and lead status updated.']);
    }

    public function editFollowup(Lead $lead, Followup $followup)
    {

        return response()->json($followup);
    }

    public function updateFollowup(Request $request, Lead $lead, Followup $followup)
    {

        $request->validate([
            'next_follow_up_date' => 'nullable|date',
            'next_follow_up_time' => 'nullable|date_format:H:i',
            'new_status' => 'required|string',
            'remarks' => 'nullable|string',
            'reason' => 'nullable|string',
        ]);

        $nextFollowUpDateTime = null;
        if ($request->next_follow_up_date && $request->next_follow_up_time) {
            $nextFollowUpDateTime = $request->next_follow_up_date . ' ' . $request->next_follow_up_time;
        } else if ($request->next_follow_up_date) {
            $nextFollowUpDateTime = $request->next_follow_up_date;
        }

        $followup->update([
            'next_follow_up_date' => $nextFollowUpDateTime,
            'new_status' => $request->new_status,
            'remarks' => $request->remarks,
        ]);

        $lead->status = $request->new_status;
        $lead->save();

        if ($lead->status === 'lost') {
            $this->createLossOrderFromLead($lead, $request->reason);
        }

        return response()->json(['message' => 'Follow up updated successfully and lead status updated.']);
    }

    public function deleteFollowup(Lead $lead, Followup $followup)
    {

        $followup->delete();
        return response()->json(['message' => 'Follow up deleted successfully.']);
    }

    public function getLeadsApi(Request $request)
    {
        $data = Lead::with(['agent', 'leadSource', 'leadCategory', 'product', 'productModel', 'followups', 'dealership', 'client', 'employee'])->select('leads.*')->orderBy('created_at', 'desc');

        $user = Auth::user();
        if ($user && $user->user_type === 'employee') {
            $user->load('employee');
            if ($user->employee && $user->employee->dealership_id !== null) {
                $dealershipId = $user->employee->dealership_id;
                $data->where(function ($q) use ($dealershipId) {
                    $q->where('dealership_id', $dealershipId)
                        ->orWhereNull('dealership_id');
                });
            }
        }

        if ($request->filled('employee_assignment_status')) {
            if ($request->input('employee_assignment_status') === 'assigned') {
                $data->whereNotNull('employee_id');
            } elseif ($request->input('employee_assignment_status') === 'unassigned') {
                $data->whereNull('employee_id');
            }
        }

        if ($request->filled('followup_filter')) {
            if ($request->input('followup_filter') === 'today') {
                $data->whereHas('followups', function ($query) {
                    $query->whereDate('next_follow_up_date', today());
                });
            }
        }

        if ($request->filled('status')) {
            $data->where('status', $request->input('status'));
        }

        if ($request->filled('has_followup')) {
            if ($request->input('has_followup') === 'no') {
                $data->whereHas('followups');
            } elseif ($request->input('has_followup') === 'yes') {
                $data->whereDoesntHave('followups');
            }
        }

        if ($request->filled('lead_category_id')) {
            $data->where('lead_category_id', $request->input('lead_category_id'));
        }

        if ($request->filled('lead_source_id')) {
            $data->where('lead_source_id', $request->input('lead_source_id'));
        }

        if ($request->filled('dealership_id')) {
            $data->where('dealership_id', $request->input('dealership_id'));
        }

        if ($request->filled('from_date') || $request->filled('to_date')) {
            $data->whereHas('followups', function ($query) use ($request) {
                if ($request->filled('from_date')) {
                    $query->whereDate('next_follow_up_date', '>=', $request->input('from_date'));
                }
                if ($request->filled('to_date')) {
                    $query->whereDate('next_follow_up_date', '<=', $request->input('to_date'));
                }
            });
        }

        return DataTables::of($data)->make(true);
    }


    public function allFollowupsIndex()
    {
        return view('followups.index');
    }


    public function getAllFollowupsData(Request $request)
    {

        if ($request->ajax()) {
            $data = Followup::with('lead')->orderBy('created_at', 'desc');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('lead_name', function ($row) {
                    return $row->lead ? $row->lead->name : 'N/A';
                })
                ->addColumn('action', function ($row) {
                    return '';
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }
}
