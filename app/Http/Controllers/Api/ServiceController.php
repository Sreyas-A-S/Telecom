<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;
use App\Models\Client;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\Employee;
use App\Models\Notification; // Import the Notification model
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\TaskService;

/**
 * @OA\Tag(
 *     name="Services",
 *     description="API Endpoints of Services"
 * )
 */
class ServiceController extends Controller
{
    /**
     * @OA\Get(
     *      path="/services",
     *      operationId="getServicesList",
     *      tags={"Services"},
     *      summary="Get list of services",
     *      description="Returns list of services",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(type="array",  @OA\Items(
     *              @OA\Property(property="id", type="integer", format="int64", example=1),
     *              @OA\Property(property="name", type="string", example="Service Name"),
     *              @OA\Property(property="description", type="string", example="Nature of Complaint/Service"),
     *              @OA\Property(property="customer_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="customer_name", type="string", example="Customer Name"),
     *              @OA\Property(property="machine_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="machine_name", type="string", example="Machine Name"),
     *              @OA\Property(property="machine_model_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="machine_model_name", type="string", example="Machine Model Name"),
     *              @OA\Property(property="machine_series_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="machine_series_name", type="string", example="Machine Series Name"),
     *              @OA\Property(property="zone_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="zone_name", type="string", example="North Zone"),
     *              @OA\Property(property="dealership_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="dealership_name", type="string", example="Dealership Name"),
     *              @OA\Property(property="service_engineer_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="service_engineer_name", type="string", example="Service Engineer Name"),
     *              @OA\Property(property="service_engineer_id_2", type="integer", format="int64", example=2),
     *              @OA\Property(property="service_engineer_2_name", type="string", example="Service Engineer 2 Name"),
     *              @OA\Property(property="requested_location", type="string", example="Service Location"),
     *              @OA\Property(property="location", type="string", example="Service Location"),
     *              @OA\Property(property="latitude", type="number", format="float", example=12.345678),
     *              @OA\Property(property="longitude", type="number", format="float", example=98.765432),
     *              @OA\Property(property="referral_id", type="string", example="SVHE2110250001"),
     *              @OA\Property(property="status", type="string", example="open"),
     *              @OA\Property(property="machine_status", type="string", example="warranty"),
     *              @OA\Property(property="contact_info", type="string", example="1234567890"),
     *              @OA\Property(property="contact_person", type="string", example="John Doe"),
     *              @OA\Property(property="doc", type="string", format="date", example="2023-10-21"),
     *              @OA\Property(property="failure_date", type="string", format="date", example="2023-10-25"),
     *              @OA\Property(property="failure_hmr", type="string", example="1234"),
     *              @OA\Property(property="price", type="number", format="float", example=500.00),
     *              @OA\Property(property="due_date_1", type="string", format="date", example="2023-11-01"),
     *              @OA\Property(property="due_date_2", type="string", format="date", example="2023-11-05"),
     *              @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-21T00:00:00.000000Z"),
     *              @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-21T00:00:00.000000Z")
     *          ))
     *       ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     */
    public function index(Request $request)
    {
        $query = Service::with('tasks', 'zone');

        if ($request->has('zone_id')) {
            $query->where('zone_id', $request->zone_id);
        }

        $services = $query->orderBy('created_at', 'desc')->get();

        //add additional data to new index response
        foreach ($services as $service) {
            $service->dealership_name = $service->dealership ? $service->dealership->name : null;
            $service->client_name = $service->client ? $service->client->name : null;
            $service->product_name = $service->product ? $service->product->name : null;
            $service->product_model_name = $service->productModel ? $service->productModel->name : null;
            $service->model_series_name = $service->modelSeries ? $service->modelSeries->name : null;
            $service->service_engineer_name = $service->serviceEngineer ? $service->serviceEngineer->name : null;
            $service->service_engineer_2_name = $service->serviceEngineer2 ? $service->serviceEngineer2->name : null;
            $service->zone_name = $service->zone ? $service->zone->name : null;
        }
        $clean = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'customer_id' => $service->client_id,
                'customer_name' => $service->client_name,
                'machine_id' => $service->product_id,
                'machine_name' => $service->product_name,
                'machine_model_id' => $service->product_model_id,
                'machine_model_name' => $service->product_model_name,
                'machine_series_id' => $service->model_series_id,
                'machine_series_name' => $service->model_series_name,
                'zone_id' => $service->zone_id,
                'zone_name' => $service->zone_name,
                'dealership_id' => $service->dealership_id,
                'dealership_name' => $service->dealership_name,
                'service_engineer_id' => $service->service_engineer_id,
                'service_engineer_name' => $service->service_engineer_name,
                'service_engineer_id_2' => $service->service_engineer_id_2,
                'service_engineer_2_name' => $service->service_engineer_2_name,
                'requested_location' => $service->requested_location,
                'location' => $service->requested_location,
                'latitude' => $service->latitude,
                'longitude' => $service->longitude,
                'referral_id' => $service->referral_id,
                'status' => $service->status,
                'machine_status' => $service->machine_status,
                'contact_info' => $service->contact_info,
                'contact_person' => $service->contact_person,
                'doc' => $service->doc,
                'failure_date' => $service->failure_date,
                'failure_hmr' => $service->failure_hmr,
                'price' => $service->price,
                'due_date_1' => $service->due_date_1,
                'due_date_2' => $service->due_date_2,
                'created_at' => $service->created_at,
                'updated_at' => $service->updated_at,
            ];
        });

        return response()->json($clean);
    }

    /**
     * @OA\Post(
     *      path="/services",
     *      operationId="storeService",
     *      tags={"Services"},
     *      summary="Store new service",
     *      description="Returns service data",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"customer_id", "machine_id"},
     *              @OA\Property(property="customer_id", type="integer",        format="int64", example=1),
     *              @OA\Property(property="machine_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="machine_model_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="machine_series_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="zone_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="name", type="string", example="Service Name"),
     *              @OA\Property(property="description", type="string", example="Nature of Complaint/Service"),
     *              @OA\Property(property="requested_location", type="string", example="Service Location"),
     *              @OA\Property(property="machine_status", type="string", example="warranty"),
     *              @OA\Property(property="contact_info", type="string", example="1234567890"),
     *              @OA\Property(property="contact_person", type="string", example="John Doe"),
     *              @OA\Property(property="doc", type="string", format="date", example="2023-10-21"),
     *              @OA\Property(property="failure_date", type="string", format="date", example="2023-10-25"),
     *              @OA\Property(property="failure_hmr", type="string", example="1234"),
     *              @OA\Property(property="price", type="number", format="float", example=500.00),
     *              @OA\Property(property="due_date_1", type="string", format="date", example="2023-11-01"),
     *              @OA\Property(property="due_date_2", type="string", format="date", example="2023-11-05"),
     *              @OA\Property(property="service_engineer_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="service_engineer_id_2", type="integer", format="int64", example=2),
     *              @OA\Property(property="latitude", type="number", format="float", example=12.345678),
     *              @OA\Property(property="longitude", type="number", format="float", example=98.765432)
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", format="int64", example=1),
     *              @OA\Property(property="name", type="string", example="Service Name"),
     *              @OA\Property(property="description", type="string", example="Nature of Complaint/Service"),
     *              @OA\Property(property="customer_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="customer_name", type="string", example="Customer Name"),
     *              @OA\Property(property="machine_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="machine_name", type="string", example="Machine Name"),
     *              @OA\Property(property="machine_model_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="machine_model_name", type="string", example="Machine Model Name"),
     *              @OA\Property(property="machine_series_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="machine_series_name", type="string", example="Machine Series Name"),
     *              @OA\Property(property="zone_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="zone_name", type="string", example="North Zone"),
     *              @OA\Property(property="dealership_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="dealership_name", type="string", example="Dealership Name"),
     *              @OA\Property(property="service_engineer_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="service_engineer_name", type="string", example="Service Engineer Name"),
     *              @OA\Property(property="service_engineer_id_2", type="integer", format="int64", example=2),
     *              @OA\Property(property="service_engineer_2_name", type="string", example="Service Engineer 2 Name"),
     *              @OA\Property(property="requested_location", type="string", example="Service Location"),
     *              @OA\Property(property="location", type="string", example="Service Location"),
     *              @OA\Property(property="latitude", type="number", format="float", example=12.345678),
     *              @OA\Property(property="longitude", type="number", format="float", example=98.765432),
     *              @OA\Property(property="referral_id", type="string", example="SVHE2110250001"),
     *              @OA\Property(property="status", type="string", example="open"),
     *              @OA\Property(property="machine_status", type="string", example="warranty"),
     *              @OA\Property(property="contact_info", type="string", example="1234567890"),
     *              @OA\Property(property="contact_person", type="string", example="John Doe"),
     *              @OA\Property(property="doc", type="string", format="date", example="2023-10-21"),
     *              @OA\Property(property="failure_date", type="string", format="date", example="2023-10-25"),
     *              @OA\Property(property="failure_hmr", type="string", example="1234"),
     *              @OA\Property(property="price", type="number", format="float", example=500.00),
     *              @OA\Property(property="due_date_1", type="string", format="date", example="2023-11-01"),
     *              @OA\Property(property="due_date_2", type="string", format="date", example="2023-11-05"),
     *              @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-21T00:00:00.000000Z"),
     *              @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-21T00:00:00.000000Z")
     *          )
     *       ),
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
     *      )
     * )
     */
    public function store(Request $request, TaskService $taskService)
    {
        $request->validate([
            'customer_id' => 'required|exists:clients,id',
            'machine_id' => 'required|exists:products,id',
            'machine_model_id' => 'nullable|exists:product_models,id',
            'machine_series_id' => 'nullable|exists:model_series,id',
            'zone_id' => 'nullable|exists:zones,id',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'requested_location' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
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
            'failure_hmr' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $dealershipId = $user->employee->dealership_id ?? null;
        $employeeId = $user->employee->id ?? null;

        $referralId = $this->generateUniqueReferralId();
        $serviceName = trim((string) $request->input('name', ''));
        if ($serviceName === '') {
            $serviceName = null;
        }

        $data = [
            'client_id' => $request->customer_id,
            'product_id' => $request->machine_id,
            'product_model_id' => $request->machine_model_id,
            'model_series_id' => $request->machine_series_id,
            'zone_id' => $request->zone_id,
            'name' => $serviceName,
            'description' => $request->description,
            'requested_location' => $request->requested_location ?? $request->location,
            'machine_status' => $request->machine_status,
            'type_of_service' => $request->type_of_service,
            'contact_info' => $request->contact_info,
            'service_engineer_id' => $request->service_engineer_id,
            'service_engineer_id_2' => $request->service_engineer_id_2,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'price' => $request->price,
            'due_date_1' => $request->due_date_1,
            'due_date_2' => $request->due_date_2,
            'contact_person' => $request->contact_person,
            'doc' => $request->doc,
            'failure_date' => $request->failure_date,
            'failure_hmr' => $request->failure_hmr,
            'referral_id' => $referralId,
            'dealership_id' => $dealershipId,
            'employee_id' => $employeeId,
        ];

        $service = Service::create($data);

        if ($service->service_engineer_id || $service->service_engineer_id_2) {
            $taskService->createTasksForService($request, $service);
        }

        $service->load(['client', 'product', 'productModel', 'modelSeries', 'serviceEngineer', 'serviceEngineer2', 'tasks']);

        return response()->json([
            'message' => 'Service created successfully.',
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'customer_id' => $service->client_id,
                'customer_name' => $service->client ? $service->client->name : null,
                'machine_id' => $service->product_id,
                'machine_name' => $service->product ? $service->product->name : null,
                'machine_model_id' => $service->product_model_id,
                'machine_model_name' => $service->productModel ? $service->productModel->name : null,
                'machine_series_id' => $service->model_series_id,
                'machine_series_name' => $service->modelSeries ? $service->modelSeries->name : null,
                'zone_id' => $service->zone_id,
                'zone_name' => $service->zone ? $service->zone->name : null,
                'dealership_id' => $service->dealership_id,
                'dealership_name' => $service->dealership ? $service->dealership->name : null,
                'service_engineer_id' => $service->service_engineer_id,
                'service_engineer_name' => $service->serviceEngineer ? $service->serviceEngineer->name : null,
                'service_engineer_id_2' => $service->service_engineer_id_2,
                'service_engineer_2_name' => $service->serviceEngineer2 ? $service->serviceEngineer2->name : null,
                'requested_location' => $service->requested_location,
                'location' => $service->requested_location,
                'latitude' => $service->latitude,
                'longitude' => $service->longitude,
                'referral_id' => $service->referral_id,
                'status' => $service->status,
                'machine_status' => $service->machine_status,
                'contact_info' => $service->contact_info,
                'contact_person' => $service->contact_person,
                'doc' => $service->doc,
                'failure_date' => $service->failure_date,
                'failure_hmr' => $service->failure_hmr,
                'price' => $service->price,
                'due_date_1' => $service->due_date_1,
                'due_date_2' => $service->due_date_2,
                'created_at' => $service->created_at,
                'updated_at' => $service->updated_at,
            ]
        ], 201);
    }

    protected function generateUniqueReferralId()
    {
        $date = \Carbon\Carbon::now()->format('dmy');
        $prefix = 'SVHE' . $date;

        $latestService = Service::where('referral_id', 'like', $prefix . '%')
            ->orderBy('referral_id', 'desc')
            ->first();

        $sequence = 1;
        if ($latestService) {
            $lastSequence = (int) str_replace($prefix, '', $latestService->referral_id);
            $sequence = $lastSequence + 1;
        }

        $referralId = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        // Ensure the generated referral ID is unique
        while (Service::where('referral_id', $referralId)->exists()) {
            $sequence++;
            $referralId = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        }

        return $referralId;
    }

    /**
     * @OA\Get(
     *      path="/services/{id}",
     *      operationId="getServiceById",
     *      tags={"Services"},
     *      summary="Get service information",
     *      description="Returns service data",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Service id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", format="int64", example=1),
     *              @OA\Property(property="name", type="string", example="Service Name"),
     *              @OA\Property(property="description", type="string", example="Nature of Complaint/Service"),
     *              @OA\Property(property="customer_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="customer_name", type="string", example="Customer Name"),
     *              @OA\Property(property="machine_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="machine_name", type="string", example="Machine Name"),
     *              @OA\Property(property="machine_model_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="machine_model_name", type="string", example="Machine Model Name"),
     *              @OA\Property(property="machine_series_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="machine_series_name", type="string", example="Machine Series Name"),
     *              @OA\Property(property="zone_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="zone_name", type="string", example="North Zone"),
     *              @OA\Property(property="dealership_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="dealership_name", type="string", example="Dealership Name"),
     *              @OA\Property(property="service_engineer_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="service_engineer_name", type="string", example="Service Engineer Name"),
     *              @OA\Property(property="service_engineer_id_2", type="integer", format="int64", example=2),
     *              @OA\Property(property="service_engineer_2_name", type="string", example="Service Engineer 2 Name"),
     *              @OA\Property(property="requested_location", type="string", example="Service Location"),
     *              @OA\Property(property="location", type="string", example="Service Location"),
     *              @OA\Property(property="latitude", type="number", format="float", example=12.345678),
     *              @OA\Property(property="longitude", type="number", format="float", example=98.765432),
     *              @OA\Property(property="referral_id", type="string", example="SVHE2110250001"),
     *              @OA\Property(property="status", type="string", example="open"),
     *              @OA\Property(property="machine_status", type="string", example="warranty"),
     *              @OA\Property(property="contact_info", type="string", example="1234567890"),
     *              @OA\Property(property="contact_person", type="string", example="John Doe"),
     *              @OA\Property(property="doc", type="string", format="date", example="2023-10-21"),
     *              @OA\Property(property="failure_date", type="string", format="date", example="2023-10-25"),
     *              @OA\Property(property="failure_hmr", type="string", example="1234"),
     *              @OA\Property(property="price", type="number", format="float", example=500.00),
     *              @OA\Property(property="due_date_1", type="string", format="date", example="2023-11-01"),
     *              @OA\Property(property="due_date_2", type="string", format="date", example="2023-11-05"),
     *              @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-21T00:00:00.000000Z"),
     *              @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-21T00:00:00.000000Z")
     *          )
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function show(Service $service)
    {
        $service->load(['client', 'product', 'productModel', 'modelSeries', 'serviceEngineer', 'serviceEngineer2', 'tasks']);

        $service->dealership_name = $service->dealership ? $service->dealership->name : null;
        $service->client_name = $service->client ? $service->client->name : null;
        $service->product_name = $service->product ? $service->product->name : null;
        $service->product_model_name = $service->productModel ? $service->productModel->name : null;
        $service->model_series_name = $service->modelSeries ? $service->modelSeries->name : null;
        $service->zone_name = $service->zone ? $service->zone->name : null;
        $service->service_engineer_name = $service->serviceEngineer ? $service->serviceEngineer->name : null;
        $service->service_engineer_2_name = $service->serviceEngineer2 ? $service->serviceEngineer2->name : null;

        return response()->json([
            'id' => $service->id,
            'name' => $service->name,
            'description' => $service->description,
            'customer_id' => $service->client_id,
            'customer_name' => $service->client_name,
            'machine_id' => $service->product_id,
            'machine_name' => $service->product_name,
            'machine_model_id' => $service->product_model_id,
            'machine_model_name' => $service->product_model_name,
            'machine_series_id' => $service->model_series_id,
            'machine_series_name' => $service->model_series_name,
            'zone_id' => $service->zone_id,
            'zone_name' => $service->zone_name,
            'dealership_id' => $service->dealership_id,
            'dealership_name' => $service->dealership_name,
            'service_engineer_id' => $service->service_engineer_id,
            'service_engineer_name' => $service->service_engineer_name,
            'service_engineer_id_2' => $service->service_engineer_id_2,
            'service_engineer_2_name' => $service->service_engineer_2_name,
            'requested_location' => $service->requested_location,
            'location' => $service->requested_location,
            'latitude' => $service->latitude,
            'longitude' => $service->longitude,
            'referral_id' => $service->referral_id,
            'status' => $service->status,
            'machine_status' => $service->machine_status,
            'type_of_service' => $service->type_of_service,
            'service_interval' => $service->service_interval,
            'contact_info' => $service->contact_info,
            'contact_person' => $service->contact_person,
            'doc' => $service->doc,
            'failure_date' => $service->failure_date,
            'failure_hmr' => $service->failure_hmr,
            'price' => $service->price,
            'due_date_1' => $service->due_date_1,
            'due_date_2' => $service->due_date_2,
            'created_at' => $service->created_at,
            'updated_at' => $service->updated_at,
        ]);
    }

    /**
     * @OA\Put(
     *      path="/services/{id}",
     *      operationId="updateService",
     *      tags={"Services"},
     *      summary="Update existing service",
     *      description="Returns updated service data",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Service id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/Service")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", format="int64", example=1),
     *              @OA\Property(property="name", type="string", example="Service Name"),
     *              @OA\Property(property="description", type="string", example="Nature of Complaint/Service"),
     *              @OA\Property(property="customer_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="customer_name", type="string", example="Customer Name"),
     *              @OA\Property(property="machine_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="machine_name", type="string", example="Machine Name"),
     *              @OA\Property(property="machine_model_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="machine_model_name", type="string", example="Machine Model Name"),
     *              @OA\Property(property="machine_series_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="machine_series_name", type="string", example="Machine Series Name"),
     *              @OA\Property(property="zone_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="zone_name", type="string", example="North Zone"),
     *              @OA\Property(property="dealership_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="dealership_name", type="string", example="Dealership Name"),
     *              @OA\Property(property="service_engineer_id", type="integer", format="int64", example=1),
     *              @OA\Property(property="service_engineer_name", type="string", example="Service Engineer Name"),
     *              @OA\Property(property="service_engineer_id_2", type="integer", format="int64", example=2),
     *              @OA\Property(property="service_engineer_2_name", type="string", example="Service Engineer 2 Name"),
     *              @OA\Property(property="requested_location", type="string", example="Service Location"),
     *              @OA\Property(property="location", type="string", example="Service Location"),
     *              @OA\Property(property="latitude", type="number", format="float", example=12.345678),
     *              @OA\Property(property="longitude", type="number", format="float", example=98.765432),
     *              @OA\Property(property="referral_id", type="string", example="SVHE2110250001"),
     *              @OA\Property(property="status", type="string", example="open"),
     *              @OA\Property(property="machine_status", type="string", example="warranty"),
     *              @OA\Property(property="contact_info", type="string", example="1234567890"),
     *              @OA\Property(property="created_at", type="string", format="date-time", example="2025-10-21T00:00:00.000000Z"),
     *              @OA\Property(property="updated_at", type="string", format="date-time", example="2025-10-21T00:00:00.000000Z")
     *          )
     *       ),
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
     *      )
     * )
     */
    public function update(Request $request, Service $service, TaskService $taskService)
    {
        $request->validate([
            'customer_id' => 'required|exists:clients,id',
            'machine_id' => 'required|exists:products,id',
            'machine_model_id' => 'nullable|exists:product_models,id',
            'machine_series_id' => 'nullable|exists:model_series,id',
            'zone_id' => 'nullable|exists:zones,id',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'requested_location' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
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
            'failure_hmr' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();
        $employeeId = $user->employee->id ?? null;

        $requestedReferralId = trim((string) $request->input('referral_id', ''));
        $referralId = $requestedReferralId !== '' ? $requestedReferralId : ($service->referral_id ?: $this->generateUniqueReferralId());
        $serviceName = trim((string) $request->input('name', ''));
        if ($serviceName === '') {
            $serviceName = null;
        }

        $data = [
            'client_id' => $request->customer_id,
            'product_id' => $request->machine_id,
            'product_model_id' => $request->machine_model_id,
            'model_series_id' => $request->machine_series_id,
            'zone_id' => $request->zone_id,
            'name' => $serviceName,
            'description' => $request->description,
            'requested_location' => $request->requested_location ?? $request->location,
            'machine_status' => $request->machine_status,
            'type_of_service' => $request->type_of_service,
            'contact_info' => $request->contact_info,
            'service_engineer_id' => $request->service_engineer_id,
            'service_engineer_id_2' => $request->service_engineer_id_2,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'price' => $request->price,
            'due_date_1' => $request->due_date_1,
            'due_date_2' => $request->due_date_2,
            'contact_person' => $request->contact_person,
            'doc' => $request->doc,
            'failure_date' => $request->failure_date,
            'failure_hmr' => $request->failure_hmr,
            'referral_id' => $referralId,
        ];

        $service->update($data);

        if ($service->service_engineer_id || $service->service_engineer_id_2) {
            $taskService->createTasksForService($request, $service);
        }

        $service->refresh();
        $service->load(['client', 'product', 'productModel', 'modelSeries', 'serviceEngineer', 'serviceEngineer2', 'tasks']);

        return response()->json([
            'message' => 'Service updated successfully.',
            'service' => [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'customer_id' => $service->client_id,
                'customer_name' => $service->client ? $service->client->name : null,
                'machine_id' => $service->product_id,
                'machine_name' => $service->product ? $service->product->name : null,
                'machine_model_id' => $service->product_model_id,
                'machine_model_name' => $service->productModel ? $service->productModel->name : null,
                'machine_series_id' => $service->model_series_id,
                'machine_series_name' => $service->modelSeries ? $service->modelSeries->name : null,
                'zone_id' => $service->zone_id,
                'zone_name' => $service->zone ? $service->zone->name : null,
                'dealership_id' => $service->dealership_id,
                'dealership_name' => $service->dealership ? $service->dealership->name : null,
                'service_engineer_id' => $service->service_engineer_id,
                'service_engineer_name' => $service->serviceEngineer ? $service->serviceEngineer->name : null,
                'service_engineer_id_2' => $service->service_engineer_id_2,
                'service_engineer_2_name' => $service->serviceEngineer2 ? $service->serviceEngineer2->name : null,
                'requested_location' => $service->requested_location,
                'location' => $service->requested_location,
                'latitude' => $service->latitude,
                'longitude' => $service->longitude,
                'referral_id' => $service->referral_id,
                'status' => $service->status,
                'machine_status' => $service->machine_status,
                'contact_info' => $service->contact_info,
                'contact_person' => $service->contact_person,
                'doc' => $service->doc,
                'failure_date' => $service->failure_date,
                'failure_hmr' => $service->failure_hmr,
                'price' => $service->price,
                'due_date_1' => $service->due_date_1,
                'due_date_2' => $service->due_date_2,
                'created_at' => $service->created_at,
                'updated_at' => $service->updated_at,
            ]
        ], 200);
    }

    /**
     * @OA\Delete(
     *      path="/services/{id}",
     *      operationId="deleteService",
     *      tags={"Services"},
     *      summary="Delete existing service",
     *      description="Deletes a record and returns no content",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Service id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Service deleted successfully.")
     *          )
     *       ),
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
     *          description="Resource Not Found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Service not found.")
     *          )
     *      )
     * )
     */
    public function destroy($id)
    {
        $service = Service::find($id);

        if (!$service) {
            return response()->json(['message' => 'Service not found.'], 404);
        }

        $service->delete();

        return response()->json(['message' => 'Service deleted successfully.'], 200);
    }

    /**
     * @OA\Get(
     *      path="/services/clients",
     *      operationId="getServiceClients",
     *      tags={"Services"},
     *      summary="Get list of clients for services",
     *      description="Returns list of clients",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Client Name")
     *              )
     *          )
     *       ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function getClients()
    {
        $user = Auth::user();

        if ($user->user_type === 'employee' && $user->employee && $user->employee->dealership_id) {
            $clients = Client::where(function($q) use ($user) {
                $q->where('dealership_id', $user->employee->dealership_id)
                  ->orWhereNull('dealership_id');
            })->get(['id', 'name']);
        } else {
            $clients = Client::all(['id', 'name']);
        }
        return response()->json(['clients' => $clients]);
    }

    /**
     * @OA\Get(
     *      path="/services/products",
     *      operationId="getServiceProducts",
     *      tags={"Services"},
     *      summary="Get list of products for services based on client",
     *      description="Returns list of products",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="client_id",
     *          description="Client id",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Product Name")
     *              )
     *          )
     *       ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function getProducts(Request $request)
    {
        $clientId = $request->input('client_id');
        if ($clientId) {
            $client = Client::with(['leads.items', 'products', 'services'])->find($clientId);
            if ($client) {
                // Get product IDs from leads table (won/converted)
                $leadProductIds = $client->leads->whereIn('status', ['win', 'converted_to_client'])->pluck('product_id')->filter()->all();
                
                // Get product IDs from lead_items table
                $itemProductIds = $client->leads->whereIn('status', ['win', 'converted_to_client'])->flatMap(function($lead) {
                    return $lead->items->pluck('product_id');
                })->filter()->all();

                // Get product IDs from client_products table
                $clientProductIds = $client->products->pluck('product_id')->filter()->all();

                // Get product IDs from existing services
                $serviceProductIds = $client->services->pluck('product_id')->filter()->all();

                $productIds = array_unique(array_merge($leadProductIds, $itemProductIds, $clientProductIds, $serviceProductIds));
                
                $products = Product::whereIn('id', $productIds)->get(['id', 'name']);
                
                // include the product models and model series as nested data, filtered by client
                $products->each(function ($product) use ($client) {
                    $productId = $product->id;
                    
                    $leadModelIds = $client->leads->whereIn('status', ['win', 'converted_to_client'])->where('product_id', $productId)->pluck('product_model_id')->filter()->all();
                    $itemModelIds = $client->leads->whereIn('status', ['win', 'converted_to_client'])->flatMap(function($lead) use ($productId) {
                        return $lead->items->where('product_id', $productId)->pluck('product_model_id');
                    })->filter()->all();
                    $clientModelIds = $client->products->where('product_id', $productId)->pluck('product_model_id')->filter()->all();
                    $serviceModelIds = $client->services->where('product_id', $productId)->pluck('product_model_id')->filter()->all();

                    $modelIds = array_unique(array_merge($leadModelIds, $itemModelIds, $clientModelIds, $serviceModelIds));

                    if (!empty($modelIds)) {
                        $product->models = ProductModel::whereIn('id', $modelIds)->get(['id', 'name']);
                    } else {
                        $product->models = $product->models()->get(['id', 'name']);
                    }

                    $product->models->each(function ($model) use ($client) {
                        $modelId = $model->id;
                        $leadSeriesIds = $client->leads->whereIn('status', ['win', 'converted_to_client'])->where('product_model_id', $modelId)->pluck('model_series_id')->filter()->all();
                        $itemSeriesIds = $client->leads->whereIn('status', ['win', 'converted_to_client'])->flatMap(function($lead) use ($modelId) {
                            return $lead->items->where('product_model_id', $modelId)->pluck('model_series_id');
                        })->filter()->all();
                        $clientSeriesIds = $client->products->where('product_model_id', $modelId)->pluck('model_series_id')->filter()->all();
                        $serviceSeriesIds = $client->services->where('product_model_id', $modelId)->pluck('model_series_id')->filter()->all();

                        $seriesIds = array_unique(array_merge($leadSeriesIds, $itemSeriesIds, $clientSeriesIds, $serviceSeriesIds));

                        if (!empty($seriesIds)) {
                            $model->series = \App\Models\ModelSeries::whereIn('id', $seriesIds)->get(['id', 'name']);
                        } else {
                            $model->series = $model->modelSeries()->get(['id', 'name']);
                        }
                    });
                });
                return response()->json(['products' => $products]);
            }
        }
        // If no client_id or client not found, return an empty array
        return response()->json(['products' => []]);
    }

    /**
     * @OA\Get(
     *      path="/services/product-models",
     *      operationId="getServiceProductModels",
     *      tags={"Services"},
     *      summary="Get list of product models for services based on product",
     *      description="Returns list of product models",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="product_id",
     *          description="Product id",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Product Model Name")
     *              )
     *          )
     *       ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function getProductModels(Request $request)
    {
        $productId = $request->input('product_id');
        $clientId = $request->input('client_id');

        if ($clientId && $productId) {
            $client = Client::with(['leads.items', 'products', 'services'])->find($clientId);
            if ($client) {
                $leadModelIds = $client->leads->whereIn('status', ['win', 'converted_to_client'])->where('product_id', $productId)->pluck('product_model_id')->filter()->all();
                $itemModelIds = $client->leads->whereIn('status', ['win', 'converted_to_client'])->flatMap(function($lead) use ($productId) {
                    return $lead->items->where('product_id', $productId)->pluck('product_model_id');
                })->filter()->all();
                $clientModelIds = $client->products->where('product_id', $productId)->pluck('product_model_id')->filter()->all();
                $serviceModelIds = $client->services->where('product_id', $productId)->pluck('product_model_id')->filter()->all();

                $modelIds = array_unique(array_merge($leadModelIds, $itemModelIds, $clientModelIds, $serviceModelIds));
                
                if (!empty($modelIds)) {
                    $productModels = ProductModel::whereIn('id', $modelIds)->get(['id', 'name']);
                    return response()->json(['product_models' => $productModels]);
                }
            }
        }

        $productModels = ProductModel::where('product_id', $productId)->get(['id', 'name']);
        return response()->json(['product_models' => $productModels]);
    }

    /**
     * @OA\Get(
     *      path="/services/model-series",
     *      operationId="getServiceModelSeries",
     *      tags={"Services"},
     *      summary="Get list of model series for services based on product models",
     *      description="Returns list of model series",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="product_model_ids[]",
     *          description="Product Model IDs",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="array",
     *              @OA\Items(type="integer")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Model Series Name")
     *              )
     *          )
     *       ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function getModelSeries(Request $request)
    {
        $productModelIds = $request->input('product_model_ids');
        $clientId = $request->input('client_id');

        if (empty($productModelIds)) {
            return response()->json(['model_series' => []]);
        }

        if (!is_array($productModelIds)) {
            $productModelIds = [$productModelIds];
        }

        if ($clientId) {
            $client = Client::with(['leads.items', 'products', 'services'])->find($clientId);
            if ($client) {
                $leadSeriesIds = $client->leads->whereIn('status', ['win', 'converted_to_client'])->whereIn('product_model_id', $productModelIds)->pluck('model_series_id')->filter()->all();
                $itemSeriesIds = $client->leads->whereIn('status', ['win', 'converted_to_client'])->flatMap(function($lead) use ($productModelIds) {
                    return $lead->items->whereIn('product_model_id', $productModelIds)->pluck('model_series_id');
                })->filter()->all();
                $clientSeriesIds = $client->products->whereIn('product_model_id', $productModelIds)->pluck('model_series_id')->filter()->all();
                $serviceSeriesIds = $client->services->whereIn('product_model_id', $productModelIds)->pluck('model_series_id')->filter()->all();

                $seriesIds = array_unique(array_merge($leadSeriesIds, $itemSeriesIds, $clientSeriesIds, $serviceSeriesIds));
                
                if (!empty($seriesIds)) {
                    $modelSeries = \App\Models\ModelSeries::whereIn('id', $seriesIds)->get(['id', 'name']);
                    return response()->json(['model_series' => $modelSeries]);
                }
            }
        }

        $modelSeries = \App\Models\ModelSeries::whereIn('product_model_id', $productModelIds)->get(['id', 'name']);
        return response()->json(['model_series' => $modelSeries]);
    }

    /**
     * @OA\Get(
     *      path="/services/service-engineers",
     *      operationId="getServiceEngineers",
     *      tags={"Services"},
     *      summary="Get list of service engineers",
     *      description="Returns list of service engineers",
     *      security={{"bearerAuth":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="name", type="string", example="Service Engineer Name")
     *              )
     *          )
     *       ),
     *      @OA\Response(
     *          response=499,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function getServiceEngineers(Request $request)
    {
        $user = Auth::user();
        $dealershipId = null;

        if ($user && $user->user_type === 'employee' && $user->employee && $user->employee->dealership_id) {
            $dealershipId = $user->employee->dealership_id;
        } else {
            // Prefer dealership from the current client/service context (without dealership_id params)
            $customerId = $request->query('customer_id') ?? $request->query('client_id');
            if ($customerId) {
                $dealershipId = Client::whereKey($customerId)->value('dealership_id');
            } else {
                $serviceId = $request->query('service_id');
                if ($serviceId) {
                    $dealershipId = Service::whereKey($serviceId)->value('dealership_id');
                    if (!$dealershipId) {
                        $dealershipId = Service::whereKey($serviceId)
                            ->with('client:id,dealership_id')
                            ->first()?->client?->dealership_id;
                    }
                }
            }
        }

        $query = Employee::where(function ($q) {
            $q->whereHas('role', function ($sq) {
                $sq->where(function ($ssq) {
                    $ssq->whereRaw('LOWER(REPLACE(role, "_", " ")) = ?', ['service engineer'])
                        ->orWhereRaw('LOWER(REPLACE(role, " ", "_")) = ?', ['service_engineer'])
                        ->orWhereRaw('UPPER(role) = ?', ['SERVICE ENGINEER']);
                });
            })
            ->orWhereRaw('LOWER(REPLACE(designation, "_", " ")) = ?', ['service engineer'])
            ->orWhereRaw('LOWER(REPLACE(designation, " ", "_")) = ?', ['service_engineer'])
            ->orWhereRaw('UPPER(designation) = ?', ['SERVICE ENGINEER']);
        });

        if ($dealershipId) {
            $query->where('dealership_id', $dealershipId);
        }

        $serviceEngineers = $query->with('dealership:id,name')->get(['id', 'role_id', 'name', 'dealership_id']);

        $formattedEngineers = $serviceEngineers->map(function ($engineer) use ($dealershipId) {
            $dealershipName = $engineer->dealership ? ' (' . $engineer->dealership->name . ')' : ' (No Dealership)';
            $isSameDealership = ($dealershipId && $engineer->dealership_id == $dealershipId);

            return [
                'id' => $engineer->id,
                'name' => $engineer->name . $dealershipName,
                'is_same_dealership' => $isSameDealership,
                'dealership_id' => $engineer->dealership_id,
            ];
        });

        return response()->json(['service_engineers' => $formattedEngineers]);
    }

    /**
     * @OA\Put(
     *      path="/services/{id}/assign-engineer",
     *      operationId="assignEngineerToService",
     *      tags={"Services"},
     *      summary="Assign engineer to service",
     *      description="Assigns one or two engineers to a service and returns success message",
     *      security={{"bearerAuth":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Service id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="service_engineer_id", type="integer", example=1),
     *              @OA\Property(property="service_engineer_id_2", type="integer", example=2),
     *              @OA\Property(property="due_date_1", type="string", format="date", example="2023-11-01"),
     *              @OA\Property(property="due_date_2", type="string", format="date", example="2023-11-05")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Service engineers assigned successfully.")
     *          )
     *       ),
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
     *      )
     * )
     */
    public function assignEngineer(Request $request, Service $service, \App\Services\TaskService $taskService)
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


        $data = $request->only(['service_engineer_id', 'service_engineer_id_2', 'due_date_1', 'due_date_2']);

        if (empty($data) && !$request->hasAny(['service_engineer_id', 'service_engineer_id_2'])) {
            return response()->json(['message' => 'No engineer assignment data provided.'], 400);
        }

        $service->update($data);

        $taskService->createTasksForService($request, $service);
        $service->refresh();

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
                    $payloadData = array_merge($data ?? [], [
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
                        'service_id' => $service->id,
                        'onesignal_response' => $response,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send OneSignal notification to primary engineer.', [
                        'employee_id' => $employee->id,
                        'player_id' => $employee->user->player_id,
                        'service_id' => $service->id,
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
                    $payloadData = array_merge($data ?? [], [
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

                    Log::info('OneSignal notification sent successfully to secondary engineer.', [
                        'employee_id' => $employee->id,
                        'player_id' => $employee->user->player_id,
                        'service_id' => $service->id,
                        'onesignal_response' => $response,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send OneSignal notification to secondary engineer.', [
                        'employee_id' => $employee->id,
                        'player_id' => $employee->user->player_id,
                        'service_id' => $service->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        // Send notification to service managers of the dealership(s) involved
        $dealershipIds = collect([])
            ->when($request->service_engineer_id, function ($collection) use ($request) {
                $employee = Employee::find($request->service_engineer_id);
                return $employee ? $collection->push($employee->dealership_id) : $collection;
            })
            ->when($request->service_engineer_id_2, function ($collection) use ($request) {
                $employee = Employee::find($request->service_engineer_id_2);
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
                        $payloadData = array_merge($data ?? [], [
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
                            'service_id' => $service->id,
                            'dealership_id' => $dealershipId,
                            'onesignal_response' => $response,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to send OneSignal notification to service manager.', [
                            'manager_id' => $manager->id,
                            'player_id' => $manager->user->player_id,
                            'service_id' => $service->id,
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
}
