<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'product_id',
        'product_model_id',
        'model_series_id',
        'machine_serial_number',
        'doc',
        'engine_model',
        'engine_serial_number',
        'source',
        'dealership_id',
        'import_id',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
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

    public function dealership()
    {
        return $this->belongsTo(Dealership::class);
    }
}
