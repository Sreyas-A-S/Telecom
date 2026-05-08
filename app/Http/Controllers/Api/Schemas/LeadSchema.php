<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Lead",
 *     title="Lead",
 *     description="Lead object",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the lead"
 *     ),
 *     @OA\Property(
 *         property="salutation",
 *         type="string",
 *         nullable=true,
 *         description="Salutation of the lead"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the lead"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         nullable=true,
 *         description="Email of the lead"
 *     ),
 *     @OA\Property(
 *         property="phone_number",
 *         type="string",
 *         nullable=true,
 *         description="Phone number of the lead"
 *     ),
 *     @OA\Property(
 *         property="agent_id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the associated agent"
 *     ),
 *     @OA\Property(
 *         property="agent_type",
 *         type="string",
 *         description="Type of the agent (e.g., App\\Models\\Employee, App\\Models\\Agent)"
 *     ),
 *     @OA\Property(
 *         property="lead_source_id",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="ID of the associated lead source"
 *     ),
 *     @OA\Property(
 *         property="lead_category_id",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="ID of the associated lead category"
 *     ),
 *     @OA\Property(
 *         property="lead_value",
 *         type="number",
 *         format="float",
 *         nullable=true,
 *         description="Value of the lead"
 *     ),
 *     @OA\Property(
 *         property="allow_follow_up",
 *         type="boolean",
 *         description="Whether follow-up is allowed"
 *     ),
 *     @OA\Property(
 *         property="chance_of_success",
 *         type="integer",
 *         nullable=true,
 *         description="Chance of success (0-100)"
 *     ),
 *     @OA\Property(
 *         property="product_id",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="ID of the associated product"
 *     ),
 *     @OA\Property(
 *         property="product_model_id",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="ID of the associated product model"
 *     ),
 *     @OA\Property(
 *         property="model_series_id",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="ID of the associated model series"
 *     ),
 *     @OA\Property(
 *         property="dealership_id",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="ID of the associated dealership"
 *     ),
 *     @OA\Property(
 *         property="location",
 *         type="string",
 *         nullable=true,
 *         description="Location of the lead"
 *     ),
 *     @OA\Property(
 *         property="quantity",
 *         type="integer",
 *         nullable=true,
 *         description="Quantity of the product"
 *     ),
 *     @OA\Property(
 *         property="financier",
 *         type="string",
 *         nullable=true,
 *         description="Financier for the lead"
 *     ),
 *     @OA\Property(
 *         property="type",
 *         type="string",
 *         nullable=true,
 *         description="Type of lead"
 *     ),
 *     @OA\Property(
 *         property="login_status",
 *         type="string",
 *         nullable=true,
 *         description="Login status"
 *     ),
 *     @OA\Property(
 *         property="stage",
 *         type="string",
 *         nullable=true,
 *         description="Stage of the lead"
 *     ),
 *     @OA\Property(
 *         property="remarks",
 *         type="string",
 *         nullable=true,
 *         description="Remarks about the lead"
 *     ),
 *     @OA\Property(
 *         property="billing",
 *         type="string",
 *         nullable=true,
 *         description="Billing information"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="ID of the user who created the lead"
 *     ),
 *     @OA\Property(
 *         property="client_id",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="ID of the associated client"
 *     ),
 *     @OA\Property(
 *         property="last_status_before_conversion",
 *         type="string",
 *         nullable=true,
 *         description="Last status before conversion to client"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Current status of the lead"
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Creation timestamp"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Last update timestamp"
 *     )
 * )
 */
