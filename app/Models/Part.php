<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Part",
 *     title="Part",
 *     description="Part model",
 *     @OA\Xml(name="Part"),
 *     @OA\Property(property="id", type="integer", readOnly="true", example=1),
 *     @OA\Property(property="material_description", type="string", example="Engine Oil"),
 *     @OA\Property(property="tax_id", type="integer", example=1),
 *     @OA\Property(property="unit_price", type="number", format="float", example=10.50),
 *     @OA\Property(property="hsn", type="string", example="8708"),
 *     @OA\Property(property="dealer", type="string", example="Dealer A"),
 *     @OA\Property(property="bin", type="string", example="A1"),
 *     @OA\Property(property="part_number", type="string", example="PN12345"),
 *     @OA\Property(property="stock_quantity", type="integer", example=100),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="dealership_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Creation timestamp", readOnly="true"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Update timestamp", readOnly="true")
 * )
 */
class Part extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_description',
        'tax_id',
        'unit_price',
        'hsn',
        'machine',
        'dealer',
        'bin',
        'part_number',
        'stock_quantity',
        'is_active',
        'dealership_id',
        'import_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'part_product');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }



    public function productModels()
    {
        return $this->belongsToMany(ProductModel::class, 'part_product_model');
    }

    public function modelSeries()
    {
        return $this->belongsToMany(ModelSeries::class, 'part_model_series');
    }

    public function packageKits()
    {
        return $this->belongsToMany(PackageKit::class, 'package_kit_part');
    }
}
