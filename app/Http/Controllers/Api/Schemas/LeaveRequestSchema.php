<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="LeaveRequest",
 *     title="LeaveRequest",
 *     description="Leave Request model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the leave request"
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the user who made the request"
 *     ),
 *     @OA\Property(
 *         property="leave_type",
 *         type="string",
 *         description="Type of leave (e.g., casual, sick, paid, unpaid, compensatory)"
 *     ),
 *     @OA\Property(
 *         property="start_date",
 *         type="string",
 *         format="date",
 *         description="Start date of the leave"
 *     ),
 *     @OA\Property(
 *         property="end_date",
 *         type="string",
 *         format="date",
 *         description="End date of the leave"
 *     ),
 *     @OA\Property(
 *         property="reason",
 *         type="string",
 *         description="Reason for the leave"
 *     ),
 *     @OA\Property(
 *         property="duration",
 *         type="string",
 *         description="Duration of the leave"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="Status of the leave request (pending, approved, rejected, cancelled, cancelled by admin, approved and forwarded)"
 *     ),
 *     @OA\Property(
 *         property="attachment",
 *         type="string",
 *         nullable=true,
 *         description="Path to the attachment file"
 *     ),
 *     @OA\Property(
 *         property="is_compensatory",
 *         type="boolean",
 *         description="Is the leave compensatory"
 *     ),
 *     @OA\Property(
 *         property="compensatory_date",
 *         type="string",
 *         format="date",
 *         nullable=true,
 *         description="Date of the compensatory leave"
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
 *
 * @OA\Schema(
 *     schema="LeaveRequestPaginatedResponse",
 *     title="LeaveRequestPaginatedResponse",
 *     description="Paginated list of leave requests",
 *     @OA\Property(property="current_page", type="integer"),
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/LeaveRequest")),
 *     @OA\Property(property="first_page_url", type="string"),
 *     @OA\Property(property="from", type="integer"),
 *     @OA\Property(property="last_page", type="integer"),
 *     @OA\Property(property="last_page_url", type="string"),
 *     @OA\Property(property="next_page_url", type="string", nullable=true),
 *     @OA\Property(property="path", type="string"),
 *     @OA\Property(property="per_page", type="integer"),
 *     @OA\Property(property="prev_page_url", type="string", nullable=true),
 *     @OA\Property(property="to", type="integer"),
 *     @OA\Property(property="total", type="integer"),
 * )
 */
class LeaveRequestSchema
{
    // This class is just a container for Swagger Schema annotations.
}
