<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'visit_id',
        'latitude',
        'longitude',
        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
