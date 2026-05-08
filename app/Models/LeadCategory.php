<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="LeadCategory",
 *     description="Lead Category model",
 *     @OA\Xml(name="LeadCategory")
 * )
 */
class LeadCategory extends Model
{
    use HasFactory;

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
     *     description="Name of the lead category",
     *     example="Hot Lead"
     * )
     *
     * @var string
     */
    protected $name;

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

    protected $fillable = ['name'];

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}
