<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Product",
 *     description="Product model",
 *     @OA\Xml(name="Product")
 * )
 * 
 * @property int $id
 * @property string $name
 * @property float $price
 * @property string|null $hsn_sac
 * @property string|null $description
 * @property int|null $category_id
 * @property int|null $sub_category_id
 * @property string|null $unit_type
 * @property int|null $tax_id
 * @property string|null $brochure
 * @property string|null $import_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'hsn_sac',
        'description',
        'category_id',
        'sub_category_id',
        'brand',
        'unit_type',
        'tax_id',
        'brochure',
        'import_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function models()
    {
        return $this->hasMany(ProductModel::class);
    }

    public function parts()
    {
        return $this->belongsToMany(Part::class, 'part_product');
    }

    protected static function booted()
    {
        static::deleting(function ($product) {
            $product->models->each->delete();
        });
    }
}
