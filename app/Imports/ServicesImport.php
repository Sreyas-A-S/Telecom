<?php

namespace App\Imports;

use App\Models\Service;
use App\Models\Client;
use App\Models\Product;
use App\Models\ProductModel;
use App\Models\ModelSeries;
use App\Models\Dealership;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

class ServicesImport implements OnEachRow, WithChunkReading, WithHeadingRow, WithEvents
{
    use RegistersEventListeners;

    private $import_id;
    private $totalRows;
    private $processedRows = 0;
    private $successCount = 0;
    private $failedCount = 0;
    private $errorResults = [];
    private $newClientsCount = 0;
    private $clientCache = [];
    private $productCache = [];
    private $productModelCache = [];
    private $modelSeriesCache = [];
    private $dealershipCache = [];
    private $employeeCache = [];

    public function __construct($import_id)
    {
        $this->import_id = $import_id;
    }

    public function onRow(Row $row)
    {
        $this->processedRows++;
        $rowData = $row->toArray();

        // Safe indexing fallback
        // Headings: Sl No(0), Customer Name(1), Zone(2), Machine Model(3), Machine Serial Number(4), Product(5), DOC(6), Machine Status(7), Type of Service(8), Complaints(9), Contact Person(10), Contact(11), Failure Date(12), Failure HMR(13), Revenue(14), Location(15), Service Engineer(16), Service Engineer 2(17), Call Status(18), Call Remarks(19)
        $customerName = $rowData['customer_name'] ?? $rowData[1] ?? null;
        $zoneName = $rowData['zone'] ?? $rowData[2] ?? null;
        $productModelName = $rowData['machine_model'] ?? $rowData[3] ?? null;
        $modelSeriesName = $rowData['machine_serial_number'] ?? $rowData[4] ?? null;
        $productValue = $rowData['product'] ?? $rowData[5] ?? null;
        $machineStatus = $rowData['machine_status'] ?? $rowData['service_category'] ?? $rowData[7] ?? null;
        $typeOfService = $rowData['type_of_service'] ?? $rowData[8] ?? null;
        $complaints = $rowData['nature_of_complaints'] ?? $rowData[9] ?? null;
        $contactPerson = $rowData['contact_person'] ?? $rowData[10] ?? null;
        $contactField = $rowData['contact'] ?? $rowData[11] ?? null;
        $failureDate = $rowData['failure_date'] ?? $rowData[12] ?? null;
        $failureHmr = $rowData['failure_hmr'] ?? $rowData[13] ?? null;
        $revenue = $rowData['revenue'] ?? $rowData[14] ?? null;
        $location = $rowData['requested_location'] ?? $rowData[15] ?? null;

        try {
            if (empty(array_filter($rowData))) {
                return;
            }

            // Use nature of complaints as the name if service name is not provided
            $name = trim($rowData['service_name'] ?? $complaints ?? '');
            $warnings = [];

            // Zone lookup
            $zone_id = null;
            if ($zoneName) {
                $zone = \App\Models\Zone::where('name', $zoneName)->first();
                if ($zone) {
                    $zone_id = $zone->id;
                } else {
                    $warnings[] = "Zone not found: {$zoneName}";
                }
            }

            // Client lookup/creation using 'Contact' field
            $client_id = null;
            if (!empty($contactField)) {
                $contactField = trim($contactField);
                if (!isset($this->clientCache[$contactField])) {
                    $client = Client::where('phone_number', $contactField)->first();
                    if ($client) {
                        $this->clientCache[$contactField] = $client->id;
                    } else if (!empty($customerName)) {
                        $newClient = Client::create([
                            'name' => $customerName,
                            'phone_number' => $contactField,
                        ]);
                        $this->clientCache[$contactField] = $newClient->id;
                        $this->newClientsCount++;
                    } else {
                        $this->clientCache[$contactField] = false;
                    }
                }
                $client_id = $this->clientCache[$contactField] ?: null;
                if (!$client_id) {
                    $warnings[] = "Client not found and Customer Name missing (Contact: {$contactField})";
                }
            }

            // Product lookup with cache
            $product_id = null;
            if ($productValue) {
                if (!isset($this->productCache[$productValue])) {
                    $product = Product::where('name', $productValue)->first();
                    $this->productCache[$productValue] = $product ? $product->id : false;
                }
                $product_id = $this->productCache[$productValue] ?: null;
                if (!$product_id) $warnings[] = "Product not found: {$productValue}";
            }

            // Dealership lookup with cache (using Product field as requested)
            $dealership_id = null;
            if ($productValue) {
                $cacheKey = strtolower($productValue);
                if (!isset($this->dealershipCache[$cacheKey])) {
                    $d = Dealership::where('name', 'LIKE', $productValue)->first();
                    $this->dealershipCache[$cacheKey] = $d ? $d->id : false;
                }
                $dealership_id = $this->dealershipCache[$cacheKey] ?: null;
                if (!$dealership_id) {
                    $warnings[] = "Dealership not found for product value: {$productValue}";
                }
            }

            // Product Model lookup with cache
            $product_model_id = null;
            if ($productModelName) {
                if (!isset($this->productModelCache[$productModelName])) {
                    $pm = ProductModel::where('name', $productModelName)->first();
                    $this->productModelCache[$productModelName] = $pm ? $pm->id : false;
                }
                $product_model_id = $this->productModelCache[$productModelName] ?: null;
                if (!$product_model_id) $warnings[] = "Machine Model not found: {$productModelName}";
            }

            // Model Series lookup with cache
            $model_series_id = null;
            if ($modelSeriesName) {
                if (!isset($this->modelSeriesCache[$modelSeriesName])) {
                    $ms = ModelSeries::where('name', $modelSeriesName)->first();
                    $this->modelSeriesCache[$modelSeriesName] = $ms ? $ms->id : false;
                }
                $model_series_id = $this->modelSeriesCache[$modelSeriesName] ?: null;
                if (!$model_series_id) $warnings[] = "Serial Number not found: {$modelSeriesName}";
            }

            // Service Engineer lookups with cache
            $service_engineer_id = $this->getEngineerId($rowData['service_engineer_mobile_email_or_emp_id'] ?? null);
            $service_engineer_id_2 = $this->getEngineerId($rowData['service_engineer_2_mobile_email_or_emp_id'] ?? null);

            $referral_id = ($service_engineer_id || $service_engineer_id_2) ? $this->generateUniqueReferralId() : null;
            $assigned_at = ($service_engineer_id || $service_engineer_id_2) ? now() : null;

            Service::create([
                'name' => $name ?: null,
                'description' => $complaints,
                'client_id' => $client_id,
                'product_id' => $product_id,
                'product_model_id' => $product_model_id,
                'model_series_id' => $model_series_id,
                'dealership_id' => $dealership_id,
                'zone_id' => $zone_id,
                'service_engineer_id' => $service_engineer_id,
                'service_engineer_id_2' => $service_engineer_id_2,
                'assigned_at' => $assigned_at,
                'is_service' => 1,
                'requested_location' => $location,
                'contact_info' => $contactField,
                'machine_status' => $machineStatus,
                'type_of_service' => $typeOfService,
                'price' => $revenue,
                'contact_person' => $contactPerson,
                'doc' => $rowData['doc_date_of_commissioning'] ?? $rowData[6] ?? null,
                'failure_date' => $failureDate,
                'failure_hmr' => $failureHmr,
                'import_id' => $this->import_id,
                'referral_id' => $referral_id,
                'call_status' => $rowData['call_status'] ?? $rowData[18] ?? 'opened',
                'call_remarks' => $rowData['call_remarks'] ?? $rowData[19] ?? null,
            ]);

            if (empty($warnings)) {
                $this->successCount++;
            } else {
                $this->errorResults[] = [
                    'row_number' => $this->processedRows,
                    'service_name' => $name ?: 'Row ' . $this->processedRows,
                    'status' => 'success_with_warnings',
                    'reason' => 'Imported with warnings',
                    'warnings' => $warnings
                ];
            }
        } catch (\Exception $e) {
            Log::error("Error processing row {$this->processedRows} for service import {$this->import_id}: " . $e->getMessage());
            $this->failedCount++;
            $this->errorResults[] = [
                'row_number' => $this->processedRows,
                'service_name' => $name ?? 'Unknown',
                'status' => 'failed',
                'reason' => $e->getMessage()
            ];
        }

        if ($this->processedRows % 50 === 0 || $this->processedRows === $this->totalRows) {
            $this->updateProgressInCache();
        }

        if ($this->processedRows % 100 === 0) {
            gc_collect_cycles();
        }
    }

    private function getEngineerId($identifier)
    {
        if (empty($identifier)) return null;
        $identifier = trim($identifier);
        if (!isset($this->employeeCache[$identifier])) {
            $eng = Employee::where('mobile', $identifier)->orWhere('email', $identifier)->orWhere('employee_id', $identifier)->first();
            $this->employeeCache[$identifier] = $eng ? $eng->id : false;
        }
        return $this->employeeCache[$identifier] ?: null;
    }

    private function updateProgressInCache()
    {
        $progress = Cache::get('service_import_progress:' . $this->import_id);
        if (!$progress) {
            $progress = [
                'total_rows' => $this->totalRows ?? 0,
                'results' => []
            ];
        }

        $progress['processed_rows'] = $this->processedRows;
        $progress['success_count'] = $this->successCount;
        $progress['failed_count'] = $this->failedCount;
        $progress['new_clients_count'] = $this->newClientsCount;
        $progress['results'] = $this->errorResults;

        if ($this->totalRows > 0) {
            $progress['percentage'] = min(99, round(($this->processedRows / $this->totalRows) * 100));
        }

        Cache::put('service_import_progress:' . $this->import_id, $progress, now()->addHours(2));
    }

    public function chunkSize(): int
    {
        return 250;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public static function afterImport(AfterImport $event)
    {
        $import = $event->getConcernable();
        $progress = Cache::get('service_import_progress:' . $import->import_id);
        if ($progress) {
            $progress['processed_rows'] = $import->processedRows;
            $progress['success_count'] = $import->successCount;
            $progress['failed_count'] = $import->failedCount;
            $progress['new_clients_count'] = $import->newClientsCount;
            $progress['results'] = $import->errorResults;
            $progress['status'] = 'completed';
            $progress['percentage'] = 100;
            Cache::put('service_import_progress:' . $import->import_id, $progress, now()->addHours(2));
        }
    }

    public function afterSheet(AfterSheet $event)
    {
        $this->totalRows = $event->getDelegate()->getHighestRow() - 1;
        $progress = Cache::get('service_import_progress:' . $this->import_id);
        if ($progress) {
            $progress['total_rows'] = $this->totalRows;
            Cache::put('service_import_progress:' . $this->import_id, $progress, now()->addHours(2));
        }
    }

    private function generateUniqueReferralId()
    {
        $date = \Carbon\Carbon::now()->format('dmy');
        $prefix = 'SVHE' . $date;
        $latestService = Service::where('referral_id', 'like', $prefix . '%')->orderBy('referral_id', 'desc')->first();
        $sequence = $latestService ? ((int) substr($latestService->referral_id, -4)) + 1 : 1;
        $referralId = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        while (Service::where('referral_id', $referralId)->exists()) {
            $sequence++;
            $referralId = $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
        }
        return $referralId;
    }
}
