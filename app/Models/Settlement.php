<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_code',
        'employee_name',
        'age',
        'department',
        'head_office_branch',
        'designation',
        'date_of_joining',
        'date_of_resignation',
        'reason_for_resignation',
        'dealership_id',
    ];

    public function remarks()
    {
        return $this->hasMany(SettlementRemark::class);
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }
}
