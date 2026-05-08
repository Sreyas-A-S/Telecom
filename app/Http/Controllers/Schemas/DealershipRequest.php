<?php

namespace App\Http\Controllers\Schemas;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="DealershipRequest",
 *     description="Dealership request body",
 *     required={"name"}
 * )
 */
class DealershipRequest
{
    /**
     * @OA\Property(
     *     title="Name",
     *     description="Name of the dealership",
     *     example="Example Dealership",
     *     maxLength=255
     * )
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(
     *     title="Email",
     *     description="Email of the dealership",
     *     example="dealership@example.com",
     *     nullable=true
     * )
     *
     * @var string
     */
    public $email;

    /**
     * @OA\Property(
     *     title="Phone",
     *     description="Phone number of the dealership",
     *     example="+1234567890",
     *     nullable=true
     * )
     *
     * @var string
     */
    public $phone;

    /**
     * @OA\Property(
     *     title="Address",
     *     description="Address of the dealership",
     *     example="123 Main St",
     *     nullable=true
     * )
     *
     * @var string
     */
    public $address;

    /**
     * @OA\Property(
     *     title="City",
     *     description="City of the dealership",
     *     example="Anytown",
     *     nullable=true
     * )
     *
     * @var string
     */
    public $city;

    /**
     * @OA\Property(
     *     title="State",
     *     description="State of the dealership",
     *     example="CA",
     *     nullable=true
     * )
     *
     * @var string
     */
    public $state;

    /**
     * @OA\Property(
     *     title="Zip",
     *     description="Zip code of the dealership",
     *     example="90210",
     *     nullable=true
     * )
     *
     * @var string
     */
    public $zip;

    /**
     * @OA\Property(
     *     title="Country",
     *     description="Country of the dealership",
     *     example="USA",
     *     nullable=true
     * )
     *
     * @var string
     */
    public $country;

    /**
     * @OA\Property(
     *     title="Status",
     *     description="Status of the dealership (active or inactive)",
     *     example="active",
     *     enum={"active", "inactive"},
     *     nullable=true
     * )
     *
     * @var string
     */
    public $status;
}