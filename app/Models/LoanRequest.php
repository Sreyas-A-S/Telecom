<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanRequest extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'status',
        'requested_on',
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
}