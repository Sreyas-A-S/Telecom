<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LossOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'lead_id',
        'month',
        'dealership_id',
        'product_name',
        'tonnage',
        'product_model_name',
        'model_series_name',
        'customer',
        'segment',
        'application',
        'financier',
        'district',
        'category',
        'participation',
        'reasons_for_loss',
        'remarks',
        'engineer_name',
        'selected_dealership_id',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function dealership()
    {
        return $this->belongsTo(Dealership::class);
    }
}
