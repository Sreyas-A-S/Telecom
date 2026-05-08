<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\Lead;
use App\Models\ClientProduct;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\ModelSeries;
use App\Models\Dealership;
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
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ClientProductsImport implements OnEachRow, WithHeadingRow, WithChunkReading, WithEvents, WithCalculatedFormulas
{
    use RegistersEventListeners;

    private $import_id;
    private $totalRows;
    private $processedRows = 0;
    private $successCount = 0;
    private $failedCount = 0;
    private $errorResults = [];
    
    private $clientCache = [];
    private $productCache = [];
    private $productModelCache = [];
    private $modelSeriesCache = [];
    private $dealershipCache = [];

    public function __construct($import_id)
    {
        $this->import_id = $import_id;
        Cache::put('client_import_progress:' . $this->import_id, [
            'total_rows' => 0,
            'processed_rows' => 0,
            'percentage' => 0,
            'status' => 'pending',
            'results' => []
        ], now()->addHours(2));
    }

    public function onRow(Row $row)
    {
        $this->processedRows++;
        $rowData = $row->toArray();

        // 1. Extract Variables (Handles both named and numeric headers)
        // Expected columns: Sl No, Phone Number, Email, Machine, Machine Model, DOC, Engine Model, Engine Serial Number
        $customerName = $rowData['client_name'] ?? $rowData['name'] ?? null;
        $phoneNumber = $rowData['phone_number'] ?? null;
        $email = $rowData['email'] ?? null;
        $machineName = $rowData['machine'] ?? null;
        $productModelName = $rowData['machine_model'] ?? null;
        $docRaw = $rowData['doc'] ?? null;
        $engineModel = $rowData['engine_model'] ?? null;
        $engineSerialNumber = $rowData['engine_serial_number'] ?? null;
        $machineSerialNumber = $rowData['machine_serial_number'] ?? null;

        // Prioritize machine_serial_number for the ModelSeries (Product Serial Number)
        $modelSeriesName = $machineSerialNumber ?? $engineSerialNumber ?? null;

        // Fallback to numeric indices if headers not found
        if (is_numeric(key($rowData))) {
            $val1 = $rowData[1] ?? null;
            // Pattern 2 (no name): Sl No(0), Phone(1), Email(2), Machine(3), Model(4), DOC(5), Engine(6), Series(7), Machine Serial(8)
            if ($val1 && (is_numeric(str_replace(['+', ' ', '-'], '', $val1)) || strlen($val1) <= 15)) {
                $phoneNumber = $val1;
                $email = $rowData[2] ?? null;
                $machineName = $rowData[3] ?? null;
                $productModelName = $rowData[4] ?? null;
                $docRaw = $rowData[5] ?? null;
                $engineModel = $rowData[6] ?? null;
                $engineSerialNumber = $rowData[7] ?? null;
                $machineSerialNumber = $rowData[8] ?? null;
                $modelSeriesName = $machineSerialNumber ?? $engineSerialNumber ?? null;
            } else {
                // Assume Pattern 1: Sl No(0), Name(1), Phone(2), Email(3), Machine(4), Model(5), DOC(6), Engine(7), Series(8), Machine Serial(9)
                $customerName = $val1;
                $phoneNumber = $rowData[2] ?? null;
                $email = $rowData[3] ?? null;
                $machineName = $rowData[4] ?? null;
                $productModelName = $rowData[5] ?? null;
                $docRaw = $rowData[6] ?? null;
                $engineModel = $rowData[7] ?? null;
                $engineSerialNumber = $rowData[8] ?? null;
                $machineSerialNumber = $rowData[9] ?? null;
                $modelSeriesName = $machineSerialNumber ?? $engineSerialNumber ?? null;
            }
        }

        $modelSeriesName = trim($modelSeriesName ?? '');
        $warnings = [];

        try {
            if (empty($machineName)) {
                return;
            }

            // Handle Date (DOC)
            $doc = null;
            if ($docRaw) {
                if (is_numeric($docRaw)) {
                    try {
                        $doc = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($docRaw);
                    } catch (\Exception $e) {
                        Log::warning("Excel date conversion failed for '{$docRaw}': " . $e->getMessage());
                    }
                } else {
                    try {
                        $doc = Carbon::parse($docRaw);
                    } catch (\Exception $e) {
                        Log::warning("Carbon date parsing failed for '{$docRaw}': " . $e->getMessage());
                    }
                }
            }

            $client = null;

            // 1. Lookup Client by Phone
            if (!empty($phoneNumber)) {
                $phoneNumber = trim($phoneNumber);
                if (!isset($this->clientCache['phone_' . $phoneNumber])) {
                    $c = Client::where('phone_number', $phoneNumber)->first();
                    $this->clientCache['phone_' . $phoneNumber] = $c ?: false;
                }
                $client = $this->clientCache['phone_' . $phoneNumber];
            }

            // 2. Lookup Client by Email (if not found by phone)
            if (!$client && !empty($email)) {
                $email = trim($email);
                if (!isset($this->clientCache['email_' . $email])) {
                    $c = Client::where('email', $email)->first();
                    $this->clientCache['email_' . $email] = $c ?: false;
                }
                $client = $this->clientCache['email_' . $email];
            }

            // 3. Create Client if not found
            if (!$client) {
                if (empty($customerName) && empty($phoneNumber)) {
                    $this->failedCount++;
                    $this->errorResults[] = [
                        'row_number' => $this->processedRows,
                        'client_name' => 'N/A',
                        'machine' => $machineName ?? 'N/A',
                        'status' => 'failed',
                        'reason' => "Cannot create client: Both Name and Phone are missing."
                    ];
                    return;
                }

                $dealership_id_for_client = Auth::user()->employee->dealership_id ?? null;
                
                $client = Client::create([
                    'name' => !empty($customerName) ? $customerName : $phoneNumber,
                    'phone_number' => $phoneNumber,
                    'email' => $email,
                    'dealership_id' => $dealership_id_for_client,
                    'employee_id' => Auth::user()->employee->id ?? Auth::user()->id ?? null,
                    'import_id' => $this->import_id,
                    'notes' => 'Auto-created during product import'
                ]);

                // Cache the new client
                if (!empty($phoneNumber)) $this->clientCache['phone_' . $phoneNumber] = $client;
                if (!empty($email)) $this->clientCache['email_' . $email] = $client;
                
                $warnings[] = "Newly created client: " . $client->name;
            }

            // 4. Determine Dealership
            $dealership_id = Auth::user()->employee->dealership_id ?? null;

            // 5. Product (Machine) Association
            if (!isset($this->productCache[$machineName])) {
                $product = Product::where('name', $machineName)->first();
                $this->productCache[$machineName] = $product ? $product->id : false;
            }
            $product_id = $this->productCache[$machineName] ?: null;

            if (!$product_id) {
                $this->failedCount++;
                $this->errorResults[] = [
                    'row_number' => $this->processedRows,
                    'status' => 'failed',
                    'reason' => "Machine '{$machineName}' not found in products table"
                ];
                return;
            }

            // 6. Model Lookup/Creation
            $product_model_id = null;
            if ($productModelName) {
                $modelKey = $productModelName . '_' . $product_id;
                if (!isset($this->productModelCache[$modelKey])) {
                    $pm = ProductModel::firstOrCreate([
                        'name' => $productModelName,
                        'product_id' => $product_id
                    ]);
                    $this->productModelCache[$modelKey] = $pm->id;
                }
                $product_model_id = $this->productModelCache[$modelKey] ?: null;
            }

            // 7. Series (Serial Number) Lookup/Creation
            $model_series_id = null;
            if ($modelSeriesName && $product_model_id) {
                $seriesKey = $modelSeriesName . '_' . $product_model_id;
                if (!isset($this->modelSeriesCache[$seriesKey])) {
                    $ms = ModelSeries::firstOrCreate([
                        'name' => $modelSeriesName,
                        'product_model_id' => $product_model_id
                    ]);
                    $this->modelSeriesCache[$seriesKey] = $ms->id;
                }
                $model_series_id = $this->modelSeriesCache[$seriesKey] ?: null;
            }

            // 8. Create ClientProduct
            // Save to new client_products table
            $existsInProducts = ClientProduct::where('client_id', $client->id)
                ->where('product_id', $product_id)
                ->where('product_model_id', $product_model_id)
                ->where('model_series_id', $model_series_id)
                ->exists();

            if (!$existsInProducts) {
                ClientProduct::create([
                    'client_id' => $client->id,
                    'product_id' => $product_id,
                    'product_model_id' => $product_model_id,
                    'model_series_id' => $model_series_id,
                    'doc' => $doc,
                    'engine_model' => $engineModel,
                    'engine_serial_number' => $engineSerialNumber,
                    'machine_serial_number' => $machineSerialNumber,
                    'dealership_id' => $dealership_id,
                    'import_id' => $this->import_id,
                ]);
            }

            $this->successCount++;
            $this->errorResults[] = [
                'row_number' => $this->processedRows,
                'client_name' => $client->name,
                'machine' => $machineName,
                'status' => !empty($warnings) ? 'success_with_warnings' : 'success',
                'reason' => 'Product association created',
                'warnings' => $warnings
            ];

        } catch (\Exception $e) {
            Log::error("Error processing row {$this->processedRows} for product import {$this->import_id}: " . $e->getMessage());
            $this->failedCount++;
            $this->errorResults[] = [
                'row_number' => $this->processedRows,
                'client_name' => $customerName ?: ($phoneNumber ?: 'Unknown'),
                'machine' => $machineName ?? 'Unknown',
                'status' => 'failed',
                'reason' => $e->getMessage()
            ];
        }

        if ($this->processedRows % 50 === 0 || $this->processedRows === $this->totalRows) {
            $this->updateProgressInCache();
        }
    }

    private function updateProgressInCache()
    {
        $progress = Cache::get('client_import_progress:' . $this->import_id);
        if (!$progress) {
            $progress = ['total_rows' => $this->totalRows ?? 0, 'results' => []];
        }
        
        $progress['processed_rows'] = $this->processedRows;
        $progress['success_count'] = $this->successCount;
        $progress['failed_count'] = $this->failedCount;
        $progress['results'] = $this->errorResults;
        
        if ($this->totalRows > 0) {
            $progress['percentage'] = min(99, round(($this->processedRows / $this->totalRows) * 100));
        }
        
        Cache::put('client_import_progress:' . $this->import_id, $progress, now()->addHours(2));
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
        $progress = Cache::get('client_import_progress:' . $import->import_id);
        if ($progress) {
            $progress['processed_rows'] = $import->processedRows;
            $progress['success_count'] = $import->successCount;
            $progress['failed_count'] = $import->failedCount;
            $progress['results'] = $import->errorResults;
            $progress['status'] = 'completed';
            $progress['percentage'] = 100;
            Cache::put('client_import_progress:' . $import->import_id, $progress, now()->addHours(2));
        }
    }

    public function afterSheet(AfterSheet $event)
    {
        $this->totalRows = $event->getDelegate()->getHighestRow() - 1;
        $progress = Cache::get('client_import_progress:' . $this->import_id);
        if ($progress) {
            $progress['total_rows'] = $this->totalRows;
            Cache::put('client_import_progress:' . $this->import_id, $progress, now()->addHours(2));
        }
    }
}
