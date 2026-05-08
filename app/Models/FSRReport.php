<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory; // Added missing import
use App\Models\Task;
use App\Models\User;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="FSRReport",
 *     title="FSR Report",
 *     description="FSR Report model",
 *     @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 *     @OA\Property(property="task_id", type="integer", description="ID of the associated task", example="1"),
 *     @OA\Property(property="on_site_assessment", type="string", nullable=true, example="Assessment details"),
 *     @OA\Property(property="analysis_of_cause", type="string", nullable=true, example="Cause analysis details"),
 *     @OA\Property(property="actions_taken", type="string", nullable=true, example="Actions taken details"),
 *     @OA\Property(property="submitted_by_user_id", type="integer", description="ID of the user who submitted the report", example="1"),
 *     @OA\Property(property="submitted_by", ref="#/components/schemas/User", description="User who submitted the report"),
 *     @OA\Property(property="task", ref="#/components/schemas/Task", description="Associated task details"),
 *     @OA\Property(property="payment_status", type="string", enum={"pending", "paid"}, example="pending"),
 *     @OA\Property(property="status", type="string", enum={"pending", "approved", "rejected"}, example="pending"),
 *     @OA\Property(property="images", type="array", @OA\Items(type="string"), nullable=true, description="Array of image paths"),
 *     @OA\Property(property="image_urls", type="array", @OA\Items(type="string"), nullable=true, description="Array of image full URLs"),
 *     @OA\Property(property="part_quotations", type="array", @OA\Items(ref="#/components/schemas/FSRQuotation"), description="List of part quotations"),
 *     @OA\Property(property="payment_history", type="array", @OA\Items(ref="#/components/schemas/FSRPayment"), description="List of payment installments"),
 *     @OA\Property(property="created_at", type="string", format="date-time", readOnly="true", example="2023-10-27T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", readOnly="true", example="2023-10-27T10:00:00Z")
 * )
 */
class FSRReport extends Model
{
    use HasFactory;

    //declare table name
    protected $table = 'fsr_reports';

    protected $fillable = [
        'task_id',
        'on_site_assessment',
        'analysis_of_cause',
        'actions_taken',
        'submitted_by_user_id',
        'payment_status',
        'status',
        'images',
    ];

    protected $casts = [
        'payment_status' => 'string',
        'images' => 'array',
        // No longer casting parts_required as it's moved to a separate table
    ];

    protected $appends = ['image_urls'];

    public function getImageUrlsAttribute()
    {
        if (empty($this->images) || !is_array($this->images)) {
            return [];
        }

        return array_map(function ($path) {
            return asset('storage/' . $path);
        }, $this->images);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by_user_id');
    }

    public function partQuotations()
    {
        return $this->hasMany(FSRQuotation::class, 'fsr_id');
    }

    public function paymentHistory()
    {
        return $this->hasMany(FSRPayment::class, 'fsr_report_id');
    }
}
