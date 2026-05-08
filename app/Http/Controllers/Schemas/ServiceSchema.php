<?php

namespace App\Http\Controllers\Schemas;

/**
 * @OA\Schema(
 *     schema="Service",
 *     title="Service",
 *     description="Service model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID of the service",
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="customer_id",
 *         type="integer",
 *         description="ID of the customer",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="machine_id",
 *         type="integer",
 *         description="ID of the machine",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="machine_model_id",
 *         type="integer",
 *         description="ID of the machine model",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="machine_series_id",
 *         type="integer",
 *         description="ID of the machine series",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the service",
 *         example="Service Name"
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="Nature of Complaint/Service",
 *         example="Machine not starting"
 *     ),
 *     @OA\Property(
 *         property="requested_location",
 *         type="string",
 *         description="Requested location for the service",
 *         example="Service Location"
 *     ),
 *     @OA\Property(
 *         property="machine_status",
 *         type="string",
 *         description="Status of the machine",
 *         enum={"warranty", "extended_warranty", "post_warranty"},
 *         example="warranty"
 *     ),
 *     @OA\Property(
 *         property="type_of_service",
 *         type="string",
 *         description="Type of service",
 *         enum={"free_service", "warranty_claimable", "warranty_coupon_service", "campaign", "paid_service", "coupon_service", "amc"},
 *         example="amc"
 *     ),
 *     @OA\Property(
 *         property="service_interval",
 *         type="integer",
 *         description="Service interval HMR; must be a multiple of 50, 250, 400, 1000, 2000, 5000, or 10000",
 *         example=250
 *     ),
 *     @OA\Property(
 *         property="contact_info",
 *         type="string",
 *         description="Contact information for the service",
 *         example="1234567890"
 *     ),
 *     @OA\Property(
 *         property="service_engineer_id",
 *         type="integer",
 *         description="ID of the primary service engineer",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="service_engineer_id_2",
 *         type="integer",
 *         description="ID of the secondary service engineer",
 *         example=2
 *     ),
 *     @OA\Property(
 *         property="latitude",
 *         type="number",
 *         format="float",
 *         description="Latitude of the service location",
 *         example=12.345678
 *     ),
 *     @OA\Property(
 *         property="longitude",
 *         type="number",
 *         format="float",
 *         description="Longitude of the service location",
 *         example=98.765432
 *     ),
 *     @OA\Property(
 *         property="dealership_id",
 *         type="integer",
 *         description="ID of the dealership",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="contact_person",
 *         type="string",
 *         description="Contact person for the service",
 *         example="John Doe"
 *     ),
 *     @OA\Property(
 *         property="doc",
 *         type="string",
 *         format="date",
 *         description="Date of Commissioning",
 *         example="2023-10-21"
 *     ),
 *     @OA\Property(
 *         property="failure_date",
 *         type="string",
 *         format="date",
 *         description="Date of failure",
 *         example="2023-10-25"
 *     ),
 *     @OA\Property(
 *         property="failure_hmr",
 *         type="string",
 *         description="Failure HMR",
 *         example="1234"
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         format="float",
 *         description="Price of the service",
 *         example=500.00
 *     ),
 *     @OA\Property(
 *         property="due_date_1",
 *         type="string",
 *         format="date",
 *         description="Primary due date",
 *         example="2023-11-01"
 *     ),
 *     @OA\Property(
 *         property="due_date_2",
 *         type="string",
 *         format="date",
 *         description="Secondary due date",
 *         example="2023-11-05"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Date and time of creation",
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Date and time of last update",
 *         readOnly=true
 *     )
 * )
 */
class ServiceSchema {}
