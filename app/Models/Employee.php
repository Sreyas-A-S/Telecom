<?php

namespace App\Models;

use App\Models\Dealership;
use App\Models\Department;
use App\Models\Role;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tymon\JWTAuth\Contracts\JWTSubject;
use OpenApi\Annotations as OA;



/**
 * @OA\Schema(
 *     title="Employee",
 *     description="Employee model",
 *     @OA\Xml(name="Employee")
 * )
 */

if (!class_exists('App\Models\Employee')) {
    class Employee extends Authenticatable implements JWTSubject
    {
        use HasFactory;

        protected static function booted()
        {
            static::saving(function ($employee) {
                if ($employee->designation) {
                    $role = Role::firstOrCreate(
                        ['role' => $employee->designation],
                        ['is_active' => true]
                    );
                    $employee->role_id = $role->id;
                }
            });
        }

        protected $fillable = [
            'name',
            'email',
            'password',
            'profile_pic',
            'designation',
            'department_id',
            'role_id',
            'dealership_id',
            'zone_id',
            'country',
            'mobile',
            'gender',
            'joining_date',
            'dob',
            'reporting_to',
            'address',
            'employee_id',
            'is_broker',
            'is_tracking_on',
            'import_id',
            'user_id',
            'marital_status',
            'emergency_contact',
            'father_name',
            'mother_name',
            'spouse_name',
            'shirt_size',
            'tshirt_size',
            'blood_group',
            'bank_name',
            'account_number',
            'ifsc_code',
            'pf_no',
            'esi_no',
            'lwf_no',
            'aadhar_no',
            'pan_no',
            'branch',
            'status',
            'current_vehicle_type',
        ];

        /**
         * The attributes that should be hidden for serialization.
         * 
         * @var array<int, string>
         */
        protected $hidden = [
            'password',
            'remember_token',
        ];

        /**
         * The attributes that should be cast.
         * 
         * @var array<string, string>
         */
        protected $casts = [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_broker' => 'boolean',
            'task_started_time' => 'datetime',
            'status' => 'boolean',
        ];



        /**
         * Get the department that owns the employee.
         */


        /**
         * Get the identifier that will be stored in the subject claim of the JWT.
         *
         * @return mixed
         */
        public function getJWTIdentifier()
        {
            return $this->getKey();
        }

        /**
         * Return a key value array, containing any custom claims to be added to the JWT.
         *
         * @return array
         */
        public function getJWTCustomClaims()
        {
            return [];
        }

        public function department(): BelongsTo
        {
            return $this->belongsTo(Department::class);
        }

        /**
         * Get the role that owns the employee.
         */
        public function role(): BelongsTo
        {
            return $this->belongsTo(Role::class);
        }

        /**
         * Get the dealership that owns the employee.
         */
        public function dealership(): BelongsTo
        {
            return $this->belongsTo(Dealership::class);
        }

        /**
         * Get the zone that owns the employee.
         */
        public function zone(): BelongsTo
        {
            return $this->belongsTo(Zone::class);
        }

        /**
         * Get the employee this employee reports to.
         */
        public function reporter(): BelongsTo
        {
            return $this->belongsTo(Employee::class, 'reporting_to');
        }

        public function reporter2(): BelongsTo
        {
            return $this->belongsTo(Employee::class, 'reporting_to', 'user_id');
        }

        public function reporter3(): BelongsTo
        {
            return $this->belongsTo(User::class);
        }

        /**
         * Get the employees that report to this employee.
         */
        public function subordinates(): HasMany
        {
            return $this->hasMany(Employee::class, 'reporting_to', 'user_id');
        }

        public function user(): BelongsTo
        {
            return $this->belongsTo(User::class, 'user_id');
        }

        public function leads(): HasMany
        {
            return $this->hasMany(Lead::class, 'employee_id');
        }

        public function tasks(): HasMany
        {
            return $this->hasMany(Task::class, 'assigned_to', 'id');
        }

        public function employeeImports()
        {
            return $this->belongsTo(EmployeeImport::class, 'import_id', 'id');
        }

        public function agentSession(): \Illuminate\Database\Eloquent\Relations\HasOne
        {
            return $this->hasOne(AgentSession::class);
        }

        public function clocks(): HasMany
        {
            return $this->hasMany(Clock::class);
        }
    }
}
