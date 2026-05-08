<?php

namespace App\Http\Controllers\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="ProductModelRequest",
 *     description="Product Model request body",
 *     required={"name", "product_id"}
 * )
 */
class ProductModelRequest
{
    /**
     * @OA\Property(
     *     title="Product ID",
     *     description="ID of the associated product",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    public $product_id;

    /**
     * @OA\Property(
     *     title="Name",
     *     description="Name of the product model",
     *     example="Model X",
     *     maxLength=255
     * )
     *
     * @var string
     */
    public $name;
}