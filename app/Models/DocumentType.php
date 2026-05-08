<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="DocumentType",
 *     title="Document Type",
 *     description="Document Type model schema",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="NOC"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-11-11T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-11-11T10:00:00Z")
 * )
 */
class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function documentRequests()
    {
        return $this->hasMany(DocumentRequest::class);
    }
}
