<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="FSRQuotation",
 *     title="FSR Quotation",
 *     description="FSR Quotation model",
 *     @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 *     @OA\Property(property="fsr_id", type="integer", description="ID of the associated FSR report", example="1"),
 *     @OA\Property(property="part_id", type="integer", description="ID of the associated part", example="1"),
 *     @OA\Property(property="part", ref="#/components/schemas/Part", description="Details of the associated part"),
 *     @OA\Property(property="quoted_quantity", type="integer", example="2"),
 *     @OA\Property(property="quoted_unit_price", type="number", format="float", example="150.00"),
 *     @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected"}, example="pending"),
 *     @OA\Property(property="approved_by_user_id", type="integer", nullable=true, example="1"),
 *     @OA\Property(property="approver", ref="#/components/schemas/User", description="User who approved the quotation"),
 *     @OA\Property(property="approved_at", type="string", format="date-time", nullable=true, example="2023-10-27T10:00:00Z"),
 *     @OA\Property(property="remarks", type="string", nullable=true, example="Approval remarks"),
 *     @OA\Property(property="approved_quantity", type="integer", nullable=true, example="2"),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly="true"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly="true")
 * )
 */
class FSRQuotation extends Model
{
    use HasFactory;

    protected $table = 'fsr_quotations'; // Explicitly define table name

    protected $fillable = [
        'fsr_id',
        'part_id',
        'quoted_quantity',
        'quoted_unit_price',
        'status',
        'approved_by_user_id',
        'approved_at',
        'remarks',
        'approved_quantity',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function fsrReport()
    {
        return $this->belongsTo(FSRReport::class, 'fsr_id'); // Specify foreign key
    }

    public function part()
    {
        return $this->belongsTo(Part::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
