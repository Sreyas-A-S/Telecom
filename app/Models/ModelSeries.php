<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModelSeries extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'product_model_id', 'price'];

    public function productModel()
    {
        return $this->belongsTo(ProductModel::class);
    }

    public function product_model()
    {
        return $this->productModel();
    }

    public function parts()
    {
        return $this->belongsToMany(Part::class, 'part_model_series');
    }
}
