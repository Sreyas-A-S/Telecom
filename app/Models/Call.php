<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Call extends Model
{
    protected $fillable = [
        'caller_user_id',
        'receiver_user_id',
        'external_number',
        'status',
        'direction',
        'start_time',
        'end_time',
        'recording_url',
        'call_sid',
        'channel',
        'lead_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function caller()
    {
        return $this->belongsTo(User::class, 'caller_user_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_user_id');
    }
}
