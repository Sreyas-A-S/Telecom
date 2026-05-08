<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['role', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles')->withPivot('is_active')->withTimestamps();
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }
}
