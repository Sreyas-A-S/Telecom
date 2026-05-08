<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = ['name'];

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_zone')->withPivot('relationship_type');
    }
}
