<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DealershipSetting extends Model
{
    protected $table = 'dealership_setting';
    protected $fillable = ['dealership_id', 'setting_id', 'enabled'];
}