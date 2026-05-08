<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Clock",
 *     title="Clock",
 *     description="Clock record object",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the clock record"
 *     ),
 *     @OA\Property(
 *         property="employee_id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the associated employee"
 *     ),
 *     @OA\Property(
 *         property="clock_in_time",
 *         type="string",
 *         format="date-time",
 *         description="Clock in timestamp"
 *     ),
 *     @OA\Property(
 *         property="clock_out_time",
 *         type="string",
 *         format="date-time",
 *         nullable=true,
 *         description="Clock out timestamp"
 *     ),
 *     @OA\Property(
 *         property="remarks",
 *         type="string",
 *         nullable=true,
 *         description="Remarks provided during clock out"
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
class ClockSchema
{
    // This class is just a container for Swagger Schema annotations.
}
