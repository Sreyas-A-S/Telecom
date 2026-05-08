<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use OpenApi\Annotations as OA;

class ClientController extends Controller
{
    /**
     * @OA\Get(
     *     path="/clients",
     *     operationId="getClientsList",
     *     tags={"Clients"},
     *     summary="Get list of clients",
     *     description="Returns list of clients",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="search[value]",
     *         in="query",
     *         description="Search value for filtering clients",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="dealership_id",
     *         in="query",
     *         description="Filter clients by dealership ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="mode",
     *         in="query",
     *         description="Operational mode. Use 'includeLeads' to return both clients and unlinked leads in the results.",
     *         @OA\Schema(type="string", enum={"includeLeads"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="draw", type="integer", example=1),
     *             @OA\Property(property="recordsTotal", type="integer", example=100),
     *             @OA\Property(property="recordsFiltered", type="integer", example=50),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(ref="#/components/schemas/Client")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function index(Request $request)
    {
        $data = Client::with([
            'dealership',
            'agent',
            'leadSource',
            // Only load essential relationships for the list if they are small.
            // If products/leads are not displayed in the mobile list, we should consider removing them from eager loading.
            'products.product',
            'leads' => function ($query) {
                $query->where(function ($q) {
                    $q->where('status', 'converted_to_client')
                        ->orWhere('status', 'win');
                });
            },
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
        }

        if ($request->input('mode') === 'includeLeads') {
            $leadsQuery = \App\Models\Lead::with(['dealership', 'agent', 'leadSource', 'items.product', 'product'])
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
                return [
                    'id' => $lead->id,
                    'salutation' => $lead->salutation,
                    'name' => $lead->name,
                    'email' => $lead->email,
                    'phone_number' => $lead->phone_number,
                    'alternate_contact_number' => $lead->alternate_contact_number,
                    'address' => $lead->location,
                    'gps_location' => $lead->map_location,
                    'dealership_id' => $lead->dealership_id,
                    'employee_id' => $lead->employee_id,
                    'agent_type' => $lead->agent_type,
                    'agent_id' => $lead->agent_id,
                    'lead_source_id' => $lead->lead_source_id,
                    'lead_category_id' => $lead->lead_category_id,
                    'notes' => $lead->remarks,
                    'latitude' => $lead->latitude,
                    'longitude' => $lead->longitude,
                    'created_at' => $lead->created_at,
                    'updated_at' => $lead->updated_at,
                    'dealership' => $lead->dealership,
                    'agent' => $lead->agent,
                    'lead_source' => $lead->leadSource,
                    'products' => $lead->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product' => $item->product,
                        ];
                    }),
                    'leads' => [], // Leads don't have associated leads in the same way clients do
                    'is_lead' => true,
                ];
            });

            $clients = $data->get();
            $combined = $clients->concat($transformedLeads);

            return DataTables::of($combined)
                ->addIndexColumn()
                ->make(true);
        }

        return DataTables::of($data)
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * @OA\Post(
     *     path="/clients",
     *     operationId="storeClient",
     *     tags={"Clients"},
     *     summary="Store a new client",
     *     description="Creates a new client record",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Jane Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="jane.doe@example.com", nullable=true),
     *             @OA\Property(property="phone_number", type="string", example="+1234567890", nullable=true),
     *             @OA\Property(property="location", type="string", example="New York", nullable=true),
     *             @OA\Property(property="latitude", type="number", format="float", example=40.7128, nullable=true),
     *             @OA\Property(property="longitude", type="number", format="float", example=-74.0060, nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Client created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Client")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'map_location' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $clientData = $request->all();
        if ($request->filled('map_location')) {
            $clientData['gps_location'] = $request->input('map_location');
        }

        $client = Client::create($clientData);

        return response()->json($client, 201);
    }

    /**
     * @OA\Get(
     *     path="/clients/{id}",
     *     operationId="getClientById",
     *     tags={"Clients"},
     *     summary="Get client information",
     *     description="Returns a single client",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of client to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="client",
     *                 description="Client details with nested relationships (leads, services)",
     *                 example={
     *                     "id": 1,
     *                     "salutation": "Mr.",
     *                     "name": "John Doe",
     *                     "email": "john.doe@example.com",
     *                     "phone_number": "+1234567890",
     *                     "address": "123 Main St, Anytown, USA",
     *                     "gps_location": "40.7128,-74.0060",
     *                     "dealership_id": 1,
     *                     "employee_id": 2,
     *                     "agent_type": "App\\Models\\User",
     *                     "agent_id": 3,
     *                     "lead_source_id": 1,
     *                     "lead_category_id": 2,
     *                     "notes": "Interested in solar panels.",
     *                     "latitude": 40.7128,
     *                     "longitude": -74.0060,
     *                     "state_id": 5,
     *                     "district_id": 10,
     *                     "created_at": "2024-01-01T12:00:00Z",
     *                     "updated_at": "2024-01-10T15:30:00Z",
     *                     "leads": {
     *                         {
     *                             "id": 101,
     *                             "client_id": 1,
     *                             "status": "win",
     *                             "created_at": "2024-01-05T10:00:00Z",
     *                             "updated_at": "2024-01-15T10:00:00Z",
     *                             "items": {
     *                                 {
     *                                     "id": 1,
     *                                     "lead_id": 101,
     *                                     "product_id": 1,
     *                                     "product_model_id": 5,
     *                                     "quantity": 2,
     *                                     "price": 500.00,
     *                                     "product": {
     *                                         "id": 1,
     *                                         "name": "Solar Panel X"
     *                                     },
     *                                     "product_model": {
     *                                         "id": 5,
     *                                         "name": "Model-2024"
     *                                     }
     *                                 }
     *                             },
     *                             "followups": {
     *                                 {
     *                                     "id": 501,
     *                                     "lead_id": 101,
     *                                     "notes": "Called client, very interested.",
     *                                     "followup_date": "2024-01-06T11:00:00Z",
     *                                     "created_at": "2024-01-06T11:05:00Z"
     *                                 }
     *                             },
     *                             "tasks": {
     *                                 {
     *                                     "id": 801,
     *                                     "lead_id": 101,
     *                                     "title": "Site Survey",
     *                                     "description": "Evaluate location for solar panel placement.",
     *                                     "status": "completed",
     *                                     "followups": {
     *                                         {
     *                                             "id": 901,
     *                                             "task_id": 801,
     *                                             "remarks": "Roof is in good condition. Shading is minimal.",
     *                                             "created_at": "2024-01-07T14:30:00Z"
     *                                         }
     *                                     }
     *                                 }
     *                             }
     *                         }
     *                     },
     *                     "services": {
     *                         {
     *                             "id": 201,
     *                             "client_id": 1,
     *                             "product_id": 1,
     *                             "status": "pending",
     *                             "created_at": "2024-02-01T09:00:00Z",
     *                             "tasks": {
     *                                 {
     *                                     "id": 301,
     *                                     "service_id": 201,
     *                                     "title": "Installation",
     *                                     "status": "assigned",
     *                                     "followups": {}
     *                                 }
     *                             }
     *                         }
     *                     }
     *                 }
     *             ),
     *             @OA\Property(
     *                 property="products",
     *                 type="array",
     *                 description="List of products purchased by the client (derived from won leads and their items)",
     *                 example={
     *                     {
     *                         "product_id": 1,
     *                         "product_name": "Solar Panel X",
     *                         "product_model_id": 5,
     *                         "product_model_name": "Model-2024",
     *                         "quantity": 2,
     *                         "lead_id": 101,
     *                         "purchase_date": "2024-01-15T10:00:00Z"
     *                     }
     *                 },
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="product_name", type="string", example="Solar Panel X"),
     *                     @OA\Property(property="product_model_id", type="integer", example=5),
     *                     @OA\Property(property="product_model_name", type="string", example="Model-2024"),
     *                     @OA\Property(property="quantity", type="integer", example=2),
     *                     @OA\Property(property="lead_id", type="integer", example=101),
     *                     @OA\Property(property="purchase_date", type="string", format="date-time", example="2024-01-15T10:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client not found"
     *     )
     * )
     */
    public function show(Client $client)
    {
        // Load relationships as requested:
        // - Leads (with followups.user, tasks -> followups.user)
        // - Services (with (tasks -> followups.user))
        // - Products (derived from leads)

        $client->load([
            'leads.followups.user',
            'leads.tasks.followups.user',
            'leads.product',
            'leads.productModel',
            'leads.items.product',
            'leads.items.productModel',
            'leads.items.modelSeries',
            'services.tasks.followups.user',
            'services.product',
            'services.productModel',
        ]);

        // Transform the client data for API response
        $clientArray = $client->toArray();

        // Transform Leads
        if (isset($clientArray['leads'])) {
            $clientArray['leads'] = collect($clientArray['leads'])->map(function ($leadData, $index) use ($client) {
                // Add user_name to lead followups
                if (isset($leadData['followups'])) {
                    $leadData['followups'] = collect($leadData['followups'])->map(function ($followup) {
                        $followup['user_name'] = $followup['user']['name'] ?? null;
                        return $followup;
                    })->toArray();
                }

                // Add user_name to lead task followups
                if (isset($leadData['tasks'])) {
                    $leadData['tasks'] = collect($leadData['tasks'])->map(function ($task) {
                        if (isset($task['followups'])) {
                            $task['followups'] = collect($task['followups'])->map(function ($followup) {
                                $followup['user_name'] = $followup['user']['name'] ?? null;
                                return $followup;
                            })->toArray();
                        }
                        return $task;
                    })->toArray();
                }

                // Transform Items (existing logic)
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

        // Transform Services
        if (isset($clientArray['services'])) {
            $clientArray['services'] = collect($clientArray['services'])->map(function ($serviceData) {
                if (isset($serviceData['tasks'])) {
                    $serviceData['tasks'] = collect($serviceData['tasks'])->map(function ($task) {
                        if (isset($task['followups'])) {
                            $task['followups'] = collect($task['followups'])->map(function ($followup) {
                                $followup['user_name'] = $followup['user']['name'] ?? null;
                                return $followup;
                            })->toArray();
                        }
                        return $task;
                    })->toArray();
                }
                return $serviceData;
            })->toArray();
        }

        // Derive products from won/converted leads
        $products = collect();

        $client->leads->whereIn('status', ['win', 'converted_to_client'])
            ->each(function ($lead) use ($products) {
                // If lead has items, add them
                if ($lead->items && $lead->items->count() > 0) {
                    foreach ($lead->items as $item) {
                        $products->push([
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name ?? null,
                            'product_model_id' => $item->product_model_id,
                            'product_model_name' => $item->productModel->name ?? null,
                            'model_series_id' => $item->model_series_id,
                            'model_series_name' => $item->modelSeries->name ?? null,
                            'quantity' => $item->quantity,
                            'lead_id' => $lead->id,
                            'purchase_date' => $lead->updated_at,
                        ]);
                    }
                }
                // Fallback to lead's main product if no items (and product_id exists)
                elseif ($lead->product_id) {
                    $products->push([
                        'product_id' => $lead->product_id,
                        'product_name' => $lead->product->name ?? null,
                        'product_model_id' => $lead->product_model_id,
                        'product_model_name' => $lead->productModel->name ?? null,
                        'model_series_id' => $lead->model_series_id,
                        'model_series_name' => $lead->modelSeries->name ?? null,
                        'quantity' => $lead->quantity ?? 1,
                        'lead_id' => $lead->id,
                        'purchase_date' => $lead->updated_at,
                    ]);
                }
            });

        return response()->json([
            'client' => $clientArray,
            'products' => $products->values()
        ]);
    }

    /**
     * @OA\Put(
     *     path="/clients/{id}",
     *     operationId="updateClient",
     *     tags={"Clients"},
     *     summary="Update an existing client",
     *     description="Updates a client record by ID",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of client to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="Jane Doe Updated"),
     *             @OA\Property(property="email", type="string", format="email", example="jane.doe.updated@example.com", nullable=true),
     *             @OA\Property(property="phone_number", type="string", example="+1987654321", nullable=true),
     *             @OA\Property(property="location", type="string", example="Los Angeles", nullable=true),
     *             @OA\Property(property="latitude", type="number", format="float", example=34.0522, nullable=true),
     *             @OA\Property(property="longitude", type="number", format="float", example=-118.2437, nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="string", example="Client updated successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client not found"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name' => 'required',
            'map_location' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $clientData = $request->all();
        if ($request->filled('map_location')) {
            $clientData['gps_location'] = $request->input('map_location');
        }

        $client->update($clientData);

        return response()->json(['success' => 'Client updated successfully.']);
    }

    /**
     * @OA\Delete(
     *     path="/clients/{id}",
     *     operationId="deleteClient",
     *     tags={"Clients"},
     *     summary="Delete a client",
     *     description="Deletes a client record by ID",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of client to delete",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="string", example="Client deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Client not found"
     *     )
     * )
     */
    public function destroy(Client $client)
    {
        $client->delete();

        return response()->json(['success' => 'Client deleted successfully.']);
    }
}
