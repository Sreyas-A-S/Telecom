<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{

    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'user_type',
        'employee_id',
        'profile_pic',
        'player_id',
        'designation',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

    protected $appends = ['role_id'];

    public function getRoleIdAttribute()
    {
        if ($this->user_type === 'employee' && $this->employee) {
            return $this->employee->role_id;
        }
        return null;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')->withPivot('is_active')->withTimestamps();
    }

    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id', 'id');
    }

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
        return [
            'role_id' => $this->role_id,
        ];
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function gpsTraces()
    {
        return $this->hasMany(UserGpsTrace::class);
    }

    public function performanceReviews()
    {
        return $this->hasMany(PerformanceReview::class, 'employee_id');
    }

    public function lastPerformanceReview()
    {
        return $this->hasOne(PerformanceReview::class, 'employee_id')->latestOfMany('review_date');
    }
}
