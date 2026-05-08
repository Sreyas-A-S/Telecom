<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Lead",
 *     description="Lead model",
 *     @OA\Xml(name="Lead")
 * )
 */
class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'salutation',
        'name',
        'company',
        'email',
        'phone_number',
        'alternate_contact_number',
        'location',
        'lead_source_id',
        'lead_category_id',
        'lead_value',
        'allow_follow_up',
        'status',
        'chance_of_success',
        'product_id',
        'product_model_id',
        'machine_serial_number',
        'quantity',
        'financier',
        'type',
        'login_status',
        'stage',
        'billing',
        'remarks',
        'dealership_id',
        'user_id',
        'client_id',
        'employee_id',
        'model_series_id',
        'doc',
        'engine_model',
        'engine_serial_number',
        'latitude',
        'longitude',
        'import_id',
    ];

    protected $casts = [
        'allow_follow_up' => 'boolean',
        'billing' => 'date',
    ];

    public function agent()
    {
        return $this->morphTo();
    }

    public function leadSource()
    {
        return $this->belongsTo(LeadSource::class);
    }

    public function leadCategory()
    {
        return $this->belongsTo(LeadCategory::class);
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

    public function followups()
    {
        return $this->hasMany(Followup::class);
    }

    public function dealership()
    {
        return $this->belongsTo(Dealership::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function items()
    {
        return $this->hasMany(LeadItem::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function lossOrders()
    {
        return $this->hasMany(LossOrder::class);
    }

    public function syncItems($items)
    {
        if (empty($items)) return;

        $this->items()->delete();

        foreach ($items as $item) {
            $product = null;
            if (isset($item['product_name'])) {
                $product = Product::firstOrCreate(['name' => $item['product_name']]);
            } elseif (isset($item['product_id'])) {
                $product = Product::find($item['product_id']);
            }

            if (!$product) continue;

            $productModelId = $item['product_model_id'] ?? null;
            if (isset($item['product_model_name']) && $item['product_model_name']) {
                $productModel = ProductModel::firstOrCreate(
                    ['name' => $item['product_model_name'], 'product_id' => $product->id],
                    ['description' => '']
                );
                $productModelId = $productModel->id;
            }

            $modelSeriesId = $item['model_series_id'] ?? null;
            if (isset($item['model_series_name']) && $item['model_series_name'] && $productModelId) {
                $modelSeries = ModelSeries::firstOrCreate(
                    ['name' => $item['model_series_name'], 'product_model_id' => $productModelId]
                );
                $modelSeriesId = $modelSeries->id;
            }

            $this->items()->create([
                'product_id' => $product->id,
                'product_model_id' => $productModelId,
                'model_series_id' => $modelSeriesId,
                'machine_serial_number' => $item['machine_serial_number'] ?? null,
                'engine_serial_number' => $item['engine_serial_number'] ?? null,
                'quantity' => $item['quantity'] ?? 1,
                'price' => $item['price'] ?? null,
            ]);
        }
    }
}
