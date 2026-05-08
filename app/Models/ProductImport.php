<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImport extends Model
{
    use HasFactory;

    protected $fillable = ['id', 'file_name'];
    protected $keyType = 'string';
    public $incrementing = false;

    public function products()
    {
        return $this->hasMany(Product::class, 'import_id');
    }
}
