<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
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
use App\Models\District;
use App\Models\Client;
use App\Models\LossOrder;
use App\Models\ClientProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LeadsExport;
use App\Models\ModelSeries;
use DateTime;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Services\TaskService;

class LeadController extends Controller
{
    /**
     * @OA\Get(
     *      path="/leads",
     *      summary="Get a list of leads",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="employee_assignment_status",
     *          in="query",
     *          description="Filter by employee assignment status (assigned/unassigned)",
     *          @OA\Schema(type="string", enum={"assigned", "unassigned"})
     *      ),
     *      @OA\Parameter(
     *          name="followup_filter",
     *          in="query",
     *          description="Filter by followup status (today)",
     *          @OA\Schema(type="string", enum={"today"})
     *      ),
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="Filter by lead status",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="has_followup",
     *          in="query",
     *          description="Filter by whether lead has followups (yes/no)",
     *          @OA\Schema(type="string", enum={"yes", "no"})
     *      ),
     *      @OA\Parameter(
     *          name="lead_category_id",
     *          in="query",
     *          description="Filter by lead category ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="lead_source_id",
     *          in="query",
     *          description="Filter by lead source ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="dealership_id",
     *          in="query",
     *          description="Filter by dealership ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="from_date",
     *          in="query",
     *          description="Filter by follow up date from (YYYY-MM-DD)",
     *          @OA\Schema(type="string", format="date")
     *      ),
     *      @OA\Parameter(
     *          name="to_date",
     *          in="query",
     *          description="Filter by follow up date to (YYYY-MM-DD)",
     *          @OA\Schema(type="string", format="date")
     *      ),
     *      @OA\Parameter(
     *          name="search_value",
     *          in="query",
     *          description="Global search keyword (searches name, email, phone_number, location, agent, lead source, product, lead category, dealership, and status)",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          in="query",
     *          description="Page number for pagination",
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Number of items per page",
     *          @OA\Schema(type="integer", example=10)
     *      ),
     *      @OA\Parameter(
     *          name="length",
     *          in="query",
     *          description="(DataTables compatibility) Number of items per page",
     *          @OA\Schema(type="integer", example=10)
     *      ),
     *      @OA\Parameter(
     *          name="start",
     *          in="query",
     *          description="(DataTables compatibility) Offset for pagination",
     *          @OA\Schema(type="integer", example=0)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Leads retrieved successfully."),
     *              @OA\Property(property="data", type="array", @OA\Items(
     *                  allOf={
     *                      @OA\Schema(ref="#/components/schemas/Lead"),
     *                      @OA\Schema(
     *                          @OA\Property(
     *                              property="items",
     *                              type="array",
     *                              @OA\Items(
     *                                  type="object",
     *                                  @OA\Property(property="product_id", type="integer", example=1),
     *                                  @OA\Property(property="product_name", type="string", example="Solar Panel"),
     *                                  @OA\Property(property="quantity", type="integer", example=5)
     *                              )
     *                          )
     *                      )
     *                  }
     *              ))
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      )
     * )
     */
    public function index(Request $request)
    {
        // if (!checkMenu(auth('api')->payload()->get('role_id'), 5, 'read')) {
        //     return $this->sendError('Unauthorized', [], 499);
        // }

        $data = Lead::with([
            'client',
            'employee',
            'leadSource',
            'leadCategory',
            'dealership',
            'product',
            'productModel',
            'modelSeries',
            'items.product',
            'items.productModel',
            'items.modelSeries',
            'followups' => function ($query) {
                $query->latest('next_follow_up_date')->take(1);
            }
        ])->select('leads.*')->orderBy('created_at', 'desc');

        $user = Auth::user();
        if ($user && $user->user_type === 'employee') {
            $user->load('employee');
            if ($user->employee && $user->employee->dealership_id !== null) {
                $dealershipId = $user->employee->dealership_id;
                $data->where(function($q) use ($dealershipId) {
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
                $query->whereRaw('followups.next_follow_up_date = (SELECT MAX(next_follow_up_date) FROM followups WHERE lead_id = leads.id)');

                if ($request->filled('from_date')) {
                    $query->whereDate('followups.next_follow_up_date', '>=', $request->input('from_date'));
                }
                if ($request->filled('to_date')) {
                    $query->whereDate('followups.next_follow_up_date', '<=', $request->input('to_date'));
                }
            });
        }

        // Apply global search filter (moved from DataTables filter)
        if ($request->has('search_value') && !empty($request->input('search_value'))) {
            $keyword = $request->input('search_value');

            $data->where(function ($q) use ($keyword) {
                // Search 'name' column (salutation, name, email, phone_number)
                $q->orWhere('salutation', 'like', "%{$keyword}%")
                    ->orWhere('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone_number', 'like', "%{$keyword}%")
                    ->orWhere('location', 'like', "%{$keyword}%");

                // Search 'agent_data' (polymorphic agent and leadSource)
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

                // Search 'product' (product name and lead_value)
                $q->orWhereHas('product', function ($q2) use ($keyword) {
                    $q2->where('name', 'like', "%{$keyword}%");
                })->orWhere('lead_value', 'like', "%{$keyword}%");

                // Search 'leadCategory.name' and 'dealership.name'
                $q->orWhereHas('leadCategory', function ($q2) use ($keyword) {
                    $q2->where('name', 'like', "%{$keyword}%");
                })->orWhereHas('dealership', function ($q2) use ($keyword) {
                    $q2->where('name', 'like', "%{$keyword}%");
                });

                // Search 'status'
                $q->orWhere('status', 'like', "%{$keyword}%");
            });
        }

        // Determine items per page
        $perPage = $request->input('per_page', $request->input('length', 10));

        // Determine current page
        // Prioritize 'page' parameter, then calculate from 'start' if 'page' is not present
        $page = $request->input('page', ($request->input('start', 0) / $perPage) + 1);

        $leads = $data->paginate($perPage, ['*'], 'page', $page);

        $transformedLeads = $leads->getCollection()->map(function ($lead) {
            $lead->makeHidden([
                'client',
                'employee',
                'leadSource',
                'leadCategory',
                'dealership',
                'product',
                'productModel',
                'modelSeries',
                'agent',
                'followups'
            ]);
            $leadArray = $lead->toArray();
            $newLeadArray = [];
            foreach ($leadArray as $key => $value) {
                // For these fields, keep null if null
                if (in_array($key, ['product_model_id', 'model_series_id', 'lead_value', 'user_id'])) {
                    $newLeadArray[$key] = $value;
                } else {
                    $newLeadArray[$key] = $value === null ? "" : $value;
                }
                // Insert corresponding name after the ID
                if ($key === 'employee_id') {
                    $newLeadArray['employee_name'] = $lead->employee ? $lead->employee->name : "";
                } elseif ($key === 'lead_source_id') {
                    $newLeadArray['lead_source_name'] = $lead->leadSource ? $lead->leadSource->name : "";
                } elseif ($key === 'lead_category_id') {
                    $newLeadArray['lead_category_name'] = $lead->leadCategory ? $lead->leadCategory->name : "";
                } elseif ($key === 'dealership_id') {
                    $newLeadArray['dealership_name'] = $lead->dealership ? $lead->dealership->name : "";
                } elseif ($key === 'product_id') {
                    $newLeadArray['product_name'] = $lead->product ? $lead->product->name : "";
                } elseif ($key === 'product_model_id') {
                    $newLeadArray['product_model_name'] = $lead->productModel ? $lead->productModel->name : "";
                } elseif ($key === 'model_series_id') {
                    $newLeadArray['model_series_name'] = $lead->modelSeries ? $lead->modelSeries->name : "";
                } elseif ($key === 'client_id') {
                    $newLeadArray['client_name'] = $lead->client ? $lead->client->name : "";
                } elseif ($key === 'agent_id') {
                    $newLeadArray['agent_name'] = $lead->agent ? $lead->agent->name : "";
                } elseif ($key === 'chance_of_success') {
                    $newLeadArray['chance_of_success'] = $lead->chance_of_success === null ? null : $lead->chance_of_success;
                } elseif ($key === 'quantity') {
                    $newLeadArray['quantity'] = $lead->quantity === null ? null : $lead->quantity;
                } elseif ($key === 'lead_value') {
                    $newLeadArray['lead_value'] = $lead->lead_value === null ? null : (int) $lead->lead_value;
                } elseif ($key === 'billing') {
                    $newLeadArray['billing'] = $lead->billing === null ? null : (string) (new DateTime($lead->billing))->format('Y-m-d');
                } elseif ($key === 'map_location') {
                    $newLeadArray['map_location'] = $lead->map_location === null ? null : $lead->map_location;
                } elseif ($key === 'latitude') {
                    $newLeadArray['latitude'] = $lead->latitude === null ? null : $lead->latitude;
                } elseif ($key === 'longitude') {
                    $newLeadArray['longitude'] = $lead->longitude === null ? null : $lead->longitude;
                }
            }
            $newLeadArray['latest_follow_up_date'] = $lead->followups->isNotEmpty()
                ? ($lead->followups->first()->next_follow_up_date ?? "")
                : "";
            $integerIdFields = [
                'employee_id',
                'lead_source_id',
                'lead_category_id',
                'dealership_id',
                'product_id',
                'product_model_id',
                'model_series_id',
                'client_id',
                'agent_id',
            ];
            foreach ($integerIdFields as $idField) {
                if (isset($newLeadArray[$idField]) && $newLeadArray[$idField] === "") {
                    $newLeadArray[$idField] = null;
                }
            }
            // Ensure product_model_id, model_series_id, lead_value, user_id are null if null, not empty string
            foreach (['product_model_id', 'model_series_id', 'lead_value', 'user_id'] as $field) {
                if (array_key_exists($field, $newLeadArray) && $newLeadArray[$field] === "") {
                    $newLeadArray[$field] = null;
                }
            }

            // Transform items if present (loaded via eager loading)
            if ($lead->relationLoaded('items')) {
                $items = $lead->items;

                // Filter out items without serial number if lead is won and there are items with serial numbers for the same model
                if ($lead->status === 'win') {
                    $modelIdsWithSerials = $items->whereNotNull('model_series_id')->pluck('product_model_id')->unique()->toArray();
                    $items = $items->filter(function ($item) use ($modelIdsWithSerials) {
                        if ($item->model_series_id === null && in_array($item->product_model_id, $modelIdsWithSerials)) {
                            return false;
                        }
                        return true;
                    });
                }

                $transformedItems = $items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_model_id' => $item->product_model_id,
                        'model_series_id' => $item->model_series_id,
                        'quantity' => $item->quantity,
                        'price' => $item->price ? (float) $item->price : null,
                        'product_name' => $item->product ? $item->product->name : null,
                        'product_model_name' => $item->productModel ? $item->productModel->name : null,
                        'model_series_name' => $item->modelSeries ? $item->modelSeries->name : null,
                    ];
                });
                $newLeadArray['items'] = $transformedItems->values();
            }

            return $newLeadArray;
        });

        $response = [
            'data' => $transformedLeads,
            'current_page' => $leads->currentPage(),
            'total' => $leads->total(),
            'per_page' => $leads->perPage(),
            'last_page' => $leads->lastPage(),
        ];
        return $this->sendResponse($response, 'Leads retrieved successfully.');
    }



    /**
     * @OA\Get(
     *      path="/leads/{id}",
     *      summary="Get a single lead by ID",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the lead to retrieve",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Lead retrieved successfully."),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="map_location", type="string", example="New York"),
     *                  @OA\Property(property="latitude", type="number", format="float", example=40.7128),
     *                  @OA\Property(property="longitude", type="number", format="float", example=-74.0060),
     *                  @OA\Property(
     *                      property="items",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="product_id", type="integer", example=1),
     *                          @OA\Property(property="product_name", type="string", example="Product Name"),
     *                          @OA\Property(property="quantity", type="integer", example=2),
     *                          @OA\Property(property="product_model_name", type="string", example="Model X", nullable=true),
     *                          @OA\Property(property="model_series_name", type="string", example="Series Y", nullable=true)
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Lead not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=404),
     *              @OA\Property(property="message", type="string", example="Lead not found")
     *          )
     *      )
     * )
     */
    public function show(Lead $lead)
    {
        // if (!checkMenu(auth('api')->payload()->get('role_id'), 5, 'read')) {
        //     return $this->sendError('Unauthorized', [], 499);
        // }

        $lead->load(['agent', 'leadSource', 'leadCategory', 'product', 'productModel', 'modelSeries', 'dealership', 'client', 'employee', 'items.product', 'items.productModel', 'items.modelSeries']);

        $leadArray = $lead->toArray();
        $leadArray['map_location'] = $lead->map_location;
        $leadArray['latitude'] = $lead->latitude;
        $leadArray['longitude'] = $lead->longitude;

        // Transform items to match specifically requested format
        if ($lead->relationLoaded('items')) {
            $items = $lead->items;

            // Filter out items without serial number if lead is won and there are items with serial numbers for the same model
            if ($lead->status === 'win') {
                $modelIdsWithSerials = $items->whereNotNull('model_series_id')->pluck('product_model_id')->unique()->toArray();
                $items = $items->filter(function ($item) use ($modelIdsWithSerials) {
                    if ($item->model_series_id === null && in_array($item->product_model_id, $modelIdsWithSerials)) {
                        return false;
                    }
                    return true;
                });
            }

            $leadArray['items'] = $items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_model_id' => $item->product_model_id,
                    'model_series_id' => $item->model_series_id,
                    'quantity' => $item->quantity,
                    'price' => $item->price ? (float) $item->price : null,
                    'product_name' => $item->product ? $item->product->name : null,
                    'product_model_name' => $item->productModel ? $item->productModel->name : null,
                    'model_series_name' => $item->modelSeries ? $item->modelSeries->name : null,
                ];
            })->values();
        }

        return $this->sendResponse($leadArray, 'Lead retrieved successfully.');
    }
    /**
     * @OA\Post(
     *      path="/leads",
     *      summary="Create a new lead",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="salutation", type="string", nullable=true, example="Mr."),
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *              @OA\Property(property="company", type="string", nullable=true, example="Acme Corp"),
     *              @OA\Property(property="email", type="string", format="email", nullable=true, example="john.doe@example.com"),
     *              @OA\Property(property="phone_number", type="string", nullable=true, example="1234567890"),
     *              @OA\Property(property="alternate_contact_number", type="string", nullable=true, example="0987654321"),
     *              @OA\Property(property="agent_id", type="integer", example=1),
     *              @OA\Property(property="agent_type", type="string", example="App\\Models\\Employee", enum={"App\\Models\\Employee", "App\\Models\\Agent"}),
     *              @OA\Property(property="lead_source", type="string", nullable=true, example="Website"),
     *              @OA\Property(property="lead_category", type="string", nullable=true, example="Hot"),
     *              @OA\Property(property="lead_value", type="number", format="float", nullable=true, example=1000.00),
     *              @OA\Property(property="allow_follow_up", type="boolean", example=true),
     *              @OA\Property(property="status", type="string", example="pending"),
     *              @OA\Property(property="chance_of_success", type="integer", nullable=true, example=50),
     *              @OA\Property(property="product_id", type="integer", example=1),
     *              @OA\Property(property="product_model_id", type="integer", nullable=true, example=1),
     *              @OA\Property(property="model_series_id", type="integer", nullable=true, example=1),
     *              @OA\Property(property="quantity", type="integer", nullable=true, example=1),
     *              @OA\Property(property="dealership_id", type="integer", nullable=true, example=1),
     *              @OA\Property(property="location", type="string", nullable=true, example="New York"),
     *              @OA\Property(property="financier", type="string", nullable=true, example="Bank A"),
     *              @OA\Property(property="type", type="string", nullable=true, example="FTB"),
     *              @OA\Property(property="login_status", type="string", nullable=true, example="Logged In"),
     *              @OA\Property(property="stage", type="string", nullable=true, example="opportunity"),
     *              @OA\Property(property="remarks", type="string", nullable=true, example="Initial contact made."),
     *              @OA\Property(property="billing", type="string", format="date", nullable=true, example="2025-12-31"),
     *              @OA\Property(property="map_location", type="string", nullable=true, example="New York"),
     *              @OA\Property(property="latitude", type="string", nullable=true, example="40.7128"),
     *              @OA\Property(property="longitude", type="string", nullable=true, example="-74.0060"),
     *              @OA\Property(
     *                  property="items",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="product_id", type="integer", example=1),
     *                      @OA\Property(property="product_model_id", type="integer", nullable=true, example=1),
     *                      @OA\Property(property="model_series_id", type="integer", nullable=true, example=1),
     *                      @OA\Property(property="quantity", type="integer", nullable=true, example=1),
     *                      @OA\Property(property="price", type="number", format="float", nullable=true, example=100.00)
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Lead created successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Lead created successfully."),
     *              @OA\Property(property="data", type="object") 
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=422),
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="data", type="object") 
     *          )
     *      )
     * )
     */
    public function store(Request $request, TaskService $taskService)
    {
        // if (!checkMenu(auth('api')->payload()->get('role_id'), 5, 'create')) {
        //     return $this->sendError('Unauthorized', [], 499);
        // }

        $request->validate([
            'salutation' => 'nullable|string',
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'alternate_contact_number' => 'nullable|string|max:20',
            'agent_id' => 'nullable|integer',
            'agent_type' => 'nullable|string|in:App\\Models\\Agent,App\\Models\\Employee',
            'lead_source' => 'nullable|string',
            'lead_category' => 'nullable|string',
            'lead_value' => 'nullable|numeric',
            'allow_follow_up' => 'boolean',
            'status' => 'required|string',
            'chance_of_success' => 'nullable|integer|min:0|max:100',
            'product_id' => 'nullable|integer|exists:products,id',
            'product_model_id' => 'nullable|integer|exists:product_models,id',
            'model_series_id' => 'nullable|integer|exists:model_series,id',
            'quantity' => 'nullable|integer|min:1',
            'dealership_id' => 'nullable|integer',
            'location' => 'nullable|string',
            'financier' => 'nullable|string',
            'type' => 'nullable|string',
            'login_status' => 'nullable|string',
            'stage' => 'nullable|string',
            'remarks' => 'nullable|string',
            'billing' => 'nullable|string',
            'map_location' => 'nullable|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'employee_id' => 'nullable|exists:employees,id',
        ]);


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

        if ($finalAgentId !== null) {
            return $this->sendError('Invalid agent selected.', [], 422);
        }

        $lead = new Lead();
        $lead->salutation = $request->salutation;
        $lead->name = $request->name;
        $lead->company = $request->company;
        $lead->email = $request->email;
        $lead->phone_number = $request->phone_number;
        $lead->alternate_contact_number = $request->alternate_contact_number;
        $lead->agent_id = $finalAgentId;
        $lead->agent_type = Agent::class;
        $lead->lead_value = $request->lead_value;
        $lead->allow_follow_up = $request->allow_follow_up;
        $lead->status = $request->status;
        $lead->chance_of_success = $request->chance_of_success;
        $lead->location = $request->location;
        $lead->quantity = $request->quantity;
        $lead->financier = $request->financier;
        $lead->type = $request->type;
        $lead->login_status = $request->login_status;
        $lead->stage = $request->stage;
        $lead->remarks = $request->remarks;
        $lead->billing = $request->billing;
        $lead->map_location = $request->map_location;
        $lead->latitude = $request->latitude;
        $lead->longitude = $request->longitude;

        $user = Auth::user();
        if ($user && $user->user_type === 'employee') {
            $user->load('employee.role');
            if ($user->employee && $user->employee->dealership_id !== null) {
                // Employee with a specific dealership_id: use their dealership_id
                $lead->dealership_id = $user->employee->dealership_id;
            } else {
                // Employee with null dealership_id: allow selection from request
                $lead->dealership_id = $request->input('dealership_id');
            }
        } else {
            // Admin or other user type: allow selection from request
            $lead->dealership_id = $request->input('dealership_id');
        }

        // Assign user_id of the authenticated user
        if ($user) {
            $lead->user_id = $user->id;
        }

        // Handle Lead Source
        if ($request->lead_source) {
            $leadSource = LeadSource::firstOrCreate(['name' => $request->lead_source]);

            $lead->lead_source_id = $leadSource->id;
        }

        // Handle Lead Category
        if ($request->lead_category) {
            $leadCategory = LeadCategory::firstOrCreate(['name' => $request->lead_category]);
            $lead->lead_category_id = $leadCategory->id;
        }

        $lead->product_id = $request->product_id;
        $lead->product_model_id = $request->product_model_id;
        $lead->model_series_id = $request->model_series_id;

        if ($user && $user->user_type === 'employee' && $user->employee) {
            $lead->employee_id = $user->employee->id;
        } elseif ($request->filled('employee_id')) {
            $lead->employee_id = $request->input('employee_id');
        }

        $lead->save();

        // Handle multiple products
        $items = $request->input('items', []);
        if (empty($items) && $request->product_id) {
            $items[] = [
                'product_id' => $request->product_id,
                'product_model_id' => $request->product_model_id,
                'model_series_id' => $request->model_series_id,
                'quantity' => $request->quantity ?? 1,
            ];
        }
        $lead->syncItems($items);

        if ($lead->employee_id) {
            $taskService->createTasksForLead($request, $lead);
        }

        return $this->sendResponse($lead, 'Lead created successfully.');
    }

    /**
     * @OA\Put(
     *      path="/leads/{id}",
     *      summary="Update a lead",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the lead to update",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="salutation", type="string", nullable=true, example="Mr."),
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *              @OA\Property(property="company", type="string", nullable=true, example="Acme Corp"),
     *              @OA\Property(property="email", type="string", format="email", nullable=true, example="john.doe@example.com"),
     *              @OA\Property(property="phone_number", type="string", nullable=true, example="1234567890"),
     *              @OA\Property(property="alternate_contact_number", type="string", nullable=true, example="0987654321"),
     *              @OA\Property(property="agent_id", type="integer", example=1),
     *              @OA\Property(property="agent_type", type="string", example="App\\Models\\Employee", enum={"App\\Models\\Employee", "App\\Models\\Agent"}),
     *              @OA\Property(property="lead_source", type="string", nullable=true, example="Website"),
     *              @OA\Property(property="lead_category", type="string", nullable=true, example="Hot"),
     *              @OA\Property(property="lead_value", type="number", format="float", nullable=true, example=1000.00),
     *              @OA\Property(property="allow_follow_up", type="boolean", example=true),
     *              @OA\Property(property="status", type="string", example="pending"),
     *              @OA\Property(property="product_id", type="integer", example=1),
     *              @OA\Property(property="product_model_id", type="integer", nullable=true, example=1),
     *              @OA\Property(property="model_series_id", type="integer", nullable=true, example=1),
     *              @OA\Property(property="dealership_id", type="integer", nullable=true, example=1),
     *              @OA\Property(property="location", type="string", nullable=true, example="New York"),
     *              @OA\Property(property="financier", type="string", nullable=true, example="Bank A"),
     *              @OA\Property(property="type", type="string", nullable=true, example="FTB"),
     *              @OA\Property(property="login_status", type="string", nullable=true, example="Logged In"),
     *              @OA\Property(property="stage", type="string", nullable=true, example="opportunity"),
     *              @OA\Property(property="remarks", type="string", nullable=true, example="Initial contact made."),
     *              @OA\Property(property="billing", type="string", format="date", nullable=true, example="2025-12-31"),
     *              @OA\Property(property="map_location", type="string", nullable=true, example="New York"),
     *              @OA\Property(property="latitude", type="string", nullable=true, example="40.7128"),
     *              @OA\Property(property="longitude", type="string", nullable=true, example="-74.0060"),
     *              @OA\Property(
     *                  property="items",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(property="product_id", type="integer", example=1),
     *                      @OA\Property(property="product_model_id", type="integer", nullable=true, example=1),
     *                      @OA\Property(property="model_series_id", type="integer", nullable=true, example=1),
     *                      @OA\Property(property="quantity", type="integer", nullable=true, example=1),
     *                      @OA\Property(property="price", type="number", format="float", nullable=true, example=100.00)
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Lead updated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Lead updated successfully."),
     *              @OA\Property(property="data", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Lead not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=404),
     *              @OA\Property(property="message", type="string", example="Lead not found")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=422),
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="data", type="object")
     *          )
     *      )
     * )
     */


    public function update(Request $request, Lead $lead = null)


    {


        if (is_null($lead)) {


            $lead = Lead::find($request->input('id'));


            if (!$lead) {


                return $this->sendError('Lead not found.', [], 404);
            }
        }





        $request->validate([
            'salutation' => 'nullable|string',
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'alternate_contact_number' => 'nullable|string|max:20',
            'agent_id' => 'required|integer',
            'agent_type' => 'required|string|in:App\\Models\\Employee,App\\Models\\Agent',
            'lead_source' => 'nullable|string',
            'lead_category' => 'nullable|string',
            'lead_value' => 'nullable|numeric',
            'allow_follow_up' => 'boolean',


            'status' => 'required|string',
            'product_id' => 'nullable|integer|exists:products,id',
            'product_model_id' => 'nullable|integer|exists:product_models,id',
            'model_series_id' => 'nullable|integer|exists:model_series,id', // Added model_series
            'dealership_id' => 'nullable|integer',
            'location' => 'nullable|string',


            'financier' => 'nullable|string',
            'type' => 'nullable|string',
            'login_status' => 'nullable|string',
            'stage' => 'nullable|string',
            'remarks' => 'nullable|string',
            'billing' => 'nullable|string',
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
        $lead->agent_id = $request->agent_id;
        $lead->agent_type = $request->agent_type;
        $lead->lead_value = $request->lead_value;
        $lead->allow_follow_up = $request->allow_follow_up;
        $lead->status = $request->status;
        $lead->location = $request->location;
        $lead->financier = $request->financier;
        $lead->type = $request->type;
        $lead->login_status = $request->login_status;
        $lead->stage = $request->stage;
        $lead->remarks = $request->remarks;
        $lead->billing = $request->billing;
        $lead->map_location = $request->map_location;
        $lead->latitude = $request->latitude;
        $lead->longitude = $request->longitude;
        // Determine dealership_id for the lead (similar to store method)
        $user = Auth::user();
        if ($user && $user->user_type === 'employee') {
            $user->load('employee');
            if ($user->employee && $user->employee->dealership_id !== null) {
                // Employee with a specific dealership_id: use their dealership_id
                $lead->dealership_id = $user->employee->dealership_id;
            } else {
                // Employee with null dealership_id: allow selection from request
                $lead->dealership_id = $request->input('dealership_id');
            }
        } else {
            // Admin or other user type: allow selection from request
            $lead->dealership_id = $request->input('dealership_id');
        }

        // Handle Lead Source
        if ($request->lead_source) {
            $leadSource = LeadSource::firstOrCreate(['name' => $request->lead_source]);
            $lead->lead_source_id = $leadSource->id;
        }

        // Handle Lead Category
        if ($request->lead_category) {
            $leadCategory = LeadCategory::firstOrCreate(['name' => $request->lead_category]);
            $lead->lead_category_id = $leadCategory->id;
        }

        if ($request->has('product_id')) {
            $lead->product_id = $request->product_id;
        }

        if ($request->has('product_model_id')) {
            $lead->product_model_id = $request->product_model_id;
        }

        if ($request->has('model_series_id')) {
            $lead->model_series_id = $request->model_series_id;
        }

        $lead->save();

        // Handle multiple products
        $items = $request->input('items', []);
        if (empty($items) && $request->product_id) {
            $items[] = [
                'product_id' => $request->product_id,
                'product_model_id' => $request->product_model_id,
                'model_series_id' => $request->model_series_id,
                'quantity' => $request->quantity ?? 1,
            ];
        }
        if (!empty($items)) {
            $lead->syncItems($items);
        }

        return $this->sendResponse($lead, 'Lead updated successfully.');
    }

    /**
     * @OA\Delete(
     *      path="/leads/{id}",
     *      summary="Delete a lead by ID",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the lead to delete",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Lead deleted successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Lead deleted successfully."),
     *              @OA\Property(property="data", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Lead not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=404),
     *              @OA\Property(property="message", type="string", example="Lead not found")
     *          )
     *      )
     * )
     */
    public function destroy(Lead $lead)
    {
        // if (! (Http::post(route('api.permissions.check-menu'), [
        //     'role_id' => auth('api')->payload()->get('role_id'),
        //     'id' => 5,
        //     'action' => 'delete'
        // ])->json()['data'])) {
        //     return $this->sendError('Unauthorized', [], 499);
        // }
        $lead->delete();

        return $this->sendResponse([], 'Lead deleted successfully.');
    }

    /**
     * @OA\Get(
     *      path="/leads/{id}/profile",
     *      summary="Get lead profile by ID",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the lead to retrieve profile for",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Lead profile retrieved successfully."),
     *              @OA\Property(property="data", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Lead not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=404),
     *              @OA\Property(property="message", type="string", example="Lead not found")
     *          )
     *      )
     * )
     */
    public function profile(Lead $lead)
    {
        // if (! (Http::post(route('api.permissions.check-menu'), [
        //     'role_id' => auth('api')->payload()->get('role_id'),
        //     'id' => 5,
        //     'action' => 'read'
        // ])->json()['data'])) {
        //     return $this->sendError('Unauthorized', [], 499);
        // }
        $lead->load('agent', 'leadSource', 'leadCategory', 'product', 'productModel', 'modelSeries', 'dealership', 'items.product', 'items.productModel', 'items.modelSeries');
        return $this->sendResponse($lead, 'Lead profile retrieved successfully.');
    }

    /**
     * @OA\Get(
     *      path="/leads/export-excel",
     *      summary="Export leads to Excel",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="followup_filter",
     *          in="query",
     *          description="Filter by followup status (e.g., today)",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="search.value",
     *          in="query",
     *          description="Global search keyword (searches name, email, phone_number, location, agent, lead source, product, lead category, dealership, and status)",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Leads export URL generated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Leads export URL generated successfully."),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="download_url", type="string", format="url", example="http://localhost/storage/leads_1678886400.xlsx")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="You do not have permission to export leads.")
     *          )
     *      )
     * )
     */
    public function exportExcel(Request $request)
    {
        // if (! (Http::post(route('api.permissions.check-menu'), [
        //     'role_id' => auth('api')->payload()->get('role_id'),
        //     'id' => 5,
        //     'action' => 'read'
        // ])->json()['data'])) {
        //     return $this->sendError('You do not have permission to export leads.', [], 499);
        // }

        $filters = [
            'followup_filter' => $request->query('followup_filter'),
            'search_value' => $request->query('search.value'),
        ];

        $user = Auth::user();
        if ($user && $user->user_type !== 'admin') {
            $user->load('employee');
            if ($user->employee && $user->employee->dealership_id !== null) {
                $filters['user_dealership_id'] = $user->employee->dealership_id;
            }
        }

        // Generate the Excel file and store it temporarily
        $fileName = 'leads_' . time() . '.xlsx';
        Excel::store(new LeadsExport($filters), $fileName, 'public');

        // Return the URL to the stored file
        return $this->sendResponse(['download_url' => asset('storage/' . $fileName)], 'Leads export URL generated successfully.');
    }

    /**
     * @OA\Put(
     *      path="/leads/{id}/status",
     *      summary="Update lead status",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the lead to update status for",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="win"),
     *              @OA\Property(property="billing", type="string", format="date", nullable=true, example="2025-12-31"),
     *              @OA\Property(property="item_details", type="object", description="Per-unit item details")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Lead status updated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Lead status updated successfully."),
     *              @OA\Property(property="data", type="object") 
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Lead not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=404),
     *              @OA\Property(property="message", type="string", example="Lead not found")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=422),
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="data", type="object") 
     *          )
     *      )
     * )
     */
    public function updateStatus(Request $request, Lead $lead)
    {
        // if (! (Http::post(route('api.permissions.check-menu'), [
        //     'role_id' => auth('api')->payload()->get('role_id'),
        //     'id' => 5,
        //     'action' => 'update'
        // ])->json()['data'])) {
        //     return $this->sendError('Unauthorized', [], 499);
        // }
        $request->validate([
            'status' => 'required|string',
            'billing' => 'nullable|string',
            'item_details' => 'nullable|array',
        ]);

        $lead->status = $request->status;
        if ($request->filled('billing')) {
            $lead->billing = $request->billing;
        }

        // Sync chance_of_success based on status
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

        if ($lead->status === 'win') {
            $this->createClientFromLead($lead, $request->input('item_details', []));
        } elseif ($lead->status === 'lost') {
            $this->createLossOrderFromLead($lead);
        }

        return $this->sendResponse($lead, 'Lead status updated successfully.');
    }

    /**
     * @OA\Put(
     *      path="/leads/{id}/update-chance-of-success",
     *      summary="Update lead chance of success",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the lead to update chance of success for",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="chance_of_success", type="integer", example=75, minimum=0, maximum=100),
     *              @OA\Property(property="billing", type="string", format="date", nullable=true, example="2025-12-31"),
     *              @OA\Property(property="item_details", type="object", description="Per-unit item details")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success rate updated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Success rate updated successfully!"),
     *              @OA\Property(property="data", type="object") 
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Lead not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=404),
     *              @OA\Property(property="message", type="string", example="Lead not found")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=422),
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="data", type="object") 
     *          )
     *      )
     * )
     */
    public function updateChanceOfSuccess(Request $request, Lead $lead)
    {
        // if (! (Http::post(route('api.permissions.check-menu'), [
        //     'role_id' => auth('api')->payload()->get('role_id'), // Corrected from role_role_id
        //     'id' => 5,
        //     'action' => 'update'
        // ])->json()['data'])) {
        //     return $this->sendError('Unauthorized', [], 499);
        // }
        $request->validate([
            'chance_of_success' => 'required|integer|min:0|max:100',
            'billing' => 'nullable|string',
            'item_details' => 'nullable|array',
        ]);

        $lead->chance_of_success = $request->chance_of_success;
        if ($request->filled('billing')) {
            $lead->billing = $request->billing;
        }

        // Sync status based on chance_of_success
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
        } elseif ($lead->chance_of_success > 0 && $lead->chance_of_success < 100) {
            // If it was Win or Lost, move to In Progress.
            // Also if allows generic update, maybe set to in progress if currently pending/new?
            // For now, only forcing out of Win/Lost to avoid overwriting specific other statuses like 'positive'
            if (in_array($lead->status, ['win', 'lost'])) {
                $lead->status = 'in progress';
            }
        }

        $lead->save();

        if ($lead->chance_of_success == 100) {
            $this->createClientFromLead($lead, $request->input('item_details', []));
        } elseif ($lead->chance_of_success == 0) {
            $this->createLossOrderFromLead($lead);
        }

        return $this->sendResponse($lead, 'Success rate updated successfully!');
    }

    /**
     * @OA\Put(
     *      path="/leads/{id}/update-billing",
     *      summary="Update lead billing and item details",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the lead",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="billing", type="string", format="date", example="2024-12-31"),
     *              @OA\Property(property="doc", type="string", format="date", example="2024-03-09"),
     *              @OA\Property(property="engine_serial_number", type="string", example="E123456"),
     *              @OA\Property(
     *                  property="item_details",
     *                  type="object",
     *                  description="Per-item unit details. Key is LeadItem ID, value is array of unit objects. Each unit can also include item_id.",
     *                  example={
     *                      "123": {
     *                          {
     *                              "item_id": 123,
     *                              "machine_serial": "MS123",
     *                              "engine_serial": "ES123",
     *                              "engine_model": "EM123"
     *                          }
     *                      }
     *                  }
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Lead updated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Lead updated successfully!"),
     *              @OA\Property(property="data", type="object") 
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error"
     *      )
     * )
     */
    public function updateBilling(Request $request, Lead $lead)
    {
        $request->validate([
            'billing' => 'nullable|string',
            'doc' => 'nullable|string',
            'engine_serial_number' => 'nullable|string',
            'item_details' => 'nullable|array',
        ]);

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

        $itemDetails = $this->normalizeItemDetails($request->input('item_details', []));

        // Process item_details to update LeadItem records
        foreach ($itemDetails as $itemId => $units) {
            if (!is_array($units)) continue;
            if (empty($units)) continue;

            $item = $lead->items()->find($itemId);
            if (!$item) continue;

            // Update the first unit info on the LeadItem itself for reference
            if (!empty($units[0]) && is_array($units[0])) {
                $unit = $units[0];
                $updateData = [
                    'machine_serial_number' => $unit['machine_serial'] ?? $item->machine_serial_number,
                    'engine_serial_number' => $unit['engine_serial'] ?? $item->engine_serial_number,
                    'engine_model' => $unit['engine_model'] ?? $item->engine_model,
                ];

                // Also try to find/create ModelSeries if machine_serial is provided
                if (!empty($unit['machine_serial'])) {
                    $modelSeries = ModelSeries::firstOrCreate([
                        'product_model_id' => $item->product_model_id,
                        'name' => $unit['machine_serial']
                    ]);
                    $updateData['model_series_id'] = $modelSeries->id;
                }

                $item->update($updateData);
            }
        }

        if ($lead->status == 'win' || $lead->chance_of_success == 100) {
            $this->createClientFromLead($lead, $itemDetails);
        }

        return $this->sendResponse($lead->load('items.modelSeries'), 'Lead updated successfully!');
    }

    private function normalizeItemDetails($itemDetails)
    {
        if (!is_array($itemDetails)) {
            return [];
        }

        $normalized = [];

        foreach ($itemDetails as $key => $value) {
            if (!is_array($value)) {
                continue;
            }

            $isUnitObject = isset($value['item_id']) || isset($value['machine_serial']) || isset($value['engine_serial']) || isset($value['engine_model']);

            if ($isUnitObject) {
                $itemId = $value['item_id'] ?? (is_numeric($key) ? (int) $key : $key);
                if ($itemId !== null && $itemId !== '') {
                    $normalized[$itemId][] = $value;
                }
                continue;
            }

            foreach ($value as $unit) {
                if (!is_array($unit)) {
                    continue;
                }
                $itemId = $unit['item_id'] ?? (is_numeric($key) ? (int) $key : $key);
                if ($itemId === null || $itemId === '') {
                    continue;
                }
                $normalized[$itemId][] = $unit;
            }
        }

        return $normalized;
    }

    /**
     * @OA\Put(
     *      path="/leads/{id}/loss-reason",
     *      summary="Update the loss reason for a lead's loss order",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the lead to update loss reason for",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="reasons_for_loss", type="string", example="Price too high"),
     *              @OA\Property(property="remarks", type="string", nullable=true, example="Customer went with a cheaper alternative.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Loss order reason updated successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Loss order reason updated successfully."),
     *              @OA\Property(property="data", type="object") 
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Loss Order not found for this lead",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=404),
     *              @OA\Property(property="message", type="string", example="Loss Order not found for this lead.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=422),
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="data", type="object") 
     *          )
     *      )
     * )
     */
    public function updateLossReason(Request $request, Lead $lead)
    {
        $request->validate([
            'reasons_for_loss' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $lossOrder = LossOrder::where('lead_id', $lead->id)->latest()->first();

        if (!$lossOrder) {
            if ($lead->status === 'lost') {
                $this->createLossOrderFromLead($lead);
                $lossOrder = LossOrder::where('lead_id', $lead->id)->latest()->first();
            } else {
                return $this->sendError('Loss Order not found for this lead.', [], 404);
            }
        }

        $lossOrder->update([
            'reasons_for_loss' => $request->reasons_for_loss,
            'remarks' => $request->remarks ?? $lossOrder->remarks,
        ]);

        return $this->sendResponse($lossOrder, 'Loss order reason updated successfully.');
    }

    private function createClientFromLead(Lead $lead, $itemDetails = [])
    {
        $itemDetails = $this->normalizeItemDetails($itemDetails);

        // Check if client already exists for this lead or by phone number to avoid duplicates
        if ($lead->client_id) {
            $client = Client::find($lead->client_id);
        } else {
            $client = Client::where('phone_number', $lead->phone_number)->first();
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
        }

        // Update current lead with client ID
        $lead->client_id = $client->id;
        $lead->save();

        // Link all other leads for this customer to the client
        Lead::where('phone_number', $lead->phone_number)
            ->whereNull('client_id')
            ->update(['client_id' => $client->id]);

        // Convert ALL products/machines from the lead items to client products
        $lead->load('items');
        if ($lead->items->isNotEmpty()) {
            foreach ($lead->items as $item) {
                // Determine if this item matches the lead's primary to inherit serial number/doc info
                $isPrimary = ($item->product_id == $lead->product_id && 
                             $item->product_model_id == $lead->product_model_id && 
                             $item->model_series_id == $lead->model_series_id);

                // Get details for this specific item (array of units)
                $units = $itemDetails[$item->id] ?? [];

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
                        'engine_serial_number' => $unitData['engine_serial'] ?? (($isPrimary && $i === 0) ? ($lead->engine_serial_number ?: null) : null),
                        'engine_model' => $unitData['engine_model'] ?? (($isPrimary && $i === 0) ? ($lead->engine_model ?: null) : null),
                        'billing' => $unitData['billing'] ?? ($isPrimary ? ($lead->billing ?? null) : null),
                        'doc' => $unitData['doc'] ?? ($isPrimary ? ($lead->doc ?: null) : null),
                        'dealership_id' => $lead->dealership_id,
                    ]);
                }
            }
        } elseif ($lead->product_id) {
            // Fallback to primary lead product if no specific items exist
            $quantity = $lead->quantity ?? 1;
            
            // For fallback, we still check item_details if passed by some key or just first entry
            $firstItemId = is_array($itemDetails) ? array_key_first($itemDetails) : null;
            $fallbackUnits = $firstItemId ? $itemDetails[$firstItemId] : [];

            for ($i = 0; $i < $quantity; $i++) {
                $unitData = $fallbackUnits[$i] ?? [];
                
                \App\Models\ClientProduct::create([
                    'client_id' => $client->id,
                    'product_id' => $lead->product_id,
                    'product_model_id' => $lead->product_model_id,
                    'model_series_id' => $lead->model_series_id,
                    'machine_serial_number' => $unitData['machine_serial'] ?? (($i === 0) ? $lead->machine_serial_number : null),
                    'engine_serial_number' => $unitData['engine_serial'] ?? (($i === 0) ? $lead->engine_serial_number : null),
                    'engine_model' => $unitData['engine_model'] ?? (($i === 0) ? $lead->engine_model : null),
                    'billing' => $unitData['billing'] ?? ($lead->billing ?? null),
                    'doc' => $unitData['doc'] ?? ($lead->doc ?: null),
                    'dealership_id' => $lead->dealership_id,
                ]);
            }
        }
    }

    private function createLossOrderFromLead(Lead $lead)
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
            'remarks' => $lead->remarks,
            'engineer_name' => $lead->employee->name ?? null,
        ]);
    }

    /**
     * @OA\Post(
     *      path="/lead-sources",
     *      summary="Create a new lead source",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="New Lead Source")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Lead source created successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Lead source created successfully."),
     *              @OA\Property(property="data", type="object") 
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=422),
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="data", type="object") 
     *          )
     *      )
     * )
     */
    public function storeLeadSource(Request $request)
    {
        // if (! (Http::post(route('api.permissions.check-menu'), [
        //     'role_id' => auth('api')->payload()->get('role_id'),
        //     'id' => 5,
        //     'action' => 'create'
        // ])->json()['data'])) {
        //     return $this->sendError('Unauthorized', [], 499);
        // }
        $request->validate(['name' => 'required|unique:lead_sources,name']);
        $leadSource = LeadSource::create(['name' => $request->name]);
        return $this->sendResponse($leadSource, 'Lead source created successfully.');
    }

    public function storeLeadCategory(Request $request)
    {
        // if (! (Http::post(route('api.permissions.check-menu'), [
        //     'role_id' => auth('api')->payload()->get('role_id'),
        //     'id' => 5,
        //     'action' => 'create'
        // ])->json()['data'])) {
        //     return $this->sendError('Unauthorized', [], 499);
        // }
        $request->validate(['name' => 'required|unique:lead_categories,name']);
        $leadCategory = LeadCategory::create(['name' => $request->name]);
        return $this->sendResponse($leadCategory, 'Lead category created successfully.');
    }

    /**
     * @OA\Put(
     *      path="/leads/{id}/assign-employee",
     *      summary="Assign an employee to a lead",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the lead to assign employee to",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="employee_id", type="integer", example=1)
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Employee assigned successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Employee assigned successfully."),
     *              @OA\Property(property="data", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Lead or Employee not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=404),
     *              @OA\Property(property="message", type="string", example="Lead or Employee not found")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=422),
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="data", type="object") 
     *          )
     *      )
     * )
     */
    public function assignEmployee(Request $request, Lead $lead, TaskService $taskService)
    {
        // if (! (Http::post(route('api.permissions.check-menu'), [
        //     'role_id' => auth('api')->payload()->get('role_id'),
        //     'id' => 5,
        //     'action' => 'update'
        // ])->json()['data'])) {
        //     return $this->sendError('Unauthorized', [], 499);
        // }

        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $employee = Employee::find($request->employee_id);

        $lead->employee_id = $employee->id;
        $lead->save();

        if ($lead->employee_id) {
            $taskService->createTasksForLead($request, $lead);
        }

        return $this->sendResponse($lead, 'Employee assigned successfully.');
    }


    /**
     * @OA\Get(
     *      path="/lead-sources",
     *      summary="Get a list of lead sources",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Response(
     *          response=200,
     *          description="Lead sources retrieved successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Lead sources retrieved successfully."),
     *              @OA\Property(property="data", type="array", @OA\Items(type="object")) 
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      )
     * )
     */
    public function indexLeadSources()
    {
        // if (! (Http::post(route('api.permissions.check-menu'), [
        //     'role_id' => auth('api')->payload()->get('role_id'),
        //     'id' => 5,
        //     'action' => 'read'
        // ])->json()['data'])) {
        //     return $this->sendError('Unauthorized', [], 499);
        // }
        $leadSources = LeadSource::all();
        return $this->sendResponse($leadSources, 'Lead sources retrieved successfully.');
    }

    public function indexLeadCategories()
    {
        // if (!Http::post(route('api.permissions.check-menu'), [
        //     'role_id' => auth('api')->payload()->get('role_id'),
        //     'id' => 5,
        //     'action' => 'read'
        // ])->json()['data']) {
        //     return $this->sendError('Unauthorized', [], 499);
        // }
        $leadCategories = LeadCategory::all();
        return $this->sendResponse($leadCategories, 'Lead categories retrieved successfully.');
    }

    /**
     * @OA\Get(
     *      path="/leads/{id}/followups",
     *      summary="Get follow-ups for a specific lead",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the lead to retrieve follow-ups for",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Follow-ups retrieved successfully."),
     *              @OA\Property(property="data", type="object") 
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Lead not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=404),
     *              @OA\Property(property="message", type="string", example="Lead not found")
     *          )
     *      )
     * )
     */
    public function getFollowups(Request $request, Lead $lead)
    {
        // if (!Http::post(route('api.permissions.check-menu'), [
        //     'role_id' => auth('api')->payload()->get('role_id'),
        //     'id' => 5,
        //     'action' => 'read'
        // ])->json()['data']) {
        //     return $this->sendError('Unauthorized', [], 499);
        // }
        $data = $lead->followups()->with('user')->orderBy('created_at', 'desc');

        // Manual pagination
        $perPage = $request->input('length', 10); // DataTables 'length' parameter for items per page
        $page = $request->input('start', 0) / $perPage + 1; // DataTables 'start' parameter for offset

        $followups = $data->paginate($perPage, ['*'], 'page', $page);

        $followups->getCollection()->transform(function ($followup) {
            $followup->user_name = $followup->user ? $followup->user->name : null;
            return $followup;
        });

        return $this->sendResponse($followups, 'Follow-ups retrieved successfully.');
    }

    /**
     * @OA\Get(
     *      path="/leads/{id}/fsr-reports",
     *      summary="Get FSR reports for a specific lead",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the lead",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="FSR reports retrieved successfully."),
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/FSRReport"))
     *          )
     *      )
     * )
     */
    public function getFsrReports(Lead $lead)
    {
        $fsrReports = \App\Models\FSRReport::whereHas('task', function ($query) use ($lead) {
            $query->where('lead_id', $lead->id);
        })->with(['task.entry.client', 'task.lead', 'submittedBy'])->get();

        return $this->sendResponse($fsrReports, 'FSR reports retrieved successfully.');
    }

    /**
     * @OA\Post(
     *      path="/leads/{id}/followups",
     *      summary="Store a new follow-up for a lead",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID of the lead to store follow-up for",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="next_follow_up_date", type="string", format="date", nullable=true, example="2025-12-31"),
     *              @OA\Property(property="next_follow_up_time", type="string", format="time", nullable=true, example="14:30"),
     *              @OA\Property(property="new_status", type="string", example="in progress"),
     *              @OA\Property(property="remarks", type="string", nullable=true, example="Called the client, left a message.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Follow up added successfully and lead status updated",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Follow up added successfully and lead status updated."),
     *              @OA\Property(property="data", type="object") 
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Lead not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=404),
     *              @OA\Property(property="message", type="string", example="Lead not found")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=422),
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="data", type="object") 
     *          )
     *      )
     * )
     */
    public function storeFollowup(Request $request, Lead $lead)
    {

        $request->validate([
            'next_follow_up_date' => 'nullable|date',
            'next_follow_up_time' => 'nullable|date_format:H:i',
            'new_status' => 'required|string',
            'remarks' => 'nullable|string',
        ]);

        $nextFollowUpDateTime = null;
        if ($request->next_follow_up_date && $request->next_follow_up_time) {
            $nextFollowUpDateTime = $request->next_follow_up_date . ' ' . $request->next_follow_up_time;
        } else if ($request->next_follow_up_date) {
            $nextFollowUpDateTime = $request->next_follow_up_date;
        }

        $lead->followups()->create([
            'next_follow_up_date' => $nextFollowUpDateTime,
            'new_status' => $request->new_status,
            'remarks' => $request->remarks,
        ]);

        $lead->status = $request->new_status;
        $lead->save();

        //return the followup along with lead
        $followup = $lead->followups()->latest()->first();

        return $this->sendResponse($followup, 'Follow up added successfully and lead status updated.');
    }

    /**
     * @OA\Get(
     *      path="/leads/{lead_id}/followups/{followup_id}/edit",
     *      summary="Get a specific follow-up for editing",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="lead_id",
     *          in="path",
     *          required=true,
     *          description="ID of the lead",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="followup_id",
     *          in="path",
     *          required=true,
     *          description="ID of the follow-up to retrieve",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Follow-up retrieved successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Follow up retrieved successfully."),
     *              @OA\Property(property="data", type="object") 
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Lead or Follow-up not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=404),
     *              @OA\Property(property="message", type="string", example="Lead or Follow-up not found")
     *          )
     *      )
     * )
     */
    public function editFollowup(Lead $lead, Followup $followup)
    {

        return $this->sendResponse($followup, 'Follow up retrieved successfully.');
    }

    /**
     * @OA\Put(
     *      path="/leads/{lead_id}/followups/{followup_id}",
     *      summary="Update a specific follow-up for a lead",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="lead_id",
     *          in="path",
     *          required=true,
     *          description="ID of the lead",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="followup_id",
     *          in="path",
     *          required=true,
     *          description="ID of the follow-up to update",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="next_follow_up_date", type="string", format="date", nullable=true, example="2025-12-31"),
     *              @OA\Property(property="next_follow_up_time", type="string", format="time", nullable=true, example="14:30"),
     *              @OA\Property(property="new_status", type="string", example="in progress"),
     *              @OA\Property(property="remarks", type="string", nullable=true, example="Updated remarks.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Follow up updated successfully and lead status updated",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Follow up updated successfully and lead status updated."),
     *              @OA\Property(property="data", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Lead or Follow-up not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=404),
     *              @OA\Property(property="message", type="string", example="Lead or Follow-up not found")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=422),
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="data", type="object") 
     *          )
     *      )
     * )
     */
    public function updateFollowup(Request $request, Lead $lead, Followup $followup)
    {

        $request->validate([
            'next_follow_up_date' => 'nullable|date',
            'next_follow_up_time' => 'nullable|date_format:H:i',
            'new_status' => 'required|string',
            'remarks' => 'nullable|string',
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

        return $this->sendResponse($followup, 'Follow up updated successfully and lead status updated.');
    }

    /**
     * @OA\Delete(
     *      path="/leads/{lead_id}/followups/{followup_id}",
     *      summary="Delete a specific follow-up for a lead",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="lead_id",
     *          in="path",
     *          required=true,
     *          description="ID of the lead",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="followup_id",
     *          in="path",
     *          required=true,
     *          description="ID of the follow-up to delete",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Follow up deleted successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Follow up deleted successfully."),
     *              @OA\Property(property="data", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Lead or Follow-up not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=404),
     *              @OA\Property(property="message", type="string", example="Lead or Follow-up not found")
     *          )
     *      )
     * )
     */
    public function deleteFollowup(Lead $lead, Followup $followup)
    {

        $followup->delete();
        return $this->sendResponse([], 'Follow up deleted successfully.');
    }

    /**
     * @OA\Post(
     *      path="/leads/{lead}/convert",
     *      summary="Convert a lead to a client",
     *      tags={"Leads"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="lead",
     *          in="path",
     *          required=true,
     *          description="ID of the lead to convert",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=false,
     *          @OA\JsonContent(
     *              @OA\Property(property="billing", type="string", format="date", example="2024-12-31", description="Billing plan/date"),
     *              @OA\Property(property="doc", type="string", format="date", example="2024-03-09", description="Date of Commissioning"),
     *              @OA\Property(property="engine_serial_number", type="string", example="E123456", description="Global engine serial number (fallback)"),
     *              @OA\Property(
     *                  property="item_details",
     *                  type="object",
     *                  description="Per-item unit details. Key is LeadItem ID, value is array of unit objects.",
     *                  example={
     *                      "123": {
     *                          {
     *                              "machine_serial": "MS123",
     *                              "engine_serial": "ES123",
     *                              "engine_model": "EM123"
     *                          }
     *                      }
     *                  }
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Lead converted to client successfully.",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Lead converted to client successfully."),
     *              @OA\Property(property="data", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Lead not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=404),
     *              @OA\Property(property="message", type="string", example="Lead not found")
     *          )
     *      )
     * )
     */
    public function convertToClient(Request $request, Lead $lead)
    {
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
                $client->notes = $lead->notes;
                $client->save();
            }

            // 2. Link the current lead to the client and update its status
            $lead->client_id = $client->id; // Assign client_id
            $lead->last_status_before_conversion = $lead->status; // Save current status
            $lead->status = 'win'; // Set status to win
            $lead->chance_of_success = 100; // Set chance of success to 100
            $lead->save();

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
                ->whereNull('client_id')
                ->where('id', '!=', $lead->id)
                ->update(['client_id' => $client->id]);

            return $this->sendResponse($client, 'Lead converted to client successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Error converting lead to client.', $e->getMessage(), 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/clients/search-by-phone",
     *      summary="Search clients by phone number and dealership ID",
     *      tags={"Clients"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(
     *          name="dealership_id",
     *          in="query",
     *          required=false,
     *          description="ID of the dealership to filter clients by (optional)",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Parameter(
     *          name="phone_number",
     *          in="query",
     *          required=false,
     *          description="Phone number to search for (partial match)",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="mode",
     *          in="query",
     *          required=false,
     *          description="Set to includeLeads to also search in leads table",
     *          @OA\Schema(type="string", enum={"includeLeads"})
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Clients retrieved successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Clients retrieved successfully."),
     *              @OA\Property(property="data", type="array", @OA\Items(
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="salutation", type="string", example="Mr."),
     *                  @OA\Property(property="name", type="string", example="John Doe"),
     *                  @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *                  @OA\Property(property="phone_number", type="string", example="1234567890")
     *              ))
     *          )
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthorized",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=499),
     *              @OA\Property(property="message", type="string", example="Unauthorized")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="status_code", type="integer", example=422),
     *              @OA\Property(property="message", type="string", example="The given data was invalid."),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      )
     * )
     */
    public function searchClientsByPhoneApi(Request $request)
    {
        Log::info('searchClientsByPhoneApi method hit.');
        $request->validate([
            'dealership_id' => 'nullable|exists:dealerships,id',
            'phone_number' => 'nullable|string',
            'mode' => 'nullable|string',
        ]);

        $dealershipId = $request->input('dealership_id');
        $phoneNumber = $request->input('phone_number');
        $mode = $request->input('mode');

        $clients = Client::query();

        // if ($dealershipId) {
        //     $clients->where('dealership_id', $dealershipId);
        // }

        if ($phoneNumber) {
            $clients->where('phone_number', 'like', '%' . $phoneNumber . '%');
        }

        $clients = $clients->select('id', 'salutation', 'name', 'email', 'phone_number')
            ->get()
            ->map(function ($client) {
                $client->is_lead = false;
                return $client;
            });

        if ($mode === 'includeLeads') {
            $leads = Lead::query()
                ->whereNull('client_id')
                ->when($phoneNumber, function ($q) use ($phoneNumber) {
                    $q->where('phone_number', 'like', '%' . $phoneNumber . '%');
                })
                ->select('id', 'salutation', 'name', 'email', 'phone_number')
                ->get()
                ->map(function ($lead) {
                    $lead->is_lead = true;
                    return $lead;
                });

            $clients = $clients->concat($leads);
        }

        return $this->sendResponse($clients, 'Clients retrieved successfully.');
    }
}
