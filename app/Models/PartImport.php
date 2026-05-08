<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartImport extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'file_name'];
    protected $keyType = 'string';
    public $incrementing = false;

    public function parts()
    {
        return $this->hasMany(Part::class, 'import_id');
    }
}
