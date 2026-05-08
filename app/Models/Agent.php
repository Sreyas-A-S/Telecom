<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;
use OpenApi\Annotations as OA;

if (!class_exists('App\Models\Agent')) {
    class Agent extends Model
    {
        use HasFactory;

        protected $fillable = [
            'name',
            'email',
            'phone_number',
            'status',
            'employee_id',
            'is_employee',
            'user_id',
            'dealership_id',
            'zone_id',
        ];

        public function employee()
        {
            return $this->belongsTo(Employee::class);
        }

        public function leads()
        {
            return $this->hasMany(Lead::class, 'agent_id');
        }
    }
}
