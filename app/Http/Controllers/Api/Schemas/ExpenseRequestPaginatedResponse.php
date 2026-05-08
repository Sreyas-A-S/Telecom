<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="ExpenseRequestPaginatedResponse",
 *      @OA\Property(property="current_page", type="integer"),
 *      @OA\Property(
 *          property="data",
 *          type="array",
 *          @OA\Items(ref="#/components/schemas/ExpenseRequest")
 *      ),
 *      @OA\Property(property="first_page_url", type="string"),
 *      @OA\Property(property="from", type="integer"),
 *      @OA\Property(property="last_page", type="integer"),
 *      @OA\Property(property="last_page_url", type="string"),
 *      @OA\Property(property="next_page_url", type="string", nullable=true),
 *      @OA\Property(property="path", type="string"),
 *      @OA\Property(property="per_page", type="integer"),
 *      @OA\Property(property="prev_page_url", type="string", nullable=true),
 *      @OA\Property(property="to", type="integer"),
 *      @OA\Property(property="total", type="integer")
 * )
 */
class ExpenseRequestPaginatedResponse {}
