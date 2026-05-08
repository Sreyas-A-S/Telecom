<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="ProductModel",
 *     description="Product Model model",
 *     @OA\Xml(name="ProductModel")
 * )
 * 
 * @property int $id
 * @property int $product_id
 * @property string $name
 * @property string|null $description
 * @property float|null $price
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class ProductModel extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'price',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function modelSeries()
    {
        return $this->hasMany(ModelSeries::class);
    }

    public function model_series()
    {
        return $this->modelSeries();
    }

    public function parts()
    {
        return $this->belongsToMany(Part::class, 'part_product_model');
    }

    protected static function booted()
    {
        static::deleting(function ($model) {
            $model->modelSeries()->delete();
        });
    }
}
