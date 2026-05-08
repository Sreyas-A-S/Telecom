<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\ClientProduct;
use App\Models\Product;
use App\Models\ProductModel;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateClientProductsImport implements OnEachRow, WithHeadingRow, WithChunkReading, WithEvents
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

        // 1. Extract Variables
        $customerName = $rowData['client_name'] ?? $rowData['name'] ?? null;
        $phoneNumber = $rowData['phone_number'] ?? null;
        $email = $rowData['email'] ?? null;
        $machineName = $rowData['machine'] ?? null;
        $productModelName = $rowData['machine_model'] ?? null;
        $docRaw = $rowData['doc'] ?? null;
        $engineModel = $rowData['engine_model'] ?? null;
        $engineSerialNumber = $rowData['engine_serial_number'] ?? null;
        $machineSerialNumber = $rowData['machine_serial_number'] ?? null;

        if (is_numeric(key($rowData))) {
            $val1 = $rowData[1] ?? null;
            if ($val1 && (is_numeric(str_replace(['+', ' ', '-'], '', $val1)) || strlen($val1) <= 15)) {
                $phoneNumber = $val1;
                $email = $rowData[2] ?? null;
                $machineName = $rowData[3] ?? null;
                $productModelName = $rowData[4] ?? null;
                $docRaw = $rowData[5] ?? null;
                $engineModel = $rowData[6] ?? null;
                $engineSerialNumber = $rowData[7] ?? null;
                $machineSerialNumber = $rowData[8] ?? null;
            } else {
                $customerName = $val1;
                $phoneNumber = $rowData[2] ?? null;
                $email = $rowData[3] ?? null;
                $machineName = $rowData[4] ?? null;
                $productModelName = $rowData[5] ?? null;
                $docRaw = $rowData[6] ?? null;
                $engineModel = $rowData[7] ?? null;
                $engineSerialNumber = $rowData[8] ?? null;
                $machineSerialNumber = $rowData[9] ?? null;
            }
        }

        $warnings = [];

        try {
            if (empty($machineName) || empty($machineSerialNumber)) {
                $this->failedCount++;
                $this->errorResults[] = [
                    'row_number' => $this->processedRows,
                    'status' => 'failed',
                    'reason' => "Machine Name and new Machine Serial Number are required."
                ];
                return;
            }

            // Handle DOC
            $doc = null;
            if ($docRaw) {
                if (is_numeric($docRaw)) {
                    try {
                        $doc = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($docRaw);
                    } catch (\Exception $e) {
                    }
                } else {
                    try {
                        $doc = Carbon::parse($docRaw);
                    } catch (\Exception $e) {
                    }
                }
            }

            // Find client
            $client = null;
            if (!empty($phoneNumber)) {
                $phoneNumber = trim($phoneNumber);
                if (!isset($this->clientCache['phone_' . $phoneNumber])) {
                    $c = Client::where('phone_number', $phoneNumber)->first();
                    $this->clientCache['phone_' . $phoneNumber] = $c ?: false;
                }
                $client = $this->clientCache['phone_' . $phoneNumber];
            }

            if (!$client && !empty($email)) {
                $email = trim($email);
                if (!isset($this->clientCache['email_' . $email])) {
                    $c = Client::where('email', $email)->first();
                    $this->clientCache['email_' . $email] = $c ?: false;
                }
                $client = $this->clientCache['email_' . $email];
            }

            if (!$client) {
                $this->failedCount++;
                $this->errorResults[] = [
                    'row_number' => $this->processedRows,
                    'client_name' => $customerName ?? $phoneNumber ?? $email ?? 'N/A',
                    'machine' => $machineName,
                    'status' => 'failed',
                    'reason' => "Client not found."
                ];
                return;
            }

            // Find Product
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
                    'reason' => "Machine '{$machineName}' not found."
                ];
                return;
            }

            // Find Model
            $product_model_id = null;
            if ($productModelName) {
                $modelKey = $productModelName . '_' . $product_id;
                if (!isset($this->productModelCache[$modelKey])) {
                    $pm = ProductModel::where('name', $productModelName)->where('product_id', $product_id)->first();
                    $this->productModelCache[$modelKey] = $pm ? $pm->id : false;
                }
                $product_model_id = $this->productModelCache[$modelKey] ?: null;
            }

            // Find ClientProduct to update
            $query = ClientProduct::where('client_id', $client->id)
                ->where('product_id', $product_id);

            if ($product_model_id) {
                $query->where('product_model_id', $product_model_id);
            }

            if ($engineModel) {
                $query->where('engine_model', $engineModel);
            }

            if ($doc) {
                $query->whereDate('doc', $doc->format('Y-m-d'));
            }

            $matchedProducts = $query->get();

            if ($matchedProducts->count() === 0) {
                $this->failedCount++;
                $this->errorResults[] = [
                    'row_number' => $this->processedRows,
                    'client_name' => $client->name,
                    'machine' => $machineName,
                    'status' => 'failed',
                    'reason' => "No matching Client Product found."
                ];
                return;
            }

            if ($matchedProducts->count() > 1) {
                $this->failedCount++;
                $this->errorResults[] = [
                    'row_number' => $this->processedRows,
                    'client_name' => $client->name,
                    'machine' => $machineName,
                    'status' => 'failed',
                    'reason' => "Multiple matching Client Products found. Please provide more precise filters."
                ];
                return;
            }

            $clientProduct = $matchedProducts->first();

            $updated = false;

            // Store old machine serial and assign new one
            if ($clientProduct->machine_serial_number != $machineSerialNumber) {
                $clientProduct->old_machine_serial_number = $clientProduct->machine_serial_number;
                $clientProduct->machine_serial_number = $machineSerialNumber;
                $updated = true;
            }

            // Update conditionally for engine serial number
            if (empty($clientProduct->engine_serial_number) && !empty($engineSerialNumber)) {
                $clientProduct->old_engine_serial_number = $clientProduct->engine_serial_number;
                $clientProduct->engine_serial_number = $engineSerialNumber;
                $updated = true;
            }

            if ($updated) {
                $clientProduct->update_import_id = $this->import_id;
                $clientProduct->save();

                $this->successCount++;
                $this->errorResults[] = [
                    'row_number' => $this->processedRows,
                    'client_name' => $client->name,
                    'machine' => $machineName,
                    'status' => 'success',
                    'reason' => 'Serial number(s) updated successfully.',
                ];
            } else {
                $this->successCount++;
                $this->errorResults[] = [
                    'row_number' => $this->processedRows,
                    'client_name' => $client->name,
                    'machine' => $machineName,
                    'status' => 'skipped',
                    'reason' => 'Serial numbers already match data.',
                ];
            }
        } catch (\Exception $e) {
            Log::error("Error updating row {$this->processedRows} for product update {$this->import_id}: " . $e->getMessage());
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
