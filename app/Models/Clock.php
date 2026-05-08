<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clock extends Model
{
    protected $fillable = [
        'employee_id',
        'clock_in_time',
        'clock_out_time',
        'remarks',
        'clock_in_latitude',
        'clock_in_longitude',
        'clock_out_latitude',
        'clock_out_longitude',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
