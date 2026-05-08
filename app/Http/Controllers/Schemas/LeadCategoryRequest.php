<?php

namespace App\Http\Controllers\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="LeadCategoryRequest",
 *     description="Lead Category request body",
 *     required={"name"}
 * )
 */
class LeadCategoryRequest
{
    /**
     * @OA\Property(
     *     title="Name",
     *     description="Name of the lead category",
     *     example="Hot Lead",
     *     maxLength=255
     * )
     *
     * @var string
     */
    public $name;
}