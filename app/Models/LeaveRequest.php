<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type',
        'start_date',
        'end_date',
        'reason',
        'status',
        'duration',
        'attachment',
        'forwarded_to_employee_id', // New field for forwarded employee
        'is_compensatory',
        'compensatory_date',
    ];
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Add new relationship for the forwarded employee
    public function forwardedToEmployee()
    {
        return $this->belongsTo(User::class, 'forwarded_to_employee_id');
    }
}
