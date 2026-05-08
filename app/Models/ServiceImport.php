<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceImport extends Model
{
    protected $table = 'service_imports';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'file_name'
    ];

    public function services()
    {
        return $this->hasMany(Service::class, 'import_id', 'id');
    }
}
