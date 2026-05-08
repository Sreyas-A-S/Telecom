<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="UserGpsTrace",
 *     title="User GPS Trace",
 *     description="User GPS Trace model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="ID of the GPS trace",
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         description="ID of the user who recorded the trace"
 *     ),
 *     @OA\Property(
 *         property="client_id",
 *         type="integer",
 *         description="ID of the client associated with the trace (nullable)",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="task_id",
 *         type="integer",
 *         description="ID of the task associated with the trace (nullable)",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="latitude",
 *         type="number",
 *         format="float",
 *         description="Latitude of the GPS trace"
 *     ),
 *     @OA\Property(
 *         property="longitude",
 *         type="number",
 *         format="float",
 *         description="Longitude of the GPS trace"
 *     ),
 *     @OA\Property(
 *         property="recorded_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the GPS trace was recorded"
 *     ),
 *     @OA\Property(
 *         property="image_path",
 *         type="string",
 *         description="Path to the image associated with the trace (nullable)",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="remarks",
 *         type="string",
 *         description="Remarks about the trace (nullable)",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="vehicle_type",
 *         type="string",
 *         description="Type of vehicle used (nullable)",
 *         nullable=true
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the record was created",
 *         readOnly=true
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="Timestamp when the record was last updated",
 *         readOnly=true
 *     )
 * )
 */
class UserGpsTrace extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'user_id',
        'task_id',
        'client_id',
        'latitude',
        'longitude',
        'recorded_at',
        'remarks',
        'image_path',
        'image_latitude',
        'image_longitude',
        'status',
        'vehicle_type',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($trace) {
            if (
                !is_numeric($trace->latitude) ||
                !is_numeric($trace->longitude) ||
                ($trace->latitude == 0 && $trace->longitude == 0)
            ) {
                return false; // Prevents the model from being created
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
