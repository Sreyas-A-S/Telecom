<?php

namespace App\Imports;

use App\Models\Part;
use App\Models\Tax;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

class PartsImport implements OnEachRow, WithHeadingRow, WithChunkReading, WithEvents, WithCalculatedFormulas
{
    use RegistersEventListeners;

    private $import_id;
    private $totalRows;
    private $processedRows = 0;
    private $successCount = 0;
    private $failedCount = 0;
    private $errorResults = [];
    private $productModelCache = [];
    private $taxCache = [];
    private $dealershipCache = [];

    public function __construct($import_id)
    {
        $this->import_id = $import_id;
    }

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();
        $this->processedRows++;
        
        // Handle potential indexing issues by mapping slugs to indexes if needed
        // Sl No (0), Machine Model (1), Part Number (2), Material Description (3), 
        // Unit Price (4), HSN (5), Dealer (6), Bin (7), Stock Quantity (8), Tax (9)
        $partNumber = isset($rowData['part_number']) ? trim($rowData['part_number']) : (isset($rowData['part_no']) ? trim($rowData['part_no']) : (isset($rowData[2]) ? trim($rowData[2]) : null));
        $taxInputValue = isset($rowData['tax']) ? trim($rowData['tax']) : (isset($rowData['tax_percentage']) ? trim($rowData['tax_percentage']) : (isset($rowData[9]) ? trim($rowData[9]) : null));
        
        $dealerNameFromExcel = null;
        $dealerKeys = ['dealer', 'dealer_name', 'distributor', 'vendor'];
        foreach ($dealerKeys as $key) {
            if (isset($rowData[$key]) && !empty(trim($rowData[$key]))) {
                $dealerNameFromExcel = trim($rowData[$key]);
                break;
            }
        }
        if (!$dealerNameFromExcel && isset($rowData[6])) $dealerNameFromExcel = trim($rowData[6]);

        $materialDesc = isset($rowData['material_description']) ? $rowData['material_description'] : (isset($rowData['description']) ? $rowData['description'] : (isset($rowData[3]) ? $rowData[3] : null));
        
        $unitPrice = isset($rowData['unit_price']) ? $rowData['unit_price'] : (isset($rowData['price']) ? $rowData['price'] : (isset($rowData['rate']) ? $rowData['rate'] : (isset($rowData[4]) ? $rowData[4] : 0)));
        // Clean price
        if (is_string($unitPrice)) {
            $unitPrice = preg_replace('/[^0-9.]/', '', $unitPrice);
        }
        $unitPrice = (float)($unitPrice ?: 0);

        $hsn = null;
        $hsnKeys = ['hsn', 'hsn_code', 'hsn_sac', 'hsn_sac_code', 'hsn_no'];
        foreach ($hsnKeys as $key) {
            if (isset($rowData[$key]) && !empty(trim($rowData[$key]))) {
                $hsn = trim($rowData[$key]);
                break;
            }
        }
        if (!$hsn && isset($rowData[5])) $hsn = trim($rowData[5]);

        $bin = isset($rowData['bin']) ? $rowData['bin'] : (isset($rowData['location']) ? $rowData['location'] : (isset($rowData['bin_location']) ? $rowData['bin_location'] : (isset($rowData[7]) ? $rowData[7] : null)));
        
        $stockQty = isset($rowData['stock_quantity']) ? $rowData['stock_quantity'] : (isset($rowData['qty']) ? $rowData['qty'] : (isset($rowData['quantity']) ? $rowData['quantity'] : (isset($rowData[8]) ? $rowData[8] : 0)));
        // Clean stock
        if (is_string($stockQty)) {
            $stockQty = preg_replace('/[^0-9.]/', '', $stockQty);
        }
        $stockQty = (float)($stockQty ?: 0);

        $machineModelName = null;
        $machineKeys = ['machine_model', 'machine', 'model', 'equipment'];
        foreach ($machineKeys as $key) {
            if (isset($rowData[$key]) && !empty(trim($rowData[$key]))) {
                $machineModelName = trim($rowData[$key]);
                break;
            }
        }
        if (!$machineModelName && isset($rowData[1])) $machineModelName = trim($rowData[1]);

        try {
            // Skip empty rows
            if (empty($partNumber)) {
                return;
            }

            $warnings = [];

            // Handle Tax lookup
            $taxId = null;
            if ($taxInputValue) {
                if (!isset($this->taxCache[$taxInputValue])) {
                    preg_match('/[0-9]*\.?[0-9]+/', $taxInputValue, $matches);
                    if (!empty($matches)) {
                        $rate = (float) $matches[0];
                        $potentialRates = [$rate];
                        if ($rate > 0 && $rate < 1) $potentialRates[] = $rate * 100;

                        $tax = null;
                        foreach ($potentialRates as $r) {
                            $tax = Tax::where('rate', number_format($r, 2, '.', ''))->first();
                            if ($tax) break;
                        }
                        
                        if (!$tax) {
                            $tax = Tax::firstOrCreate(['name' => $taxInputValue], ['rate' => $rate]);
                        }
                        $this->taxCache[$taxInputValue] = $tax->id;
                    } else {
                        $this->taxCache[$taxInputValue] = false;
                    }
                }
                $taxId = $this->taxCache[$taxInputValue] ?: null;
            }

            // Handle Dealership lookup
            $dealershipId = null;
            if ($dealerNameFromExcel) {
                $cacheKey = strtolower($dealerNameFromExcel);
                if (!isset($this->dealershipCache[$cacheKey])) {
                    $dealership = \App\Models\Dealership::where('name', 'LIKE', $dealerNameFromExcel)->first();
                    $this->dealershipCache[$cacheKey] = $dealership ? $dealership->id : false;
                }
                $dealershipId = $this->dealershipCache[$cacheKey] ?: null;
            }

            Log::debug("Inserting Part: {$partNumber}", [
                'tax_input' => $taxInputValue,
                'tax_id' => $taxId,
                'dealer_input' => $dealerNameFromExcel,
                'machine_input' => $machineModelName ?: $dealerNameFromExcel,
                'dealership_id' => $dealershipId
            ]);

            $part = Part::create([
                'part_number' => $partNumber,
                'material_description' => $materialDesc,
                'unit_price' => $unitPrice,
                'hsn' => $hsn,
                'machine' => $machineModelName ?: $dealerNameFromExcel,
                'dealer' => $dealerNameFromExcel,
                'bin' => $bin,
                'stock_quantity' => $stockQty,
                'is_active' => true,
                'tax_id' => $taxId,
                'dealership_id' => $dealershipId,
                'import_id' => $this->import_id,
            ]);

            // Link to Machine Model if provided with local cache
            if ($machineModelName) {
                if (!isset($this->productModelCache[$machineModelName])) {
                    $productModel = \App\Models\ProductModel::where('name', $machineModelName)->first();
                    $this->productModelCache[$machineModelName] = $productModel ? $productModel->id : false;
                }

                $productModelId = $this->productModelCache[$machineModelName];
                if ($productModelId) {
                    $part->productModels()->syncWithoutDetaching([$productModelId]);
                    Log::debug("Linked Part {$partNumber} to Machine Model: {$machineModelName} (ID: {$productModelId})");
                }
            }

            $this->successCount++;
            $this->errorResults[] = [
                'row_number' => $this->processedRows,
                'part_name' => $partNumber,
                'hsn' => $hsn,
                'unit_price' => $unitPrice,
                'machine' => $machineModelName ?: $dealerNameFromExcel,
                'dealer' => $dealerNameFromExcel,
                'status' => 'success',
                'reason' => 'Imported successfully',
                'warnings' => []
            ];

        } catch (\Exception $e) {
            Log::error('Error processing row ' . $this->processedRows . ' for part import ID ' . $this->import_id . ': ' . $e->getMessage());
            $this->failedCount++;
            $this->errorResults[] = [
                'row_number' => $this->processedRows,
                'part_name' => $rowData['part_number'] ?? 'Unknown',
                'hsn' => $hsn ?? null,
                'unit_price' => $unitPrice ?? 0,
                'machine' => $machineModelName ?? $dealerNameFromExcel ?? null,
                'dealer' => $dealerNameFromExcel ?? null,
                'status' => 'failed',
                'reason' => $e->getMessage(),
            ];
        }

        // Periodically update cache and collect garbage to save memory
        if ($this->processedRows % 50 === 0 || $this->processedRows === $this->totalRows) {
            $this->updateProgressInCache();
        }
        
        if ($this->processedRows % 100 === 0) {
            gc_collect_cycles();
        }
    }

    private function updateProgressInCache()
    {
        $progress = Cache::get('part_import_progress:' . $this->import_id);
        if (!$progress) {
            $progress = [
                'total_rows' => $this->totalRows ?? 0,
                'results' => []
            ];
        }
        
        $progress['processed_rows'] = $this->processedRows;
        $progress['success_count'] = $this->successCount;
        $progress['failed_count'] = $this->failedCount;
        $progress['results'] = $this->errorResults; // Only store errors/warnings
        
        if ($this->totalRows > 0) {
            $progress['percentage'] = min(99, round(($this->processedRows / $this->totalRows) * 100));
        }
        
        Cache::put('part_import_progress:' . $this->import_id, $progress, now()->addHours(2));
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function chunkSize(): int
    {
        return 250;
    }

    public static function afterImport(AfterImport $event)
    {
        $import = $event->getConcernable();
        $progress = Cache::get('part_import_progress:' . $import->import_id);
        if ($progress) {
            $progress['processed_rows'] = $import->processedRows;
            $progress['success_count'] = $import->successCount;
            $progress['failed_count'] = $import->failedCount;
            $progress['results'] = $import->errorResults;
            $progress['status'] = 'completed';
            $progress['percentage'] = 100;
            Cache::put('part_import_progress:' . $import->import_id, $progress, now()->addHours(2));
        }
    }

    public function afterSheet(AfterSheet $event)
    {
        $this->totalRows = $event->getDelegate()->getHighestRow() - 1;
        $progress = Cache::get('part_import_progress:' . $this->import_id);
        if ($progress) {
            $progress['total_rows'] = $this->totalRows;
            Cache::put('part_import_progress:' . $this->import_id, $progress, now()->addHours(2));
        }
    }
}
