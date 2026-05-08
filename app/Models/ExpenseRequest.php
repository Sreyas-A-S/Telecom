<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseRequest extends Model
{
    protected $fillable = [
        'user_id',
        'reporting_to',
        'expense_type',
        'amount',
        'approved_amount',
        'date',
        'status',
        'image',
        'description',
        'forwarded_to_employee_id',
    ];

    protected $casts = [
        // 'image' => 'array', // Removed to handle mixed data in accessor
    ];

    public function getImageAttribute($value)
    {
        if (is_null($value)) {
            return [];
        }

        // Try to decode the main value
        $decoded = json_decode($value, true);

        // If main decode failed, it acts as a plain string path
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Clear the error state so it doesn't persist to JsonResponse
            json_decode('{}');
            return [$value];
        }

        // If not an array (e.g. encoded string "path"), wrap it
        if (!is_array($decoded)) {
            return [$decoded];
        }

        // Handle potential double-encoded items within the array
        $result = array_map(function ($item) {
            if (is_string($item)) {
                $innerDecoded = json_decode($item, true);
                if (json_last_error() === JSON_ERROR_NONE && is_string($innerDecoded)) {
                    return $innerDecoded;
                }
                // Clear error from failed inner decode
                json_decode('{}');
            }
            return $item;
        }, $decoded);

        return $result;
    }

    /**
     * Get the user that owns the ExpenseRequest
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employee that owns the ExpenseRequest
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reportingTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporting_to');
    }

    /**
     * Get the user to whom the ExpenseRequest was forwarded.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function forwardedToEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'forwarded_to_employee_id');
    }
}
