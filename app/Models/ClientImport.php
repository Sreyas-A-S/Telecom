<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientImport extends Model
{
    use HasFactory;

    protected $table = 'client_imports';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'file_name',
        'type',
    ];

    public function clients()
    {
        return $this->hasMany(Client::class, 'import_id');
    }

    public function clientProducts()
    {
        return $this->hasMany(ClientProduct::class, 'import_id', 'id');
    }

    public function updatedClientProducts()
    {
        return $this->hasMany(ClientProduct::class, 'update_import_id', 'id');
    }

    public function leads()
    {
        return $this->hasMany(Lead::class, 'import_id');
    }
}
