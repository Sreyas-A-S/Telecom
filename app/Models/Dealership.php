<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Dealership",
 *     description="Dealership model",
 *     @OA\Xml(name="Dealership")
 * )
 */
class Dealership extends Model
{
    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID",
     *     format="int64",
     *     readOnly=true
     * )
     *
     * @var int
     */
    private int $id;

    /**
     * @OA\Property(
     *     title="Name",
     *     description="Name of the dealership",
     *     example="Example Dealership"
     * )
     *
     * @var string
     */
    protected $name;

    /**
     * @OA\Property(
     *     title="Email",
     *     description="Email of the dealership",
     *     example="dealership@example.com"
     * )
     *
     * @var string
     */
    protected $email;

    /**
     * @OA\Property(
     *     title="Phone",
     *     description="Phone number of the dealership",
     *     example="+1234567890"
     * )
     *
     * @var string
     */
    protected $phone;

    /**
     * @OA\Property(
     *     title="Address",
     *     description="Address of the dealership",
     *     example="123 Main St"
     * )
     *
     * @var string
     */
    protected $address;

    /**
     * @OA\Property(
     *     title="City",
     *     description="City of the dealership",
     *     example="Anytown"
     * )
     *
     * @var string
     */
    protected $city;

    /**
     * @OA\Property(
     *     title="State",
     *     description="State of the dealership",
     *     example="CA"
     * )
     *
     * @var string
     */
    protected $state;

    /**
     * @OA\Property(
     *     title="Zip",
     *     description="Zip code of the dealership",
     *     example="90210"
     * )
     *
     * @var string
     */
    protected $zip;

    /**
     * @OA\Property(
     *     title="Country",
     *     description="Country of the dealership",
     *     example="USA"
     * )
     *
     * @var string
     */
    protected $country;

    /**
     * @OA\Property(
     *     title="Status",
     *     description="Status of the dealership (active or inactive)",
     *     example="active",
     *     enum={"active", "inactive"}
     * )
     *
     * @var string
     */
    protected $status;

    /**
     * @OA\Property(
     *     title="Created At",
     *     description="Date and time of creation",
     *     format="datetime",
     *     type="string",
     *     readOnly=true
     * )
     *
     * @var string
     */
    private string $created_at;

    /**
     * @OA\Property(
     *     title="Updated At",
     *     description="Date and time of last update",
     *     format="datetime",
     *     type="string",
     *     readOnly=true
     * )
     *
     * @var string
     */
    private string $updated_at;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'brand',
        'status',
    ];


    /**
     * Get the models for the dealership.
     */
    public function models()
    {
        return $this->hasMany(Model::class);
    }
}
