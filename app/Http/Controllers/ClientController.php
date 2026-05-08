<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Lead;
use App\Models\Dealership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Session;



class ClientController extends Controller

/**
 * Convert a lead to a client.
 *
 * @OA\Post(
 *      path="/api/clients/convert/{lead_id}",
 *      operationId="convertLeadToClient",
 *      tags={"Clients"},
 *      summary="Convert a lead to a client",
 *      description="Converts a lead to a client and links related leads.",
 *      @OA\Parameter(
 *          name="lead_id",
 *          description="ID of the lead to convert",
 *          required=true,
 *          in="path",
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *          @OA\JsonContent(@OA\Property(property="message", type="string"), @OA\Property(property="client", type="object"))
 *      ),
 *      @OA\Response(response=500, description="Error converting lead to client."),
 *      security={{"bearerAuth":{}}}
 * )
 */
{
    public function convertToClient(Request $request, Lead $lead)
    {
        Log::info('Attempting to convert Lead ID: ' . $lead->id . ' to Client.');

        try {
            // Update lead with provided details if they exist
            if ($request->filled('billing')) {
                $lead->billing = $request->billing;
            }
            if ($request->filled('doc')) {
                $lead->doc = $request->doc;
            }
            if ($request->filled('engine_serial_number')) {
                $lead->engine_serial_number = $request->engine_serial_number;
            }
            $lead->save();

            // 1. Check for existing client by phone number
            $client = Client::where('phone_number', $lead->phone_number)->first();

            if (!$client) {
                // If no existing client, create a new one
                $client = new Client();
                $client->salutation = $lead->salutation;
                $client->name = $lead->name;
                $client->email = $lead->email;
                $client->phone_number = $lead->phone_number;
                $client->alternate_contact_number = $lead->alternate_contact_number;
                $client->address = $lead->address ?: $lead->location; // Mapping location to address if address is empty
                $client->dealership_id = $lead->dealership_id;
                $client->employee_id = $lead->employee_id;
                $client->agent_type = $lead->agent_type;
                $client->agent_id = $lead->agent_id;
                $client->lead_source_id = $lead->lead_source_id;
                $client->lead_category_id = $lead->lead_category_id;
                $client->notes = $lead->notes ?: $lead->remarks;
                $client->lead_id = $lead->id;
                $client->latitude = $lead->latitude;
                $client->longitude = $lead->longitude;
                $client->gps_location = $lead->latitude && $lead->longitude ? $lead->latitude . ',' . $lead->longitude : null;
                $client->save();
                Log::info('Client created successfully for Lead ID: ' . $lead->id . '. Client ID: ' . $client->id);
            }

            // 2. Link the current lead to the client and update its status
            $lead->client_id = $client->id; // Assign client_id
            $lead->last_status_before_conversion = $lead->status; // Save current status
            $lead->status = 'win'; // Set status to win
            $lead->chance_of_success = 100; // Set chance of success to 100
            $lead->save();
            Log::info('Lead ID: ' . $lead->id . ' status set to win and client_id updated to ' . $client->id . '.');

            $itemDetails = $request->input('item_details', []);

            // Convert ALL products/machines from the lead items to client products
            $lead->load('items');
            if ($lead->items->isNotEmpty()) {
                foreach ($lead->items as $item) {
                    $isPrimary = ($item->product_id == $lead->product_id &&
                        $item->product_model_id == $lead->product_model_id &&
                        $item->model_series_id == $lead->model_series_id);

                    // Get details for this specific item (array of units)
                    $units = $itemDetails[$item->id] ?? [];

                    // Update lead item with the first unit's info for reference
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

                        \App\Models\ClientProduct::create([
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
                    \App\Models\ClientProduct::create([
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

            // 3. Link other leads with the same phone number to this client
            Lead::where('phone_number', $lead->phone_number)
                ->whereNull('client_id') // Only link leads not already associated with a client
                ->where('id', '!=', $lead->id) // Exclude the current lead
                ->update(['client_id' => $client->id]);

            return response()->json(['message' => 'Lead converted to client successfully.', 'client' => $client]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error converting lead to client.', 'error' => $e->getMessage()], 500);
        }
    }

    public function revertConversion(Client $client)
    {
        Log::info('Reverting conversion for Client ID: ' . $client->id);

        try {
            DB::beginTransaction();

            // 1. Find the primary lead that triggered this client creation
            $primaryLead = Lead::find($client->lead_id);

            if ($primaryLead) {
                // Restore status and success rate
                $primaryLead->status = $primaryLead->last_status_before_conversion ?: 'win';
                $primaryLead->chance_of_success = ($primaryLead->status === 'win') ? 100 : 75;
                $primaryLead->client_id = null;
                $primaryLead->last_status_before_conversion = null;
                $primaryLead->save();
            }

            // 2. Unlink ALL other leads associated with this client
            Lead::where('client_id', $client->id)->update(['client_id' => null]);

            // 3. Delete all client products
            $client->products()->delete();

            // 4. Delete the client record
            $clientName = $client->name;
            $client->delete();

            DB::commit();

            return response()->json([
                'message' => 'Conversion for "' . $clientName . '" has been successfully reverted. The record is now back to being a Lead.',
                'redirect_url' => $primaryLead ? route('leads.profile', $primaryLead->id) : route('leads.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reverting conversion: ' . $e->getMessage());
            return response()->json(['message' => 'Error reverting conversion.', 'error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    /**
     * Get a list of clients.
     *
     * @OA\Get(
     *      path="/api/clients",
     *      operationId="getClientsList",
     *      tags={"Clients"},
     *      summary="Get list of clients",
     *      description="Returns list of clients",
     *      @OA\Parameter(
     *          name="search.value",
     *          description="Search value for filtering clients",
     *          in="query",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(type="array", @OA\Items(type="object"))
     *      ),
     *      security={{"bearerAuth":{}}}
     * )
     */
    {



        if ($request->ajax()) {
            $data = Client::with([
                'dealership',
                'agent',
                'leadSource',
                'products',
                'products.product',
                'products.productModel',
                'leads' => function ($query) {
                    $query->where('status', 'converted_to_client')
                        ->orWhere('status', 'win');
                },
                'leads.agent',
                'leads.leadSource',
                'leads.product',
                'leads.productModel'
            ])->select('clients.*')
                ->orderBy('created_at', 'desc');

            $user = \Illuminate\Support\Facades\Auth::user();
            $dealershipName = null;
            if ($user->employee && $user->employee->dealership_id) {
                $user->employee->load('dealership');
                $dealershipName = $user->employee->dealership->name;

                $data->where(function ($query) use ($user, $dealershipName) {
                    $query->where('dealership_id', $user->employee->dealership_id)
                        ->orWhereNull('dealership_id')
                        // Check if any associated product name matches dealership name in client_products
                        ->orWhereHas('products.product', function ($q) use ($dealershipName) {
                            $q->whereRaw('LOWER(name) like ?', ['%' . strtolower($dealershipName) . '%']);
                        })
                        // OR check if any associated product name matches in leads (Won/Converted)
                        ->orWhereHas('leads', function ($q) use ($dealershipName) {
                            $q->whereIn('status', ['win', 'converted_to_client'])
                                ->where(function ($sq) use ($dealershipName) {
                                    $sq->whereHas('product', function ($pq) use ($dealershipName) {
                                        $pq->whereRaw('LOWER(name) like ?', ['%' . strtolower($dealershipName) . '%']);
                                    })
                                        ->orWhereHas('items.product', function ($iq) use ($dealershipName) {
                                            $iq->whereRaw('LOWER(name) like ?', ['%' . strtolower($dealershipName) . '%']);
                                        });
                                });
                        });
                });
            }

            $filterDealershipName = null;
            if ($request->has('dealership_id') && !empty($request->dealership_id)) {
                $dealershipIdFilter = $request->dealership_id;
                $dealershipModel = \App\Models\Dealership::find($dealershipIdFilter);
                $filterDealershipName = $dealershipModel ? $dealershipModel->name : '';

                $data->where(function ($query) use ($dealershipIdFilter, $filterDealershipName) {
                    $query->where('dealership_id', $dealershipIdFilter);

                    if ($filterDealershipName) {
                        $query->orWhereHas('products.product', function ($q) use ($filterDealershipName) {
                            $q->whereRaw('LOWER(name) like ?', ['%' . strtolower($filterDealershipName) . '%']);
                        })
                            ->orWhereHas('leads', function ($q) use ($filterDealershipName) {
                                $q->whereIn('status', ['win', 'converted_to_client'])
                                    ->where(function ($sq) use ($filterDealershipName) {
                                        $sq->whereHas('product', function ($pq) use ($filterDealershipName) {
                                            $pq->whereRaw('LOWER(name) like ?', ['%' . strtolower($filterDealershipName) . '%']);
                                        })
                                            ->orWhereHas('items.product', function ($iq) use ($filterDealershipName) {
                                                $iq->whereRaw('LOWER(name) like ?', ['%' . strtolower($filterDealershipName) . '%']);
                                            });
                                    });
                            });
                    }
                });
            }

            if ($request->has('search.value') && !empty($request->input('search.value'))) {
                $searchValue = $request->input('search.value');
                $data->where(function ($query) use ($searchValue) {
                    $query->whereRaw('LOWER(name) like ?', ['%' . strtolower($searchValue) . '%'])
                        ->orWhereRaw('LOWER(email) like ?', ['%' . strtolower($searchValue) . '%'])
                        ->orWhereRaw('LOWER(phone_number) like ?', ['%' . strtolower($searchValue) . '%'])
                        ->orWhereRaw('LOWER(address) like ?', ['%' . strtolower($searchValue) . '%'])
                        ->orWhereRaw('LOWER(gps_location) like ?', ['%' . strtolower($searchValue) . '%']);
                });

                // Log the SQL query and its bindings
                Log::info('Client search SQL: ' . $data->toSql());
                Log::info('Client search Bindings: ' . json_encode($data->getBindings()));
            }

            if ($request->input('mode') === 'includeLeads') {
                $leadsQuery = \App\Models\Lead::with(['dealership', 'agent', 'leadSource', 'items.product', 'product', 'productModel'])
                    ->whereNull('client_id');

                if ($dealershipName) {
                    $leadsQuery->where(function ($query) use ($user, $dealershipName) {
                        $query->where('dealership_id', $user->employee->dealership_id)
                            ->orWhereNull('dealership_id')
                            ->orWhereHas('product', function ($q) use ($dealershipName) {
                                $q->whereRaw('LOWER(name) like ?', ['%' . strtolower($dealershipName) . '%']);
                            })
                            ->orWhereHas('items.product', function ($q) use ($dealershipName) {
                                $q->whereRaw('LOWER(name) like ?', ['%' . strtolower($dealershipName) . '%']);
                            });
                    });
                }

                if ($request->has('dealership_id') && !empty($request->dealership_id)) {
                    $dealershipIdFilter = $request->dealership_id;
                    $leadsQuery->where(function ($query) use ($dealershipIdFilter, $filterDealershipName) {
                        $query->where('dealership_id', $dealershipIdFilter);
                        if ($filterDealershipName) {
                            $query->orWhereHas('product', function ($q) use ($filterDealershipName) {
                                $q->whereRaw('LOWER(name) like ?', ['%' . strtolower($filterDealershipName) . '%']);
                            })
                                ->orWhereHas('items.product', function ($q) use ($filterDealershipName) {
                                    $q->whereRaw('LOWER(name) like ?', ['%' . strtolower($filterDealershipName) . '%']);
                                });
                        }
                    });
                }

                if ($request->has('search.value') && !empty($request->input('search.value'))) {
                    $searchValue = $request->input('search.value');
                    $leadsQuery->where(function ($query) use ($searchValue) {
                        $query->whereRaw('LOWER(name) like ?', ['%' . strtolower($searchValue) . '%'])
                            ->orWhereRaw('LOWER(email) like ?', ['%' . strtolower($searchValue) . '%'])
                            ->orWhereRaw('LOWER(phone_number) like ?', ['%' . strtolower($searchValue) . '%'])
                            ->orWhereRaw('LOWER(location) like ?', ['%' . strtolower($searchValue) . '%'])
                            ->orWhereRaw('LOWER(map_location) like ?', ['%' . strtolower($searchValue) . '%']);
                    });
                }

                $leads = $leadsQuery->get();
                $transformedLeads = $leads->map(function ($lead) {
                    // Create a dummy object or structure that mimics Client for DataTables
                    return (object) [
                        'id' => $lead->id,
                        'salutation' => $lead->salutation,
                        'name' => $lead->name,
                        'email' => $lead->email,
                        'phone_number' => $lead->phone_number,
                        'alternate_contact_number' => $lead->alternate_contact_number,
                        'address' => $lead->location,
                        'profile_pic' => null,
                        'dealership' => $lead->dealership,
                        'agent' => $lead->agent,
                        'leadSource' => $lead->leadSource,
                        'is_lead' => true,
                        // Need to provide collections for relations used in DataTables map
                        'products' => collect(),
                        'leads' => collect([$lead]), // It is its own lead
                        'created_at' => $lead->created_at,
                    ];
                });

                $clients = $data->get()->map(function ($client) {
                    $client->is_lead = false;
                    return $client;
                });
                $data = $clients->concat($transformedLeads);
            }

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('agent_name', function ($row) {
                    $agent = $row->leads->firstWhere('agent', '!=', null)->agent ?? $row->agent;
                    return $agent ? $agent->name : 'N/A';
                })
                ->addColumn('source_name', function ($row) {
                    $leadSource = $row->leads->firstWhere('leadSource', '!=', null)->leadSource ?? $row->leadSource;
                    return $leadSource ? $leadSource->name : 'N/A';
                })
                ->addColumn('name_contact', function ($row) {
                    return [
                        'salutation' => $row->salutation,
                        'name' => $row->name,
                        'email' => $row->email,
                        'phone_number' => $row->phone_number,
                        'alternate_contact_number' => $row->alternate_contact_number, // Add this
                        'address' => $row->address,
                        'profile_pic' => $row->profile_pic
                    ];
                })
                ->addColumn('agent_source', function ($row) {
                    // Prioritize agent from the first converted lead, otherwise use client's direct agent
                    $agent = $row->leads->firstWhere('agent', '!=', null)->agent ?? $row->agent;
                    $leadSource = $row->leads->firstWhere('leadSource', '!=', null)->leadSource ?? $row->leadSource;

                    return [
                        'agent' => $agent,
                        'leadSource' => $leadSource,
                    ];
                })
                ->addColumn('dealership_name', function ($row) {
                    return $row->dealership ? $row->dealership->name : 'N/A';
                })
                ->addColumn('products', function ($row) {
                    $newProducts = $row->products->pluck('product.name')->filter();
                    $statuses = ['win', 'converted_to_client'];
                    if (isset($row->is_lead) && $row->is_lead) {
                        $statuses = ['win', 'converted_to_client', 'pending', 'in progress', 'positive'];
                    }
                    $oldProducts = $row->leads->whereIn('status', $statuses)->pluck('product.name')->filter();
                    return $newProducts->concat($oldProducts)->unique()->implode(', ');
                })
                ->addColumn('product_models', function ($row) {
                    $newModels = $row->products->pluck('productModel.name')->filter();
                    $statuses = ['win', 'converted_to_client'];
                    if (isset($row->is_lead) && $row->is_lead) {
                        $statuses = ['win', 'converted_to_client', 'pending', 'in progress', 'positive'];
                    }
                    $oldModels = $row->leads->whereIn('status', $statuses)->pluck('productModel.name')->filter();
                    return $newModels->concat($oldModels)->unique()->implode(', ');
                })
                ->addColumn('action', function ($row) {
                    if (isset($row->is_lead) && $row->is_lead) {
                        $btn = '<a href="/leads/' . $row->id . '/profile" class="view btn btn-info btn-sm">View Lead</a>';
                        return $btn;
                    }
                    $btn = '<a href="/clients/' . $row->id . '" class="view btn btn-info btn-sm">View</a>';
                    $btn .= ' <a href="/clients/' . $row->id . '/edit" class="edit btn btn-primary btn-sm ms-1">Edit</a>';
                    $btn .= ' <a href="javascript:void(0)" data-id="' . $row->id . '" class="delete btn btn-danger btn-sm ms-1">Delete</a>';
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $user = \Illuminate\Support\Facades\Auth::user();
        $showDealershipColumn = true;
        $dealerships = Dealership::where('brand', 1)->orderBy('name')->get();
        // Group by name and category to avoid duplicates in the dropdown
        $products = \App\Models\Product::with('category')
            ->select('products.*')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->orderBy('products.name')
            ->get()
            ->unique(function ($item) {
                return $item->name . '-' . ($item->category->name ?? '');
            });
        $states = \App\Models\State::orderBy('name')->get();
        $leadSources = \App\Models\LeadSource::orderBy('name')->get();

        return view('clients.index', compact('dealerships', 'showDealershipColumn', 'products', 'states', 'leadSources'));
    }

    public function create()
    {
        // Group by name and category to avoid duplicates in the dropdown
        $products = \App\Models\Product::with('category')
            ->select('products.*')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->orderBy('products.name')
            ->get()
            ->unique(function ($item) {
                return $item->name . '-' . ($item->category->name ?? '');
            });
        $dealerships = Dealership::orderBy('name')->get();
        $states = \App\Models\State::orderBy('name')->get();
        $leadSources = \App\Models\LeadSource::orderBy('name')->get();

        return view('clients.create', compact('products', 'dealerships', 'states', 'leadSources'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone_number' => 'required',
            'state_id' => 'required|exists:states,id',
            'district_id' => 'required|exists:districts,id',
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'products' => 'nullable|array',
            'products.*.product_id' => 'required|exists:products,id',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->all();

            // Default to Kerala if state_id is missing or invalid
            if (!$request->filled('state_id')) {
                $kerala = \App\Models\State::where('name', 'Kerala')->first();
                $data['state_id'] = $kerala ? $kerala->id : 17; // ID 17 as fallback if DB search fails
            }

            if ($request->hasFile('profile_pic')) {
                $image = $request->file('profile_pic');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/client_images', $imageName);
                $data['profile_pic'] = 'storage/client_images/' . $imageName;
            }

            $client = Client::create($data);

            if ($request->has('products')) {
                foreach ($request->products as $productData) {
                    \App\Models\ClientProduct::create([
                        'client_id' => $client->id,
                        'product_id' => $productData['product_id'] ?? null,
                        'product_model_id' => $productData['product_model_id'] ?? null,
                        'machine_serial_number' => $productData['machine_serial_number'] ?? null,
                        'engine_serial_number' => $productData['engine_serial_number'] ?? null,
                        'engine_model' => $productData['engine_model'] ?? null,
                        'doc' => $productData['doc'] ?? null,
                    ]);
                }
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json($client, 201);
            }

            return redirect()->route('clients.index')->with('success', 'Client created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating client: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json(['message' => 'Error creating client.', 'error' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Error creating client: ' . $e->getMessage());
        }
    }

    public function show(Client $client)
    {
        if (request()->ajax()) {
            $client->load(['products.product', 'products.productModel', 'products.modelSeries', 'leads.items.product', 'leads.items.productModel', 'leads.items.modelSeries']);
            $clientArray = $client->toArray();
            if (isset($clientArray['leads'])) {
                $clientArray['leads'] = collect($clientArray['leads'])->map(function ($leadData, $index) use ($client) {
                    $lead = $client->leads[$index];
                    if (isset($leadData['items'])) {
                        $leadData['items'] = collect($leadData['items'])->map(function ($itemData, $itemIndex) use ($lead) {
                            $item = $lead->items[$itemIndex];
                            return [
                                'id' => $item->id,
                                'lead_id' => $item->lead_id,
                                'product_id' => $item->product_id,
                                'product_model_id' => $item->product_model_id,
                                'model_series_id' => $item->model_series_id,
                                'quantity' => $item->quantity,
                                'price' => $item->price ? (float) $item->price : null,
                                'product_name' => $item->product->name ?? null,
                                'product_model_name' => $item->productModel->name ?? null,
                                'model_series_name' => $item->modelSeries->name ?? null,
                            ];
                        })->toArray();
                    }
                    return $leadData;
                })->toArray();
            }
            return response()->json($clientArray);
        }

        $data = $this->getClientExportData($client);
        $services = $data['services'];
        $uniqueProducts = $data['uniqueProducts'];

        return view('clients.show', compact('client', 'services', 'uniqueProducts'));
    }

    public function exportExcel(Client $client)
    {
        $data = $this->getClientExportData($client);
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ClientDetailsExport($client, $data['services'], $data['uniqueProducts'], $data['totalInteractions']),
            'client_details_' . $client->id . '.xlsx'
        );
    }

    public function exportPdf(Client $client)
    {
        $data = $this->getClientExportData($client);
        $services = $data['services'];
        $uniqueProducts = $data['uniqueProducts'];
        $totalInteractions = $data['totalInteractions'];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('clients.pdf', compact('client', 'services', 'uniqueProducts', 'totalInteractions'));
        return $pdf->download('client_details_' . $client->id . '.pdf');
    }

    public function exportListPdf(Request $request)
    {
        $query = Client::with([
            'dealership',
            'agent',
            'leadSource',
            'leads' => function ($query) {
                $query->where('status', 'converted_to_client')
                    ->orWhere('status', 'win');
            },
            'leads.agent',
            'leads.leadSource',
            'leads.product',
            'leads.productModel'
        ])->select('clients.*');

        $user = \Illuminate\Support\Facades\Auth::user();
        if ($user->employee && $user->employee->dealership_id) {
            $user->employee->load('dealership');
            $dealershipName = $user->employee->dealership->name;

            $query->where(function ($q) use ($user, $dealershipName) {
                $q->where('dealership_id', $user->employee->dealership_id)
                    ->orWhereNull('dealership_id')
                    // Check if any associated product name matches dealership name in client_products
                    ->orWhereHas('products.product', function ($pq) use ($dealershipName) {
                        $pq->whereRaw('LOWER(name) like ?', ['%' . strtolower($dealershipName) . '%']);
                    })
                    // OR check if any associated product name matches in leads (Won/Converted)
                    ->orWhereHas('leads', function ($lq) use ($dealershipName) {
                        $lq->whereIn('status', ['win', 'converted_to_client'])
                            ->where(function ($slq) use ($dealershipName) {
                                $slq->whereHas('product', function ($pq2) use ($dealershipName) {
                                    $pq2->whereRaw('LOWER(name) like ?', ['%' . strtolower($dealershipName) . '%']);
                                })
                                    ->orWhereHas('items.product', function ($iq) use ($dealershipName) {
                                        $iq->whereRaw('LOWER(name) like ?', ['%' . strtolower($dealershipName) . '%']);
                                    });
                            });
                    });
            });
        }

        if ($request->has('dealership_id') && !empty($request->dealership_id)) {
            $dealershipIdFilter = $request->dealership_id;
            $dealershipModel = \App\Models\Dealership::find($dealershipIdFilter);
            $filterDealershipName = $dealershipModel ? $dealershipModel->name : '';

            $query->where(function ($q) use ($dealershipIdFilter, $filterDealershipName) {
                $q->where('dealership_id', $dealershipIdFilter);

                if ($filterDealershipName) {
                    $q->orWhereHas('products.product', function ($pq) use ($filterDealershipName) {
                        $pq->whereRaw('LOWER(name) like ?', ['%' . strtolower($filterDealershipName) . '%']);
                    })
                        ->orWhereHas('leads', function ($lq) use ($filterDealershipName) {
                            $lq->whereIn('status', ['win', 'converted_to_client'])
                                ->where(function ($slq) use ($filterDealershipName) {
                                    $slq->whereHas('product', function ($pq2) use ($filterDealershipName) {
                                        $pq2->whereRaw('LOWER(name) like ?', ['%' . strtolower($filterDealershipName) . '%']);
                                    })
                                        ->orWhereHas('items.product', function ($iq) use ($filterDealershipName) {
                                            $iq->whereRaw('LOWER(name) like ?', ['%' . strtolower($filterDealershipName) . '%']);
                                        });
                                });
                        });
                }
            });
        }

        if ($request->has('search') && !empty($request->search)) {
            $searchValue = $request->input('search');
            $query->where(function ($q) use ($searchValue) {
                $q->whereRaw('LOWER(name) like ?', ['%' . strtolower($searchValue) . '%'])
                    ->orWhereRaw('LOWER(email) like ?', ['%' . strtolower($searchValue) . '%'])
                    ->orWhereRaw('LOWER(phone_number) like ?', ['%' . strtolower($searchValue) . '%'])
                    ->orWhereRaw('LOWER(address) like ?', ['%' . strtolower($searchValue) . '%'])
                    ->orWhereRaw('LOWER(gps_location) like ?', ['%' . strtolower($searchValue) . '%']);
            });
        }

        $clients = $query->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('clients.pdf-list', compact('clients'));
        return $pdf->download('clients_list.pdf');
    }

    public function getClientExportData(Client $client)
    {
        $client->load([
            'state',
            'district',
            'products.product.category',
            'products.productModel',
            'products.modelSeries',
            'leads.agent',
            'leads.leadSource',
            'leads.product.category',
            'leads.productModel',
            'leads.followups.user',
            'leads.tasks.assignedEmployee',
            'leads.tasks.followups.user',
            'leads.items.product.category',
            'leads.items.productModel',
            'leads.items.modelSeries',
            'services.product.category',
            'services.productModel',
            'services.serviceEngineer',
            'services.tasks.assignedEmployee',
            'services.tasks.followups.user'
        ]);

        $services = $client->services;
        $ownedProducts = collect();

        // Products from new client_products table
        foreach ($client->products as $cp) {
            $machineSerial = $cp->machine_serial_number ?? ($cp->modelSeries->name ?? null);
            $ownedProducts->push([
                'client_product_id' => $cp->id,
                'product' => $cp->product,
                'product_id' => $cp->product_id,
                'product_name' => $cp->product->name ?? null,
                'category_name' => $cp->product->category->name ?? null,
                'model' => $cp->productModel,
                'product_model_id' => $cp->product_model_id,
                'product_model_name' => $cp->productModel->name ?? null,
                'series' => $cp->modelSeries,
                'doc' => $cp->doc,
                'machine_serial_number' => $machineSerial,
                'engine_model' => $cp->engine_model,
                'engine_serial_number' => $cp->engine_serial_number,
                'source' => 'Client Asset (Imported/Added)',
                'date' => $cp->created_at
            ]);
        }

        // Products from Won/Converted Leads (Legacy Compatibility)
        foreach ($client->leads->whereIn('status', ['win', 'converted_to_client']) as $lead) {
            foreach ($lead->items as $item) {
                $machineSerial = $item->machine_serial_number ?? ($item->modelSeries->name ?? $lead->machine_serial_number);
                $ownedProducts->push([
                    'product' => $item->product,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? null,
                    'category_name' => $item->product->category->name ?? null,
                    'model' => $item->productModel,
                    'product_model_id' => $item->product_model_id,
                    'product_model_name' => $item->productModel->name ?? null,
                    'series' => $item->modelSeries,
                    'doc' => $item->doc ?? $lead->doc,
                    'machine_serial_number' => $machineSerial,
                    'engine_model' => $item->engine_model ?? $lead->engine_model,
                    'engine_serial_number' => $item->engine_serial_number ?? $lead->engine_serial_number,
                    'source' => 'Purchase (Lead #' . $lead->id . ')',
                    'date' => $lead->updated_at
                ]);
            }
            if ($lead->items->isEmpty() && ($lead->product_id || $lead->product)) {
                $machineSerial = $lead->machine_serial_number ?? ($lead->modelSeries->name ?? null);
                $ownedProducts->push([
                    'product' => $lead->product,
                    'product_id' => $lead->product_id,
                    'product_name' => $lead->product->name ?? null,
                    'category_name' => $lead->product->category->name ?? null,
                    'model' => $lead->productModel,
                    'product_model_id' => $lead->product_model_id,
                    'product_model_name' => $lead->productModel->name ?? null,
                    'series' => $lead->modelSeries,
                    'doc' => $lead->doc,
                    'machine_serial_number' => $machineSerial,
                    'engine_model' => $lead->engine_model,
                    'engine_serial_number' => $lead->engine_serial_number,
                    'source' => 'Purchase (Lead #' . $lead->id . ')',
                    'date' => $lead->updated_at
                ]);
            }
        }

        // Products from Services
        foreach ($services as $service) {
            $machineSerial = $service->machine_serial_number ?? ($service->modelSeries->name ?? null);
            $ownedProducts->push([
                'product' => $service->product,
                'product_id' => $service->product_id,
                'product_name' => $service->product->name ?? null,
                'category_name' => $service->product->category->name ?? null,
                'model' => $service->productModel,
                'product_model_id' => $service->product_model_id,
                'product_model_name' => $service->productModel->name ?? null,
                'series' => $service->modelSeries,
                'doc' => $service->doc,
                'engine_model' => $service->engine_model,
                'engine_serial_number' => $service->engine_serial_number,
                'machine_serial_number' => $machineSerial,
                'source' => 'Service Interaction (Service #' . $service->id . ')',
                'date' => $service->created_at
            ]);
        }

        $groupedProducts = collect();
        foreach ($ownedProducts->sortByDesc('date') as $item) {
            $prodId = $item['product']->id ?? ($item['product_id'] ?? '0');
            $modelId = $item['model']->id ?? ($item['product_model_id'] ?? '0');
            $machineSerial = $item['machine_serial_number'] ?? 'no-m-serial';

            // Generate a secure identity key. 
            // Only group by engine_serial if machine_serial is missing.
            if ($machineSerial === 'no-m-serial' || empty($machineSerial)) {
                $engineSerial = $item['engine_serial_number'] ?? 'no-e-serial';
                $key = $prodId . '-' . $modelId . '-E:' . $engineSerial;
            } else {
                $key = $prodId . '-' . $modelId . '-M:' . $machineSerial;
            }

            if (!$groupedProducts->has($key)) {
                $groupedProducts->put($key, $item);
            } else {
                // Merge missing attributes (like engine_serial_number) into the existing item
                $existing = $groupedProducts->get($key);
                if (empty($existing['engine_serial_number']) && !empty($item['engine_serial_number'])) {
                    $existing['engine_serial_number'] = $item['engine_serial_number'];
                    $groupedProducts->put($key, $existing);
                }
            }
        }
        $uniqueProducts = $groupedProducts->values();

        $totalInteractions = $client->leads->sum(function ($lead) {
            return $lead->followups->count();
        }) +
            $services->sum(function ($service) {
                return $service->tasks->sum(function ($task) {
                    return $task->followups->count();
                });
            });

        return [
            'services' => $services,
            'uniqueProducts' => $uniqueProducts,
            'totalInteractions' => $totalInteractions
        ];
    }

    public function edit(Client $client)
    {
        $client->load([
            'products.product',
            'products.productModel',
            'products.modelSeries',
            'leads.items',
            'services'
        ]);

        foreach ($client->products as $cp) {
            if (empty($cp->machine_serial_number)) {
                // Try to find serial in leads
                foreach ($client->leads as $lead) {
                    // Check main lead product
                    if ($lead->product_id == $cp->product_id && $lead->product_model_id == $cp->product_model_id && !empty($lead->machine_serial_number)) {
                        $cp->machine_serial_number = $lead->machine_serial_number;
                        break;
                    }
                    // Check items in lead
                    foreach ($lead->items as $item) {
                        if ($item->product_id == $cp->product_id && $item->product_model_id == $cp->product_model_id && !empty($item->machine_serial_number)) {
                            $cp->machine_serial_number = $item->machine_serial_number;
                            break 2;
                        }
                    }
                }

                // If still null, try services
                if (empty($cp->machine_serial_number)) {
                    foreach ($client->services as $service) {
                        if ($service->product_id == $cp->product_id && $service->product_model_id == $cp->product_model_id && !empty($service->machine_serial_number)) {
                            $cp->machine_serial_number = $service->machine_serial_number;
                            break;
                        }
                    }
                }

                // Fallback to model series name if available (as seen in show page logic)
                if (empty($cp->machine_serial_number) && $cp->modelSeries) {
                    $cp->machine_serial_number = $cp->modelSeries->name;
                }
            }
        }

        return response()->json($client);
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'nullable|email',
            'phone_number' => 'nullable',
            'alternate_contact_number' => 'nullable|string',
            'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'products' => 'nullable|array',
            'products.*.product_id' => 'required|exists:products,id',
        ]);

        try {
            DB::beginTransaction();

            $data = $request->all();

            if ($request->hasFile('profile_pic')) {
                // Delete old image if exists
                if ($client->profile_pic) {
                    $oldPath = str_replace('storage/', 'public/', $client->profile_pic);
                    if (\Illuminate\Support\Facades\Storage::exists($oldPath)) {
                        \Illuminate\Support\Facades\Storage::delete($oldPath);
                    }
                }

                $image = $request->file('profile_pic');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                $image->storeAs('public/client_images', $imageName);
                $data['profile_pic'] = 'storage/client_images/' . $imageName;
            }

            $client->update($data);

            // Synchronize products
            // For machines, we might just want to delete existing ones and re-add or update based on ID
            // For now, let's delete existing manually added products and re-add from request
            $client->products()->delete();

            if ($request->has('products')) {
                foreach ($request->products as $productData) {
                    \App\Models\ClientProduct::create([
                        'client_id' => $client->id,
                        'product_id' => $productData['product_id'] ?? null,
                        'product_model_id' => $productData['product_model_id'] ?? null,
                        'machine_serial_number' => $productData['machine_serial_number'] ?? null,
                        'engine_serial_number' => $productData['engine_serial_number'] ?? null,
                        'engine_model' => $productData['engine_model'] ?? null,
                        'doc' => $productData['doc'] ?? null,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => 'Client updated successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating client: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating client.', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy(Client $client)
    /**
     * Delete a client.
     *
     * @OA\Delete(
     *      path="/api/clients/{id}",
     *      operationId="deleteClient",
     *      tags={"Clients"},
     *      summary="Delete existing client",
     *      description="Deletes a client",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID of client to delete",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(@OA\Property(property="success", type="string", example="Client deleted successfully."))
     *      ),
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      security={{"bearerAuth":{}}}
     * )
     */
    {

        // Find the associated lead and revert its status if it exists
        $lead = Lead::where('client_id', $client->id)->first();
        if ($lead) {
            $lead->status = $lead->last_status_before_conversion ?? 'pending'; // Revert to last status or default to 'pending'
            $lead->client_id = null; // Dissociate from client
            $lead->last_status_before_conversion = null; // Clear the saved status
            $lead->save();
        }

        $client->delete();

        return response()->json(['success' => 'Client deleted successfully.']);
    }

    public function destroyProduct($id)
    {
        $clientProduct = \App\Models\ClientProduct::findOrFail($id);
        $clientProduct->delete();

        return response()->json(['success' => true, 'message' => 'Owned product deleted successfully.']);
    }
}
