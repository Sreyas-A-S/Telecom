<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Tax;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductsImport implements ToModel, WithHeadingRow, WithChunkReading, WithEvents, WithCalculatedFormulas
{
    use RegistersEventListeners;

    private $import_id;
    private $totalRows;
    private $processedRows = 0;

    public function __construct($import_id)
    {
        $this->import_id = $import_id;
        // Initialize progress in cache
        Cache::put('product_import_progress:' . $this->import_id, [
            'total_rows' => 0,
            'processed_rows' => 0,
            'percentage' => 0,
            'status' => 'pending',
            'results' => []
        ], now()->addHours(2));
    }

    public function model(array $row)
    {
        $this->processedRows++;
        if ($this->processedRows === 1) {
            Log::info('Import Row 1 Data: ' . json_encode($row));
        }
        $progress = Cache::get('product_import_progress:' . $this->import_id);

        try {
            $rowData = $row;
            $rowValues = array_values($row);

            // Mapping variations for common headers
            $brandName = $row['machine_name'] ?? $row['dealership'] ?? $row['machine'] ?? $row['brand_category'] ?? $row['brand'] ?? $row['product_name'] ?? $row['machine_brand'] ?? $row['manufacturer'] ?? $row['make'] ?? $rowValues[1] ?? null;
            $machineModelName = $row['machine_model'] ?? $row['model'] ?? $row['product_model'] ?? $row['model_name'] ?? $row['variant'] ?? $row['equipment_model'] ?? $rowValues[2] ?? null;

            // Handle Category with more fallbacks
            $categoryName = $row['category'] ?? $row['product_category'] ?? $row['machine_category'] ?? $row['type'] ?? $row['machine_type'] ?? $rowValues[8] ?? null;
            
            // Skip empty rows if all identifier fields are missing
            if (empty($brandName) && empty($machineModelName) && empty($categoryName)) {
                Log::info('Skipping empty row ' . $this->processedRows);
                return null;
            }

            $name = !empty($brandName) ? trim($brandName) : (!empty($machineModelName) ? trim($machineModelName) : 'Default Machine');
            $machineModelName = !empty($machineModelName) ? trim($machineModelName) : null;
            $warnings = [];

            // Handle Category
            $category = null;
            if (!empty($categoryName)) {
                $category = Category::firstOrCreate(['name' => trim($categoryName)]);
            } else {
                $warnings[] = "Category not specified";
            }

            // Handle SubCategory - Fix: only use name for lookup since name is unique in DB
            $subCategoryValue = $row['sub_category'] ?? $row['product_sub_category'] ?? $row['machine_sub_category'] ?? $row['sub_type'] ?? $rowValues[9] ?? null;
            $subCategory = null;
            if (!empty($subCategoryValue)) {
                $subCategoryName = trim($subCategoryValue);
                $subCategory = SubCategory::where('name', $subCategoryName)->first();
                if (!$subCategory && $category) {
                    $subCategory = SubCategory::create([
                        'name' => $subCategoryName,
                        'category_id' => $category->id
                    ]);
                }
            }

            // Handle Tax
            $tax = null;
            if (!empty($row['tax'])) {
                $taxName = trim($row['tax']);
                $tax = Tax::where('name', $taxName)->first();
                if (!$tax) {
                    // Try to extract numeric rate if name looks like "GST 18%"
                    $rate = 0;
                    if (preg_match('/(\d+)/', $taxName, $matches)) {
                        $rate = (float) $matches[1];
                    }
                    $tax = Tax::create(['name' => $taxName, 'rate' => $rate]);
                }
            } else {
                $warnings[] = "Tax info not specified";
            }

            // Combine HSN and SAC if both are provided separately, or use the combined field if available
            $hsn = $row['hsn'] ?? $row['hsn_code'] ?? $rowValues[4] ?? null;
            $sac = $row['sac'] ?? $row['sac_code'] ?? $rowValues[5] ?? null;
            
            if ($hsn && $sac) {
                $hsn_sac = $hsn . ' / ' . $sac;
            } else {
                $hsn_sac = $hsn ?? $sac ?? $row['hsn_sac'] ?? $row['hsn_sac_code'] ?? null;
            }

            if (empty($hsn_sac)) {
                $warnings[] = "HSN/SAC missing";
            }

            $unit_type = $row['unit_type'] ?? $row['unit'] ?? $rowValues[7] ?? 'PCS';
            if (empty($row['unit_type']) && empty($row['unit']) && empty($rowValues[7])) {
                $warnings[] = "Unit Type defaulted to PCS";
            }

            $brand = $row['brand_category'] ?? $row['brand'] ?? $rowValues[11] ?? null;

            // Extract and clean price
            $priceValue = $row['price'] ?? $row['unit_price'] ?? $row['machine_price'] ?? $row['rate'] ?? $rowValues[3] ?? 0;

            if (is_string($priceValue)) {
                // Remove everything except numbers and decimal point
                $priceValue = preg_replace('/[^0-9.]/', '', $priceValue);
            }
            $priceValue = (float) ($priceValue ?? 0);

            // Find or create the Product (Brand/Machine) 
            // Unique by BOTH Name AND Category to allow same brand in different categories
            $product = Product::where('name', $name)
                ->where('category_id', $category ? $category->id : null)
                ->first();

            $description = $row['description'] ?? $row['material_description'] ?? $rowValues[6] ?? null;

            $productData = [
                'hsn_sac' => $hsn_sac,
                'description' => $description,
                'unit_type' => $unit_type,
                'category_id' => $category ? $category->id : null,
                'sub_category_id' => $subCategory ? $subCategory->id : null,
                'brand' => !empty($brand) ? trim($brand) : null,
                'tax_id' => $tax ? $tax->id : null,
                'import_id' => $this->import_id,
            ];

            if ($product) {
                Log::info('Updating existing product ID ' . $product->id . ' for row ' . $this->processedRows . ' (' . $name . ')');
                $product->update($productData);
            } else {
                Log::info('Creating new product for row ' . $this->processedRows . ' (' . $name . ')');
                $product = Product::create(array_merge(['name' => $name], $productData));
            }

            // Handle Machine Model (ProductModel) - Store the price and description here
            if (!empty($machineModelName)) {
                $productModel = \App\Models\ProductModel::updateOrCreate([
                    'name' => $machineModelName,
                    'product_id' => $product->id
                ], [
                    'price' => $priceValue,
                    'description' => $description
                ]);
            }

            $status = 'success';
            $reason = 'Successfully processed.';

            $progress['results'][] = [
                'row_number' => $this->processedRows,
                'product_name' => $name,
                'price' => $priceValue,
                'hsn_sac' => $hsn_sac,
                'status' => $status,
                'reason' => $reason,
                'warnings' => $warnings
            ];

            return $product;
        } catch (\Exception $e) {
            Log::error('Error processing row ' . $this->processedRows . ' for product import ID ' . $this->import_id . ': ' . $e->getMessage());
            $progress['results'][] = [
                'row_number' => $this->processedRows,
                'product_name' => $row['dealership'] ?? $row['machine'] ?? $row['name'] ?? 'Unknown',
                'status' => 'failed',
                'reason' => $e->getMessage(),
            ];
            return null;
        } finally {
            if ($progress && $this->totalRows > 0) {
                $progress['processed_rows'] = $this->processedRows;
                $progress['percentage'] = min(100, round(($this->processedRows / $this->totalRows) * 100));
            }
            Cache::put('product_import_progress:' . $this->import_id, $progress, now()->addHours(2));
        }
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function chunkSize(): int
    {   
        return 1000;
    }

    public static function afterImport(AfterImport $event)
    {
        $import = $event->getConcernable();
        $progress = Cache::get('product_import_progress:' . $import->import_id);
        if ($progress) {
            $progress['status'] = 'completed';
            $progress['percentage'] = 100;
            Cache::put('product_import_progress:' . $import->import_id, $progress, now()->addHours(2));
        }
    }

    public function afterSheet(AfterSheet $event)
    {
        $this->totalRows = $event->getDelegate()->getHighestRow() - 1; // Subtract header row
        $progress = Cache::get('product_import_progress:' . $this->import_id);
        if ($progress) {
            $progress['total_rows'] = $this->totalRows;
            Cache::put('product_import_progress:' . $this->import_id, $progress, now()->addHours(2));
        }
    }
}
