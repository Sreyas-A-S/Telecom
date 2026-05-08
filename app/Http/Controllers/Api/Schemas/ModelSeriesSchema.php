<?php

namespace App\Http\Controllers\Api\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ModelSeries",
 *     title="Model Series",
 *     description="Model Series object",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the model series"
 *     ),
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         description="Name of the model series"
 *     ),
 *     @OA\Property(
 *         property="product_model_id",
 *         type="integer",
 *         format="int64",
 *         description="ID of the associated product model"
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
class ModelSeriesSchema
{
    // This class is just a container for Swagger Schema annotations.
}