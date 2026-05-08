<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadItem extends Model
{
    protected $fillable = [
        'lead_id',
        'product_id',
        'product_model_id',
        'model_series_id',
        'machine_serial_number',
        'engine_serial_number',
        'engine_model',
        'quantity',
        'price',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productModel()
    {
        return $this->belongsTo(ProductModel::class);
    }

    public function modelSeries()
    {
        return $this->belongsTo(ModelSeries::class);
    }
}
