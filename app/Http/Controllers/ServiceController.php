<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Client; // Import Client model
use App\Models\Employee;
use App\Models\Product; // Import Product model
use App\Models\ProductModel; // Import ProductModel model
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Support\Str; // Import Str facade
use App\Models\ModelSeries;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ServicesExport;
// Import the OneSignal facade class so static analyzers and IDEs recognize the type
use Berkayk\OneSignal\OneSignalFacade as OneSignal;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log; // Import the Log facade
use Illuminate\Support\Facades\Session;
use App\Models\Notification;
use App\Services\TaskService;

class ServiceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $userRole = $user && $user->employee && $user->employee->role ? $user->employee->role->role : null;

        $showDealershipColumn = true;
        $dealerships = \App\Models\Dealership::where('brand', 1)->get();
        $zones = \App\Models\Zone::all();

        return view('services.index', compact('userRole', 'dealerships', 'showDealershipColumn', 'zones'));
    }

    public function getDataTableData(Request $request)
    {
        if ($request->ajax()) {
            if ($request->assignment_status === 'unassigned' && !checkMenu(Session::get('role_id'), 17, 'read')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            } elseif ($request->assignment_status === 'assigned' && !checkMenu(Session::get('role_id'), 18, 'read')) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            // Assuming assigned services might have a different menu ID, or no specific check here if it's generally accessible
            // For now, only protecting unassigned services with ID 17

            $user = Auth::user();
            $dealershipId = $user->employee->dealership_id ?? null;

            $query = Service::with(['client', 'product', 'productModel', 'modelSeries', 'serviceEngineer', 'serviceEngineer2', 'tasks.followups', 'dealership'])
                ->leftJoin('clients', 'services.client_id', '=', 'clients.id')
                ->leftJoin('dealerships', 'services.dealership_id', '=', 'dealerships.id')
                ->leftJoin('products', 'services.product_id', '=', 'products.id')
                ->leftJoin('product_models', 'services.product_model_id', '=', 'product_models.id')
                ->leftJoin('model_series', 'services.model_series_id', '=', 'model_series.id')
                ->leftJoin('employees as eng1', 'services.service_engineer_id', '=', 'eng1.id')
                ->leftJoin('employees as eng2', 'services.service_engineer_id_2', '=', 'eng2.id')
                ->select(
                    'services.*',
                    'services.name as service_name',
                    'clients.name as client_name_joined',
                    'dealerships.name as dealership_name_joined',
                    'products.name as product_name_joined',
                    'product_models.name as product_model_name_joined',
                    'model_series.name as model_series_name_joined',
                    'eng1.name as engineer1_name',
                    'eng2.name as engineer2_name'
                );

            if ($dealershipId) {
                $query->where(function ($q) use ($dealershipId) {
                    $q->where('services.dealership_id', $dealershipId)
                        ->orWhereNull('services.dealership_id');
                });
            }

            if ($request->has('dealership_id') && !empty($request->dealership_id)) {
                $dealershipIdFilter = $request->dealership_id;
                $dealership = \App\Models\Dealership::find($dealershipIdFilter);
                if ($dealership) {
                    $dealershipName = $dealership->name;
                    $query->where(function ($q) use ($dealershipIdFilter, $dealershipName) {
                        $q->where('services.dealership_id', $dealershipIdFilter)
                            ->orWhereHas('product', function ($pq) use ($dealershipName) {
                                $pq->whereRaw('LOWER(name) like ?', ['%' . strtolower($dealershipName) . '%']);
                            });
                    });
                }
            }

            if ($request->has('zone_id') && !empty($request->zone_id)) {
                $query->where('services.zone_id', $request->zone_id);
            }

            if ($request->has('assignment_status')) {
                if ($request->assignment_status === 'assigned') {
                    $query->whereNotNull('services.service_engineer_id');
                } elseif ($request->assignment_status === 'unassigned') {
                    $query->whereNull('services.service_engineer_id');
                }
            }

            $user = Auth::user();
            $userRole = $user && $user->employee && $user->employee->role ? $user->employee->role->role : null;

            $dataTable = DataTables::of($query)
                ->filterColumn('service_name', function ($q, $keyword) {
                    $q->where(function ($sub) use ($keyword) {
                        $sub->where('services.name', 'like', "%{$keyword}%")
                            ->orWhere('services.referral_id', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('product_name', function ($q, $keyword) {
                    $q->where(function ($sub) use ($keyword) {
                        $sub->where('products.name', 'like', "%{$keyword}%")
                            ->orWhere('product_models.name', 'like', "%{$keyword}%")
                            ->orWhere('model_series.name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('service_engineer_name', function ($q, $keyword) {
                    $q->where(function ($sub) use ($keyword) {
                        $sub->where('eng1.name', 'like', "%{$keyword}%")
                            ->orWhere('eng2.name', 'like', "%{$keyword}%");
                    });
                })
                ->order(function ($q) use ($request) {
                    if ($request->has('sort_by')) {
                        $sortBy = $request->sort_by;
                        if ($sortBy === 'customer') {
                            $q->leftJoin('clients', 'services.client_id', '=', 'clients.id')
                                ->orderBy('clients.name', 'asc');
                        } elseif ($sortBy === 'complaint') {
                            $q->orderBy('services.name', 'asc');
                        } else {
                            // Default: date (assigned_at desc for assigned, created_at desc for unassigned)
                            if ($request->assignment_status === 'assigned') {
                                $q->orderBy('services.assigned_at', 'desc');
                            } else {
                                $q->orderBy('services.created_at', 'desc');
                            }
                        }
                    } else {
                        // DataTables handles its own sorting if sort_by isn't present
                        // but we want a default if nothing is clicked
                        $columns = $request->get('order');
                        if (empty($columns)) {
                            if ($request->assignment_status === 'assigned') {
                                $q->orderBy('services.assigned_at', 'desc');
                            } else {
                                $q->orderBy('services.created_at', 'desc');
                            }
                        }
                    }
                })
                ->addIndexColumn()
                ->addColumn('created_date', function ($row) {
                    return $row->created_at ? $row->created_at->format('d M Y, h:i A') : 'N/A';
                })
                ->addColumn('assigned_date', function ($row) {
                    return $row->assigned_at ? $row->assigned_at->format('d M Y, h:i A') : 'N/A';
                })
                ->addColumn('client_name', function ($row) {
                    return $row->client ? $row->client->name : 'N/A';
                })
                ->addColumn('dealership_name', function ($row) {
                    if ($row->dealership) {
                        return $row->dealership->name;
                    }
                    return $row->product ? $row->product->name : 'N/A';
                })
                ->addColumn('product_name', function ($row) {
                    return $row->product ? $row->product->name : 'N/A';
                })
                ->addColumn('product_model_name', function ($row) {
                    return $row->productModel ? $row->productModel->name : 'N/A';
                })
                ->addColumn('model_series_name', function ($row) {
                    return $row->modelSeries ? $row->modelSeries->name : 'N/A';
                })

                ->addColumn('status_and_service', function ($row) {
                    $machineStatus = $row->machine_status ? '<span class="badge bg-primary">' . ucfirst(str_replace('_', ' ', $row->machine_status)) . '</span>' : 'N/A';
                    return $machineStatus;
                })
                ->addColumn('service_engineer_name', function ($row) {
                    $engineers = [];
                    if ($row->serviceEngineer) {
                        $engineers[] = $row->serviceEngineer->name;
                    }
                    if ($row->serviceEngineer2) {
                        $engineers[] = $row->serviceEngineer2->name;
                    }
                    return empty($engineers) ? 'Not Assigned' : implode(', ', $engineers);
                })
                ->addColumn('contact_info', function ($row) {
                    return $row->contact_info ? $row->contact_info : 'N/A';
                })
                ->addColumn('status', function ($row) {
                    $activeTask = $row->tasks->first();
                    $status = $activeTask ? $activeTask->status : 'Requested';
                    $color = 'bg-warning';
                    if ($status == 'completed') {
                        $color = 'bg-success';
                    } elseif ($status == 'ongoing' || $status == 'in_progress') {
                        $color = 'bg-info';
                    } elseif ($status == 'pending') {
                        $color = 'bg-secondary';
                    }
                    return '<span class="badge ' . $color . ' text-white">' . ucfirst(str_replace('_', ' ', $status)) . '</span>';
                })
                ->addColumn('followups', function ($row) {
                    $followupCount = $row->tasks->sum(function ($task) {
                        return $task->followups->count();
                    });
                    if ($followupCount > 0) {
                        $url = route('entries.followups.index', $row->id);
                        return '<a href="' . $url . '" class="btn btn-sm btn-primary">View Follow-ups (' . $followupCount . ')</a>';
                    }
                    return 'No Follow-ups';
                })
                ->addColumn('actions', function ($row) use ($request) {
                    $btn = '<ul class="action d-flex justify-content-around list-unstyled gap-2">';
                    $btn .= '<li class="edit"><a title="Edit" href="javascript:void(0)" data-id="' . $row->id . '" data-name="' . $row->service_name . '" class="edit-entry-btn"><i class="icon-pencil"></i></a></li>';
                    $btn .= '<li class="delete"><a title="Delete" href="javascript:void(0)" data-id="' . $row->id . '" data-name="' . $row->service_name . '" class="delete-entry-btn"><i class="icon-trash"></i></a></li>';
                    $btn .= '<li class="view"><a title="View" href="javascript:void(0)" data-id="' . $row->id . '" class="view-entry-btn"><i class="icon-eye"></i></a></li>';
                    $btn .= '<li class="assign-engineer"><a title="Assign Engineer" href="javascript:void(0)" data-id="' . $row->id . '" class="assign-engineer-btn"><i class="fa fa-user-edit"></i></a></li>';
                    $rawColumns = ['actions', 'status_and_service']; // Initialize rawColumns
                    $btn .= '</ul>';
                    return $btn;
                });
            $rawColumns = ['actions', 'status_and_service', 'followups', 'status']; // Initialize rawColumns

            return $dataTable->rawColumns($rawColumns)
                ->make(true);
        }
    }

    public function getClients()
    {
        $user = Auth::user();
        if ($user->user_type === 'employee' && $user->employee && $user->employee->dealership_id) {
            $clients = Client::where(function ($q) use ($user) {
                $q->where('dealership_id', $user->employee->dealership_id)
                    ->orWhereNull('dealership_id');
            })->get(['id', 'name', 'phone_number']);
        } else {
            $clients = Client::all(['id', 'name', 'phone_number']);
        }
        return response()->json(['clients' => $clients]);
    }


    public function getProducts(Request $request)
    {
        $clientId = $request->input('client_id');
        $dealershipId = $request->input('dealership_id');
        $user = Auth::user();

        // Load employee and dealership to get the name for filtering
        if ($user && $user->employee) {
            $user->employee->load('dealership');
        }

        $query = \App\Models\Product::with('category')
            ->select('products.*')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->orderBy('products.name');

        // Filter by dealership name
        $dealershipName = null;
        if ($dealershipId) {
            $dealership = \App\Models\Dealership::find($dealershipId);
            if ($dealership) {
                $dealershipName = $dealership->name;
            }
        } elseif ($user->employee && $user->employee->dealership) {
            $dealershipName = $user->employee->dealership->name;
        }

        if ($dealershipName && !$clientId) {
            $query->where('products.name', 'LIKE', '%' . $dealershipName . '%');
        }

        $products = $query->get();

        if ($clientId) {
            $client = Client::with(['leads.items', 'products', 'services'])->find($clientId);
            if ($client) {
                // Get product IDs from leads table (won/converted)
                $leadProductIds = $client->leads->whereIn('status', ['win', 'converted_to_client'])->pluck('product_id')->filter()->all();

                // Get product IDs from lead_items table
                $itemProductIds = $client->leads->whereIn('status', ['win', 'converted_to_client'])->flatMap(function ($lead) {
                    return $lead->items->pluck('product_id');
                })->filter()->all();

                // Get product IDs from client_products table
                $clientProductIds = $client->products->pluck('product_id')->filter()->all();

                // Get product IDs from existing services (legacy or ongoing)
                $serviceProductIds = $client->services->pluck('product_id')->filter()->all();

                $ownedProductIds = array_unique(array_merge($leadProductIds, $itemProductIds, $clientProductIds, $serviceProductIds));

                foreach ($products as $product) {
                    $product->is_owned = in_array($product->id, $ownedProductIds);
                }

                // Strictly filter to ONLY owned products if client is selected
                $products = $products->filter(function ($p) {
                    return $p->is_owned;
                });
            }
        }

        $products = $products->unique(function ($item) {
            return $item->name . '-' . ($item->category->name ?? '');
        })->values();

        return response()->json(['products' => $products]);
    }


    public function getProductModels(Request $request)
    {
        $productId = $request->input('product_id');
        $clientId = $request->input('client_id');

        $productModels = ProductModel::where('product_id', $productId)->orderBy('name')->get(['id', 'name']);

        if ($clientId && $productId) {
            $client = Client::with(['leads.items', 'products', 'services'])->find($clientId);
            if ($client) {
                $leadModelIds = $client->leads->whereIn('status', ['win', 'converted_to_client'])->where('product_id', $productId)->pluck('product_model_id')->filter()->all();
                $itemModelIds = $client->leads->whereIn('status', ['win', 'converted_to_client'])->flatMap(function ($lead) use ($productId) {
                    return $lead->items->where('product_id', $productId)->pluck('product_model_id');
                })->filter()->all();
                $clientModelIds = $client->products->where('product_id', $productId)->pluck('product_model_id')->filter()->all();
                $serviceModelIds = $client->services->where('product_id', $productId)->pluck('product_model_id')->filter()->all();

                $ownedModelIds = array_unique(array_merge($leadModelIds, $itemModelIds, $clientModelIds, $serviceModelIds));

                foreach ($productModels as $model) {
                    $model->is_owned = in_array($model->id, $ownedModelIds);
                }

                // Strictly filter to ONLY owned models when a client is selected
                $productModels = $productModels->filter(function ($m) {
                    return $m->is_owned;
                });
            }
        }

        return response()->json(['product_models' => $productModels]);
    }


    public function getModelSeries2(Request $request)
    {
        $productModelIds = $request->input('product_model_ids') ?? $request->input('product_model_id');
        $clientId = $request->input('client_id');

        if (empty($productModelIds)) {
            return response()->json(['model_series' => []]);
        }

        if (!is_array($productModelIds)) {
            $productModelIds = [$productModelIds];
        }
        $productModelIds = array_unique($productModelIds);

        $allModelSeries = \App\Models\ModelSeries::whereIn('product_model_id', $productModelIds)->orderBy('name')->get(['id', 'name', 'price']);

        if ($clientId) {
            $client = Client::with(['leads.items', 'products', 'services'])->find($clientId);
            if ($client) {
                $leadSeriesIds = $client->leads->whereIn('status', ['win', 'converted_to_client'])->whereIn('product_model_id', $productModelIds)->pluck('model_series_id')->filter()->all();
                $itemSeriesIds = $client->leads->whereIn('status', ['win', 'converted_to_client'])->flatMap(function ($lead) use ($productModelIds) {
                    return $lead->items->whereIn('product_model_id', $productModelIds)->pluck('model_series_id');
                })->filter()->all();
                $clientSeriesIds = $client->products->whereIn('product_model_id', $productModelIds)->pluck('model_series_id')->filter()->all();
                $serviceSeriesIds = $client->services->whereIn('product_model_id', $productModelIds)->pluck('model_series_id')->filter()->all();

                $ownedSeriesIds = array_unique(array_merge($leadSeriesIds, $itemSeriesIds, $clientSeriesIds, $serviceSeriesIds));

                // IMPORTANT: Handle products that DON'T have a model_series_id but DO have a machine_serial_number
                // Unique by machine_serial_number right here
                $manualProducts = $client->products()
                    ->whereIn('product_model_id', $productModelIds)
                    ->whereNull('model_series_id')
                    ->whereNotNull('machine_serial_number')
                    ->get()
                    ->unique('machine_serial_number');

                foreach ($manualProducts as $mp) {
                    // Create a pseudo-series object for the dropdown
                    $pseudoSeries = new \stdClass();
                    // We use a negative ID or string to indicate it's from client_products directly
                    // but the JS expects a numeric ID often. Let's use machine_serial_number as the value
                    // Or we can use a composite ID. Let's see how the JS handles it.
                    // The JS calls entries.product-details with the ID.
                    $pseudoSeries->id = "manual_" . $mp->id;
                    $pseudoSeries->name = $mp->machine_serial_number . ($mp->engine_serial_number ? " (Eng: " . $mp->engine_serial_number . ")" : "");
                    $pseudoSeries->price = 0;
                    $pseudoSeries->is_owned = true;
                    $pseudoSeries->is_manual = true;
                    
                    $allModelSeries->push($pseudoSeries);
                }

                foreach ($allModelSeries as $series) {
                    if (isset($series->is_manual)) continue;

                    $series->is_owned = in_array($series->id, $ownedSeriesIds);

                    // Augment with engine serial number for better identification
                    $engineSerial = null;

                    // Check products
                    $cp = $client->products->where('model_series_id', $series->id)->first();
                    if ($cp && $cp->engine_serial_number) {
                        $engineSerial = $cp->engine_serial_number;
                    }

                    if (!$engineSerial) {
                        $lead = $client->leads->where('model_series_id', $series->id)->whereNotNull('engine_serial_number')->first();
                        if ($lead) {
                            $engineSerial = $lead->engine_serial_number;
                        }
                    }

                    if (!$engineSerial) {
                        $svc = $client->services->where('model_series_id', $series->id)->whereNotNull('engine_serial_number')->first();
                        if ($svc) {
                            $engineSerial = $svc->engine_serial_number;
                        }
                    }

                    if ($engineSerial) {
                        $series->name = $series->name . ' (Eng: ' . $engineSerial . ')';
                    }
                }

                // Strictly filter to ONLY owned serial numbers when a client is selected
                $allModelSeries = $allModelSeries->filter(function ($s) {
                    return $s->is_owned;
                });
            }
        } else {
            // If no clientId, just return all series as they were
            foreach ($allModelSeries as $series) {
                $series->is_owned = false;
            }
        }

        $allModelSeries = $allModelSeries->unique(function ($item) {
            $name = strtolower(trim($item->name));
            // Separate the underlying series name from the '(Eng: XXX)' tag just for the uniqueness check
            $baseName = explode(' (eng:', $name)[0];
            return trim($baseName);
        })->sort(function ($a, $b) {
            // Sort by is_owned DESC (true/1 before false/0), then by name ASC
            if ($a->is_owned != $b->is_owned) {
                return $b->is_owned <=> $a->is_owned;
            }
            return strcasecmp($a->name, $b->name);
        })->values();

        return response()->json(['model_series' => $allModelSeries]);
    }

    /**
     * Get specific product details for a client's asset.
     */
    public function getProductDetails(Request $request)
    {
        $clientId = $request->input('client_id');
        $productId = $request->input('product_id');
        $productModelId = $request->input('product_model_id');
        $modelSeriesId = $request->input('model_series_id');
        $machineSerialInput = $request->input('machine_serial_number');

        if (!$clientId || !$productId) return response()->json(null);

        $machineSerialFromId = null;
        if ($modelSeriesId && !str_starts_with($modelSeriesId, 'manual_')) {
            $ms = \App\Models\ModelSeries::find($modelSeriesId);
            if ($ms) {
                $machineSerialFromId = $ms->name;
            }
        }

        $machineSerial = $machineSerialInput ?: $machineSerialFromId;

        // Check if this is a manual pseudo-ID
        if ($modelSeriesId && str_starts_with($modelSeriesId, 'manual_')) {
            $clientProductId = str_replace('manual_', '', $modelSeriesId);
            $cp = \App\Models\ClientProduct::find($clientProductId);
            if ($cp) {
                return response()->json([
                    'doc' => $cp->doc,
                    'engine_model' => $cp->engine_model,
                    'engine_serial_number' => $cp->engine_serial_number,
                    'machine_serial_number' => $cp->machine_serial_number
                ]);
            }
        }

        // 1. Try ClientProduct table
        $query = \App\Models\ClientProduct::where('client_id', $clientId)
            ->where('product_id', $productId);

        if ($productModelId) $query->where('product_model_id', $productModelId);
        
        $query->where(function($q) use ($modelSeriesId, $machineSerial) {
            if ($modelSeriesId && !str_starts_with($modelSeriesId, 'manual_')) {
                $q->where('model_series_id', $modelSeriesId);
                if ($machineSerial) {
                    $q->orWhere('machine_serial_number', $machineSerial)
                      ->orWhere('machine_serial_number', 'LIKE', '%' . $machineSerial . '%');
                }
            } elseif ($machineSerial) {
                $q->where('machine_serial_number', $machineSerial)
                  ->orWhere('machine_serial_number', 'LIKE', '%' . $machineSerial . '%');
            }
        });

        $details = $query->first();

        if (!$details) {
            // 2. Try Leads
            $leadQuery = \App\Models\Lead::where('client_id', $clientId)
                ->where('product_id', $productId)
                ->whereIn('status', ['win', 'converted_to_client']);

            $leadQuery->where(function($q) use ($modelSeriesId, $machineSerial) {
                if ($modelSeriesId && !str_starts_with($modelSeriesId, 'manual_')) {
                    $q->where('model_series_id', $modelSeriesId);
                    if ($machineSerial) {
                        $q->orWhere('machine_serial_number', $machineSerial)
                          ->orWhere('machine_serial_number', 'LIKE', '%' . $machineSerial . '%');
                    }
                } elseif ($machineSerial) {
                    $q->where('machine_serial_number', $machineSerial)
                      ->orWhere('machine_serial_number', 'LIKE', '%' . $machineSerial . '%');
                }
            });

            $lead = $leadQuery->first();
            if ($lead) {
                return response()->json([
                    'doc' => $lead->doc,
                    'engine_model' => $lead->engine_model,
                    'engine_serial_number' => $lead->engine_serial_number,
                    'machine_serial_number' => $lead->machine_serial_number
                ]);
            }

            // 3. Try Services (Legacy fallback)
            $svcQuery = \App\Models\Service::where('client_id', $clientId)
                ->where('product_id', $productId);

            if ($machineSerial) {
                $svcQuery->where(function($q) use ($machineSerial) {
                    $q->where('machine_serial_number', $machineSerial)
                      ->orWhere('machine_serial_number', 'LIKE', '%' . $machineSerial . '%');
                });
                
                $svc = $svcQuery->first();
                if ($svc) {
                    return response()->json([
                        'doc' => $svc->doc,
                        'engine_model' => $svc->engine_model,
                        'engine_serial_number' => $svc->engine_serial_number,
                        'machine_serial_number' => $svc->machine_serial_number
                    ]);
                }
            }
        }

        return response()->json($details);
    }


    public function getDealerships()
    {
        $dealerships = \App\Models\Dealership::where('brand', 1)->get(['id', 'name']);
        return response()->json(['dealerships' => $dealerships]);
    }

    public function getServiceEngineers(Request $request)
    {
        $user = Auth::user();
        $dealershipId = $request->input('dealership_id');

        $query = Employee::where(function ($q) {
            $q->whereHas('role', function ($sq) {
                $sq->where(function ($ssq) {
                    $ssq->whereRaw('LOWER(REPLACE(role, "_", " ")) = ?', ['service engineer'])
                        ->orWhereRaw('LOWER(REPLACE(role, " ", "_")) = ?', ['service_engineer'])
                        ->orWhereRaw('UPPER(role) = ?', ['SERVICE ENGINEER'])
                        ->orWhereRaw('LOWER(role) = ?', ['service engineer']);
                });
            })
            ->orWhereRaw('LOWER(REPLACE(designation, "_", " ")) = ?', ['service engineer'])
            ->orWhereRaw('LOWER(REPLACE(designation, " ", "_")) = ?', ['service_engineer'])
            ->orWhereRaw('UPPER(designation) = ?', ['SERVICE ENGINEER'])
            ->orWhereRaw('LOWER(designation) = ?', ['service engineer']);
        });

        if ($dealershipId && $dealershipId !== 'all') {
            $query->where('dealership_id', $dealershipId);
        }

        $serviceEngineers = $query->with('dealership:id,name')->get(['id', 'role_id', 'name', 'dealership_id']);

        $formattedEngineers = $serviceEngineers->map(function ($engineer) use ($dealershipId) {
            $dealershipName = $engineer->dealership ? ' (' . $engineer->dealership->name . ')' : ' (No Dealership)';
            $isSameDealership = ($dealershipId && $dealershipId !== 'all' && $engineer->dealership_id == $dealershipId);

            return [
                'id' => $engineer->id,
                'name' => $engineer->name . $dealershipName,
                'is_same_dealership' => $isSameDealership,
                'dealership_id' => $engineer->dealership_id
            ];
        });

        return response()->json(['service_engineers' => $formattedEngineers]);
    }

    /**
     * Generate a unique referral ID with the format SVHE(ddmmyy)NN.
     *
     * @return string
     */
    protected function generateUniqueReferralId()
    {
        // Get current date in ddmmyy format
        $date = \Carbon\Carbon::now()->format('dmy');
        $prefix = 'SVHE' . $date;

        // Find the highest existing sequence number for today's date
        $latestService = Service::where('referral_id', 'like', $prefix . '%')
            ->orderBy('referral_id', 'desc')
            ->first();

        $sequence = 1;
        if ($latestService) {
            // Extract the numeric part and increment
            $lastReferralId = $latestService->referral_id;
            $lastSequence = (int) substr($lastReferralId, -4); // Assuming NN is always 4 digits
            $sequence = $lastSequence + 1;
        }

        // Format the sequence number with leading zeros
        $referralId = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        // Ensure the generated referral ID is unique (though with this logic, it should be)
        while (Service::where('referral_id', $referralId)->exists()) {
            $sequence++;
            $referralId = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        }

        return $referralId;
    }


    public function store(Request $request, TaskService $taskService)
    {
        try {
            $request->validate([
                'client_id' => 'required|exists:clients,id',
                'product_id' => 'required|exists:products,id',
                'product_model_id' => 'nullable|exists:product_models,id',
                'model_series_id' => [
                    'nullable',
                    function ($attribute, $value, $fail) {
                        if (is_numeric($value)) {
                            if (!\App\Models\ModelSeries::where('id', $value)->exists()) {
                                $fail('The selected model series id is invalid.');
                            }
                        } elseif (is_string($value) && str_starts_with($value, 'manual_')) {
                            $id = str_replace('manual_', '', $value);
                            if (!\App\Models\ClientProduct::where('id', $id)->exists()) {
                                $fail('The selected manual serial number is invalid.');
                            }
                        } else {
                            $fail('The selected model series id is invalid.');
                        }
                    },
                ],

                'name' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'requested_location' => 'nullable|string|max:255',
                'referral_id' => 'nullable|string|max:255',
                'machine_status' => 'nullable|string|in:warranty,extended_warranty,post_warranty',
                'type_of_service' => 'nullable|string|in:free_service,warranty_claimable,warranty_coupon_service,campaign,paid_service,coupon_service,amc,warranty_free_service,warranty_mandatory,goodwill',
                'contact_info' => 'nullable|string|max:255',
                'service_engineer_id' => 'nullable|exists:employees,id',
                'service_engineer_id_2' => [
                    'nullable',
                    'exists:employees,id',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($request->filled('service_engineer_id') && $request->filled('service_engineer_id_2') && $request->service_engineer_id == $value) {
                            $fail('The second service engineer cannot be the same as the first service engineer.');
                        }
                    },
                ],
                'latitude' => 'nullable|numeric',
                'longitude' => 'nullable|numeric',
                'price' => 'nullable|numeric|min:0',
                'due_date_1' => 'nullable|date',
                'due_date_2' => 'nullable|date',
                'contact_person' => 'nullable|string|max:255',
                'doc' => 'nullable|date',
                'failure_date' => 'nullable|date',
                'failure_hmr' => 'nullable|integer|min:0',
                'call_status' => 'nullable|string|in:opened,closed,cancelled',
                'call_remarks' => 'nullable|string',
                'zone_id' => 'nullable|exists:zones,id',
            ]);

            $referralId = $this->generateUniqueReferralId();

            $user = Auth::user();
            $dealershipId = $request->input('dealership_id') ?? ($user->employee->dealership_id ?? null);
            $employeeId = $user->employee->id ?? null;

            $serviceName = trim((string) $request->input('name', ''));
            if ($serviceName === '') {
                $serviceName = null;
            }

            $data = $request->only([
                'client_id',
                'product_id',
                'product_model_id',
                'zone_id',
                'description',
                'machine_status',
                'type_of_service',
                'contact_info',
                'contact_person',
                'doc',
                'failure_date',
                'failure_hmr',
                'service_engineer_id',
                'service_engineer_id_2',
                'latitude',
                'longitude',
                'price',
                'due_date_1',
                'due_date_2',
                'call_status',
                'call_remarks',
            ]);

            // Handle manual_ model_series_id
            $modelSeriesId = $request->input('model_series_id');
            if ($modelSeriesId && str_starts_with($modelSeriesId, 'manual_')) {
                $clientProductId = str_replace('manual_', '', $modelSeriesId);
                $cp = \App\Models\ClientProduct::find($clientProductId);
                if ($cp) {
                    $data['model_series_id'] = $cp->model_series_id;
                    $data['machine_serial_number'] = $cp->machine_serial_number;
                    // Also take engine serial if not provided
                    if (!$request->filled('engine_serial_number')) {
                        $data['engine_serial_number'] = $cp->engine_serial_number;
                    }
                }
            } else {
                $data['model_series_id'] = $modelSeriesId;
            }

            $data['requested_location'] = $request->input('requested_location') ?? $request->input('location') ?? $request->input('map_location');
            $data['name'] = $serviceName;
            $data['referral_id'] = $referralId;
            $data['dealership_id'] = $dealershipId;
            $data['employee_id'] = $employeeId;

            if ($request->filled('service_engineer_id') || $request->filled('service_engineer_id_2')) {
                $data['assigned_at'] = now();
            }

            $service = Service::create($data);

            if ($service->service_engineer_id || $service->service_engineer_id_2) {
                $taskService->createTasksForService($request, $service);
            }

            return response()->json(['message' => 'Service created successfully.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error creating service entry: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
                'user_id' => Auth::id()
            ]);
            return response()->json(['message' => 'Error creating service entry: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $entry = Service::with(['client', 'product', 'productModel', 'modelSeries', 'serviceEngineer', 'serviceEngineer2', 'tasks', 'zone'])->findOrFail($id);
        return response()->json($entry);
    }

    public function show(Service $entry)
    {
        $entry->load([
            'client',
            'product',
            'productModel',
            'serviceEngineer',
            'serviceEngineer2'
        ]);
        return view('services.show', ['service' => $entry]);
    }

    public function update(Request $request, Service $entry, TaskService $taskService)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'product_id' => 'required|exists:products,id',
            'product_model_id' => 'nullable|exists:product_models,id',
            'model_series_id' => 'nullable|exists:model_series,id',

            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'requested_location' => 'nullable|string|max:255',
            'referral_id' => 'nullable|string|max:255',
            'machine_status' => 'nullable|string|in:warranty,extended_warranty,post_warranty',
            'type_of_service' => 'nullable|string|in:free_service,warranty_claimable,warranty_coupon_service,campaign,paid_service,coupon_service,amc,warranty_free_service,warranty_mandatory,goodwill',
            'contact_info' => 'nullable|string|max:255',
            'service_engineer_id' => 'nullable|exists:employees,id',
            'service_engineer_id_2' => [
                'nullable',
                'exists:employees,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->filled('service_engineer_id') && $request->filled('service_engineer_id_2') && $request->service_engineer_id == $value) {
                        $fail('The second service engineer cannot be the same as the first service engineer.');
                    }
                },
            ],
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'price' => 'nullable|numeric|min:0',
            'due_date_1' => 'nullable|date',
            'due_date_2' => 'nullable|date',
            'contact_person' => 'nullable|string|max:255',
            'doc' => 'nullable|date',
            'failure_date' => 'nullable|date',
            'failure_hmr' => 'nullable|integer|min:0',
            'call_status' => 'nullable|string|in:opened,closed,cancelled',
            'call_remarks' => 'nullable|string',
            'zone_id' => 'nullable|exists:zones,id',
        ]);

        $requestedReferralId = trim((string) $request->input('referral_id', ''));
        $referralId = $requestedReferralId !== '' ? $requestedReferralId : ($entry->referral_id ?: $this->generateUniqueReferralId());
        $serviceName = trim((string) $request->input('name', ''));
        if ($serviceName === '') {
            $serviceName = null;
        }

        $data = $request->only([
            'client_id',
            'product_id',
            'product_model_id',
            'model_series_id',
            'zone_id',

            'description',
            'machine_status',
            'type_of_service',
            'contact_info',
            'contact_person',
            'doc',
            'failure_date',
            'failure_hmr',
            'service_engineer_id',
            'service_engineer_id_2',
            'latitude',
            'longitude',
            'price',
            'call_status',
            'call_remarks',
        ]);

        if ($request->has('due_date_1')) {
            $data['due_date_1'] = $request->due_date_1;
        }
        if ($request->has('due_date_2')) {
            $data['due_date_2'] = $request->due_date_2;
        }

        $data['requested_location'] = $request->input('requested_location') ?? $request->input('location');
        $data['name'] = $serviceName;
        $data['referral_id'] = $referralId;
        if ($request->has('dealership_id')) {
            $data['dealership_id'] = $request->input('dealership_id');
        }

        $wasAssigned = $entry->service_engineer_id || $entry->service_engineer_id_2;
        $isAssigned = $request->filled('service_engineer_id') || $request->filled('service_engineer_id_2');

        if ($isAssigned) {
            if (!$wasAssigned || !$entry->assigned_at) {
                $data['assigned_at'] = now();
            }
        } else {
            $data['assigned_at'] = null;
        }

        $entry->update($data);

        if ($entry->service_engineer_id || $entry->service_engineer_id_2) {
            $taskService->createTasksForService($request, $entry);
        }
        $entry->refresh();

        return response()->json(['message' => 'Service updated successfully.']);
    }

    public function destroy(Service $entry)
    {
        $entry->delete();
        return response()->json(['message' => 'Service deleted successfully.']);
    }


    public function assignEngineer(Request $request, Service $entry, \App\Services\TaskService $taskService)
    {
        $request->validate([
            'service_engineer_id' => 'nullable|exists:employees,id',
            'service_engineer_id_2' => [
                'nullable',
                'exists:employees,id',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->filled('service_engineer_id') && $request->filled('service_engineer_id_2') && $request->service_engineer_id == $value) {
                        $fail('The second service engineer cannot be the same as the first service engineer.');
                    }
                },
            ],
            'due_date_1' => 'nullable|date',
            'due_date_2' => 'nullable|date',
        ]);

        $data = [
            'service_engineer_id' => $request->service_engineer_id,
            'service_engineer_id_2' => $request->service_engineer_id_2,
            'due_date_1' => $request->due_date_1,
            'due_date_2' => $request->due_date_2,
        ];

        $willBeAssigned = $request->service_engineer_id || $request->service_engineer_id_2;
        if ($willBeAssigned) {
            if (!$entry->assigned_at) {
                $data['assigned_at'] = now();
            }
        } else {
            $data['assigned_at'] = null;
        }

        Log::info('Assigning engineer(s) to service entry', [
            'entry_id' => $entry->id,
            'data' => $data
        ]);

        $entry->update($data);
        $entry->refresh(); // Ensure the model instance is updated with the new values

        Log::info('Service entry updated', [
            'entry' => $entry->toArray()
        ]);

        $taskService->createTasksForService($request, $entry);
        $entry->refresh();
        //get the task ids created for this service distinguished by service_engineer_id and service_engineer_id_2
        $tasks = $entry->tasks()->get();
        $taskIds = [];
        foreach ($tasks as $task) {
            if ($task->assigned_to == $request->service_engineer_id) {
                $taskIds['service_engineer_1_task_id'] = $task->id;
            } elseif ($task->assigned_to == $request->service_engineer_id_2) {
                $taskIds['service_engineer_2_task_id'] = $task->id;
            }
        }

        // Send notification to primary service engineer
        if ($request->service_engineer_id) {

            $employee = Employee::with('user')->find($request->service_engineer_id);
            if ($employee && $employee->user && $employee->user->player_id) {
                try {

                    //generate a notification_id that is not existing in the notifications table
                    do {
                        $notificationId = (string) Str::uuid();
                    } while (Notification::where('notification_id', $notificationId)->exists());

                    $title = "New Service Assigned";
                    $message = "You have been assigned a new service. Please check your dashboard for details.";
                    $payloadData = array_merge($data, [
                        'id' => $taskIds['service_engineer_1_task_id'] ?? null,
                        'route' => 'NotificationView',
                        'type' => 'new_service_assignment',
                        'notification_id' => $notificationId, // use the generated ID
                    ]);

                    $response = Http::withHeaders([
                        'Authorization' => 'Basic ' . env('ONESIGNAL_REST_API_KEY'),
                        'Content-Type' => 'application/json',
                    ])->post('https://onesignal.com/api/v1/notifications', [
                        'app_id' => env('ONESIGNAL_APP_ID'),
                        'include_aliases' => [
                            'external_id' => [$employee->user->email],
                        ],
                        'data' => $payloadData,
                        'target_channel' => 'push',
                        'priority' => 10,
                        'android_visibility' => 1,
                        'headings' => ['en' => $title],
                        'contents' => ['en' => $message],

                    ]);

                    // save the notification in the notifications table
                    Notification::create([
                        'notification_id' => $notificationId,
                        'user_id' => $employee->user_id,
                        'title' => $title,
                        'message' => $message,
                        'data' => $payloadData,
                    ]);
                    Log::info('OneSignal notification sent successfully to primary engineer.', [
                        'employee_id' => $employee->id,
                        'player_id' => $employee->user->player_id,
                        'service_id' => $entry->id,
                        'onesignal_response' => $response,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send OneSignal notification to primary engineer.', [
                        'employee_id' => $employee->id,
                        'player_id' => $employee->user->player_id,
                        'service_id' => $entry->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        // Send notification to secondary service engineer
        if ($request->service_engineer_id_2) {
            $employee = Employee::with('user')->find($request->service_engineer_id_2);
            if ($employee && $employee->user && $employee->user->player_id) {
                try {

                    //generate a notification_id that is not existing in the notifications table
                    do {
                        $notificationId = (string) Str::uuid();
                    } while (Notification::where('notification_id', $notificationId)->exists());

                    $title = "New Service Assigned";
                    $message = "You have been assigned a new service. Please check your dashboard for details.";
                    $payloadData = array_merge($data, [
                        'id' => $taskIds['service_engineer_2_task_id'] ?? null,
                        'route' => 'NotificationView',
                        'type' => 'new_service_assignment',
                        'notification_id' => $notificationId, // use the generated ID
                    ]);


                    $response = Http::withHeaders([
                        'Authorization' => 'Basic ' . env('ONESIGNAL_REST_API_KEY'),
                        'Content-Type' => 'application/json',
                    ])->post('https://onesignal.com/api/v1/notifications', [
                        'app_id' => env('ONESIGNAL_APP_ID'),
                        'include_aliases' => [
                            'external_id' => [$employee->user->email],
                        ],
                        'data' => $payloadData,
                        'target_channel' => 'push',
                        'priority' => 10,
                        'android_visibility' => 1,
                        'headings' => ['en' => $title],
                        'contents' => ['en' => $message],

                    ]);

                    // save the notification in the notifications table
                    Notification::create([
                        'notification_id' => $notificationId,
                        'user_id' => $employee->user_id,
                        'title' => $title,
                        'message' => $message,
                        'data' => $payloadData,
                    ]);

                    Log::info('OneSignal notification sent successfully to primary engineer.', [
                        'employee_id' => $employee->id,
                        'player_id' => $employee->user->player_id,
                        'service_id' => $entry->id,
                        'onesignal_response' => $response,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send OneSignal notification to primary engineer.', [
                        'employee_id' => $employee->id,
                        'player_id' => $employee->user->player_id,
                        'service_id' => $entry->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        // Send notification to service managers of the dealership(s) involved
        $dealershipIds = collect([])
            ->when($request->service_engineer_id, function ($collection) use ($request) {
                $employee = Employee::find($request->service_engineer_id, ['*']);
                return $employee ? $collection->push($employee->dealership_id) : $collection;
            })
            ->when($request->service_engineer_id_2, function ($collection) use ($request) {
                $employee = Employee::find($request->service_engineer_id_2, ['*']);
                return $employee ? $collection->push($employee->dealership_id) : $collection;
            })
            ->filter()
            ->unique();

        foreach ($dealershipIds as $dealershipId) {
            $serviceManagers = Employee::with('user')
                ->where('dealership_id', $dealershipId)
                ->whereHas('role', function ($q) {
                    $q->where('role', 'service_manager')
                        ->orWhere('role', 'Service Manager');
                })
                ->get();

            foreach ($serviceManagers as $manager) {
                if ($manager->user && $manager->user->player_id) {
                    try {
                        //generate a notification_id that is not existing in the notifications table
                        do {
                            $notificationId = (string) Str::uuid();
                        } while (Notification::where('notification_id', $notificationId)->exists());

                        $title = "New Service Assigned to Your Dealership";
                        $message = "A new service has been assigned to an engineer in your dealership. Please check the dashboard for details.";
                        $payloadData = array_merge($data, [
                            'id' => $entry->id,
                            'route' => 'NotificationView',
                            'type' => 'new_service_assignment_manager',
                            'notification_id' => $notificationId,
                        ]);

                        $response = Http::withHeaders([
                            'Authorization' => 'Basic ' . env('ONESIGNAL_REST_API_KEY'),
                            'Content-Type' => 'application/json',
                        ])->post('https://onesignal.com/api/v1/notifications', [
                            'app_id' => env('ONESIGNAL_APP_ID'),
                            'include_aliases' => [
                                'external_id' => [$manager->user->email],
                            ],
                            'data' => $payloadData,
                            'target_channel' => 'push',
                            'priority' => 10,
                            'android_visibility' => 1,
                            'headings' => ['en' => $title],
                            'contents' => ['en' => $message],
                        ]);

                        Notification::create([
                            'notification_id' => $notificationId,
                            'user_id' => $manager->user_id,
                            'title' => $title,
                            'message' => $message,
                            'data' => $payloadData,
                        ]);

                        Log::info('OneSignal notification sent successfully to service manager.', [
                            'manager_id' => $manager->id,
                            'player_id' => $manager->user->player_id,
                            'service_id' => $entry->id,
                            'dealership_id' => $dealershipId,
                            'onesignal_response' => $response,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send OneSignal notification to service manager.', [
                            'manager_id' => $manager->id,
                            'player_id' => $manager->user->player_id,
                            'service_id' => $entry->id,
                            'dealership_id' => $dealershipId,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }
            }
        }

        return response()->json(['message' => 'Service engineers assigned successfully.']);
    }

    public function getServiceFollowups(Service $service)
    {
        $service->load(['tasks.followups.user', 'tasks.assignedEmployee']);

        $followupsByTask = $service->tasks->map(function ($task) {
            return [
                'task_id' => $task->id,
                'task_title' => $task->title,
                'assigned_to' => $task->assignedEmployee ? $task->assignedEmployee->name : 'N/A',
                'followups' => $task->followups->map(function ($followup) {
                    return [
                        'id' => $followup->id,
                        'notes' => $followup->notes,
                        'created_at' => $followup->created_at->format('Y-m-d H:i:s'),
                        'submitted_by' => $followup->user ? $followup->user->name : 'N/A',
                        'images' => $followup->images,
                    ];
                })
            ];
        });

        return response()->json($followupsByTask);
    }

    public function showFollowupsPage(Service $service)
    {
        $service->load(['tasks.followups.user', 'tasks.assignedEmployee', 'client', 'product', 'productModel']);
        return view('services.followups', compact('service'));
    }

    public function getGpsDataForService(Request $request)
    {
        $userIds = $request->input('user_ids');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $gpsTraces = \App\Models\UserGpsTrace::whereIn('user_id', $userIds)
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('recorded_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('recorded_at', '<=', $endDate);
            })
            ->orderBy('recorded_at')
            ->get();

        // Fetch clock-in/clock-out times for the given users and date range
        // We need all clock records that *overlap* with the requested date range
        $clockRecords = \App\Models\Clock::whereIn('employee_id', $userIds)
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->where(function ($query) use ($startDate, $endDate) {
                    $query->where(function ($q) use ($startDate, $endDate) {
                        $q->whereDate('clock_in_time', '<=', $endDate)
                            ->whereDate('clock_out_time', '>=', $startDate);
                    })->orWhere(function ($q) use ($startDate) {
                        // Handle ongoing sessions (clock_out_time is null)
                        $q->whereNull('clock_out_time')
                            ->whereDate('clock_in_time', '<=', $startDate);
                    });
                });
            })
            ->get();

        // Filter gpsTraces to only include points within clock-in/clock-out periods
        $filteredGpsTraces = $gpsTraces->filter(function ($trace) use ($clockRecords, $endDate) {
            foreach ($clockRecords as $clock) {
                $clockIn = \Carbon\Carbon::parse($clock->clock_in_time);
                $clockOut = $clock->clock_out_time ? \Carbon\Carbon::parse($clock->clock_out_time) : null;

                // If clock_out_time is null, consider the session ongoing
                // If endDate is also null, use a very distant future date
                if ($clockOut === null) {
                    $clockOut = $endDate ? \Carbon\Carbon::parse($endDate)->endOfDay() : \Carbon\Carbon::createFromDate(9999, 12, 31);
                }

                if ($trace->recorded_at->greaterThanOrEqualTo($clockIn) && $trace->recorded_at->lessThanOrEqualTo($clockOut)) {
                    return true;
                }
            }
            return false;
        });

        $followups = \App\Models\TaskFollowup::whereIn('user_id', $userIds)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($startDate, function ($query) use ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            })
            ->with('user') // Eager load the user relationship
            ->get()
            ->map(function ($followup) {
                $followup->user_name = $followup->user ? $followup->user->name : 'N/A';
                return $followup;
            });

        return response()->json([
            'gpsTraces' => $gpsTraces,
            'followups' => $followups,
        ]);
    }

    public function getFollowupsDataTable(Service $service)
    {
        $followups = \App\Models\TaskFollowup::whereIn('task_id', $service->tasks->pluck('id'))->with('user', 'task');

        return DataTables::eloquent($followups)
            ->addColumn('task_title', function (\App\Models\TaskFollowup $followup) {
                return $followup->task->title;
            })
            ->addColumn('user_name', function (\App\Models\TaskFollowup $followup) {
                return $followup->user->name;
            })
            ->editColumn('created_at', function (\App\Models\TaskFollowup $followup) {
                return $followup->created_at->format('d M Y, h:i a');
            })
            ->addColumn('images_column', function (\App\Models\TaskFollowup $followup) {
                if (!empty($followup->images)) {
                    return '<button type="button" class="btn btn-info btn-sm view-images-btn" data-id="' . $followup->id . '" data-images="' . htmlspecialchars(json_encode($followup->images)) . '">View Images</button>';
                }
                return '';
            })
            ->rawColumns(['user_name', 'images_column'])
            ->make(true);
    }

    public function export(Request $request)
    {
        $assignmentStatus = $request->query('assignment_status', 'all');
        $format = $request->query('format', 'xlsx');
        $action = $request->query('action', 'download'); // 'download' or 'stream' (for print)

        $user = Auth::user();
        $dealershipId = $request->query('dealership_id') ?: ($user->employee->dealership_id ?? null);
        $zoneId = $request->query('zone_id');

        $extension = $format === 'csv' ? 'csv' : ($format === 'pdf' ? 'pdf' : 'xlsx');
        $fileName = 'Services_' . ($assignmentStatus === 'all' ? 'All' : ucfirst($assignmentStatus)) . '_' . now()->format('Ymd_His') . '.' . $extension;

        if ($format === 'pdf') {
            $dealership = $dealershipId ? \App\Models\Dealership::find($dealershipId) : null;

            $query = Service::with(['client', 'product', 'productModel', 'modelSeries', 'serviceEngineer.user', 'serviceEngineer2.user', 'dealership', 'zone']);

            if ($assignmentStatus === 'assigned') {
                $query->where(function ($q) {
                    $q->whereNotNull('service_engineer_id')
                        ->orWhereNotNull('service_engineer_id_2');
                });
            } elseif ($assignmentStatus === 'unassigned') {
                $query->whereNull('service_engineer_id')
                    ->whereNull('service_engineer_id_2');
            }

            if ($dealershipId) {
                $query->where('dealership_id', $dealershipId);
            }

            if ($zoneId) {
                $query->where('zone_id', $zoneId);
            }

            $services = $query->orderBy('created_at', 'desc')->get();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('services.pdf', compact('services', 'assignmentStatus', 'dealership'));

            if ($action === 'stream') {
                return $pdf->stream($fileName);
            }
            return $pdf->download($fileName);
        }
        $writerType = \Maatwebsite\Excel\Excel::XLSX;
        if ($format === 'csv') {
            $writerType = \Maatwebsite\Excel\Excel::CSV;
        }

        return Excel::download(new ServicesExport($assignmentStatus, $dealershipId, $zoneId), $fileName, $writerType);
    }
}
