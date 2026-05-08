<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="ExpenseRequest",
 *      required={"user_id", "expense_type", "amount", "date", "status"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *          format="int64"
 *      ),
 *      @OA\Property(
 *          property="user_id",
 *          description="user_id",
 *          type="integer",
 *          format="int64"
 *      ),
 *      @OA\Property(
 *          property="reporting_to",
 *          description="reporting_to",
 *          type="string",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="expense_type",
 *          description="expense_type",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="amount",
 *          description="amount",
 *          type="number",
 *          format="float"
 *      ),
 *      @OA\Property(
 *          property="approved_amount",
 *          description="approved_amount",
 *          type="number",
 *          format="float",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="date",
 *          description="date",
 *          type="string",
 *          format="date"
 *      ),
 *      @OA\Property(
 *          property="status",
 *          description="status",
 *          type="string",
 *          enum={"pending", "approved", "rejected", "processed", "approved and forwarded"}
 *      ),
 *      @OA\Property(
 *          property="description",
 *          description="description",
 *          type="string",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="image",
 *          description="image",
 *          type="string",
 *          nullable=true
 *      ),
 *      @OA\Property(
 *          property="created_at",
 *          description="created_at",
 *          type="string",
 *          format="date-time"
 *      ),
 *      @OA\Property(
 *          property="updated_at",
 *          description="updated_at",
 *          type="string",
 *          format="date-time"
 *      )
 * )
 */
class ExpenseRequestSchema {}
