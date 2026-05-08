<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Client",
 *     title="Client",
 *     description="Client object",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the client"
 *     ),
 *     @OA\Property(
 *         property="salutation",
 *         type="string",
 *         nullable=true,
 *         description="Salutation of the client"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the client"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         nullable=true,
 *         description="Email of the client"
 *     ),
 *     @OA\Property(
 *         property="phone_number",
 *         type="string",
 *         nullable=true,
 *         description="Phone number of the client"
 *     ),
 *     @OA\Property(
 *         property="address",
 *         type="string",
 *         nullable=true,
 *         description="Address of the client"
 *     ),
 *     @OA\Property(
 *         property="dealership_id",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="ID of the associated dealership"
 *     ),
 *     @OA\Property(
 *         property="employee_id",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="ID of the associated employee"
 *     ),
 *     @OA\Property(
 *         property="agent_type",
 *         type="string",
 *         nullable=true,
 *         description="Type of the agent (e.g., App\\Models\\Employee, App\\Models\\Agent)"
 *     ),
 *     @OA\Property(
 *         property="agent_id",
 *         type="integer",
 *         format="int64",
 *         nullable=true,
 *         description="ID of the associated agent"
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
 *         property="notes",
 *         type="string",
 *         nullable=true,
 *         description="Notes about the client"
 *     ),
 *     @OA\Property(
 *         property="gps_location",
 *         type="string",
 *         nullable=true,
 *         description="GPS location of the client"
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
class ClientSchema
{
    // This class is just a container for Swagger Schema annotations.
}
