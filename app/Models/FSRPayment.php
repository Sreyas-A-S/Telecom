<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="FSRPayment",
 *     title="FSR Payment",
 *     description="FSR Payment installment model",
 *     @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 *     @OA\Property(property="fsr_report_id", type="integer", description="ID of the associated FSR report", example="1"),
 *     @OA\Property(property="amount", type="number", format="float", example="500.00"),
 *     @OA\Property(property="payment_mode", type="string", enum={"cash", "online", "cheque", "other"}, example="cash"),
 *     @OA\Property(property="collected_by_user_id", type="integer", description="ID of the user who collected the payment", example="1"),
 *     @OA\Property(property="collected_by", ref="#/components/schemas/User", description="User who collected the payment"),
 *     @OA\Property(property="collected_at", type="string", format="date-time", example="2023-10-27T10:00:00Z"),
 *     @OA\Property(property="remarks", type="string", nullable=true, example="First installment"),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly="true"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly="true")
 * )
 */
class FSRPayment extends Model
{
    use HasFactory;

    protected $table = 'fsr_payments';

    protected $fillable = [
        'fsr_report_id',
        'amount',
        'payment_mode',
        'collected_by_user_id',
        'collected_at',
        'remarks',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
        'amount' => 'float',
    ];

    public function fsrReport()
    {
        return $this->belongsTo(FSRReport::class, 'fsr_report_id');
    }

    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by_user_id');
    }
}
