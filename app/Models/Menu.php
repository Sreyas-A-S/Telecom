<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['name', 'menu_group_id'];

    public function menuGroup()
    {
        return $this->belongsTo(MenuGroup::class);
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }
}
