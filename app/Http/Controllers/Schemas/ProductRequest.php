<?php

namespace App\Http\Controllers\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="ProductRequest",
 *     description="Product request body",
 *     required={"name"}
 * )
 */
class ProductRequest
{
    /**
     * @OA\Property(
     *     title="Name",
     *     description="Name of the product",
     *     example="Product A",
     *     maxLength=255
     * )
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(
     *     title="Category ID",
     *     description="ID of the product category",
     *     format="int64",
     *     nullable=true,
     *     example=1
     * )
     *
     * @var int
     */
    public $category_id;

    /**
     * @OA\Property(
     *     title="Sub Category ID",
     *     description="ID of the product sub-category",
     *     format="int64",
     *     nullable=true,
     *     example=1
     * )
     *
     * @var int
     */
    public $subcategory_id;

    /**
     * @OA\Property(
     *     title="Tax ID",
     *     description="ID of the associated tax",
     *     format="int64",
     *     nullable=true,
     *     example=1
     * )
     *
     * @var int
     */
    public $tax_id;

    /**
     * @OA\Property(
     *     title="Price",
     *     description="Price of the product",
     *     format="float",
     *     nullable=true,
     *     example=99.99
     * )
     *
     * @var float
     */
    public $price;
}