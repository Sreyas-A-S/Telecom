<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentRequest extends Model
{
    protected $fillable = [
        'user_id',
        'remarks',
        'document_type_id',
        'status',
        'requested_date',
        'forwarded_to_employee_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function forwardedToEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'forwarded_to_employee_id');
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }
}