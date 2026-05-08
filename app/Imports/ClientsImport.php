<?php

namespace App\Imports;

use App\Models\Client;
use App\Models\State;
use App\Models\District;
use App\Models\LeadSource;
use App\Models\LeadCategory;
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

class ClientsImport implements OnEachRow, WithHeadingRow, WithChunkReading, WithEvents, WithCalculatedFormulas
{
    use RegistersEventListeners;

    private $import_id;
    private $totalRows;
    private $processedRows = 0;
    private $successCount = 0;
    private $failedCount = 0;
    private $errorResults = [];
    
    private $stateCache = [];
    private $districtCache = [];
    private $leadSourceCache = [];
    private $leadCategoryCache = [];

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

        // Safe indexing fallback
        // Headings: Sl No(0), Salutation(1), Name(2), Email(3), Phone Number(4), Address(5), State(6), District(7), Lead Source(8), Lead Category(9)
        $customerName = $rowData['name'] ?? $rowData[2] ?? null;
        $salutation = $rowData['salutation'] ?? $rowData['mr'] ?? $rowData['mrs'] ?? $rowData['ms'] ?? $rowData['dr'] ?? $rowData[1] ?? null;
        $email = $rowData['email'] ?? $rowData[3] ?? null;
        $phoneNumber = $rowData['phone_number'] ?? $rowData[4] ?? null;
        $address = $rowData['address'] ?? $rowData[5] ?? null;
        $stateName = $rowData['state'] ?? $rowData[6] ?? null;
        $districtName = $rowData['district'] ?? $rowData[7] ?? null;
        $lsName = $rowData['lead_source'] ?? $rowData[8] ?? null;
        $lcName = $rowData['lead_category'] ?? $rowData[9] ?? null;

        try {
            if (!isset($customerName) || empty($customerName)) {
                return;
            }

            // State lookup
            $state_id = null;
            if ($stateName) {
                $stateName = trim($stateName);
                if (!isset($this->stateCache[$stateName])) {
                    $state = State::where('name', $stateName)->first();
                    $this->stateCache[$stateName] = $state ? $state->id : false;
                }
                $state_id = $this->stateCache[$stateName] ?: null;
            }

            // District lookup
            $district_id = null;
            if ($districtName) {
                $districtName = trim($districtName);
                $cacheKey = $districtName . ($state_id ?: '');
                if (!isset($this->districtCache[$cacheKey])) {
                    $query = District::where('name', $districtName);
                    if ($state_id) $query->where('state_id', $state_id);
                    $district = $query->first();
                    $this->districtCache[$cacheKey] = $district ? $district->id : false;
                }
                $district_id = $this->districtCache[$cacheKey] ?: null;
            }

            // Lead Source lookup
            $lead_source_id = null;
            if ($lsName) {
                $lsName = trim($lsName);
                if (!isset($this->leadSourceCache[$lsName])) {
                    $ls = LeadSource::firstOrCreate(['name' => $lsName]);
                    $this->leadSourceCache[$lsName] = $ls->id;
                }
                $lead_source_id = $this->leadSourceCache[$lsName];
            }

            // Lead Category lookup
            $lead_category_id = null;
            if ($lcName) {
                $lcName = trim($lcName);
                if (!isset($this->leadCategoryCache[$lcName])) {
                    $lc = LeadCategory::firstOrCreate(['name' => $lcName]);
                    $this->leadCategoryCache[$lcName] = $lc->id;
                }
                $lead_category_id = $this->leadCategoryCache[$lcName];
            }

            // Dealership from Auth User
            $dealership_id = Auth::user()->employee->dealership_id ?? null;

            // Find or Create Client
            if ($phoneNumber) {
                $phoneNumber = trim($phoneNumber);
                $client = Client::where('phone_number', $phoneNumber)->first();
            } else {
                $client = null;
            }

            if (!$client) {
                Client::create([
                    'import_id' => $this->import_id,
                    'salutation' => $salutation,
                    'name' => trim($customerName),
                    'email' => $email,
                    'phone_number' => $phoneNumber,
                    'address' => $address,
                    'state_id' => $state_id,
                    'district_id' => $district_id,
                    'lead_source_id' => $lead_source_id,
                    'lead_category_id' => $lead_category_id,
                    'dealership_id' => $dealership_id,
                    'employee_id' => Auth::user()->employee->id ?? Auth::user()->id ?? null,
                ]);
                $this->successCount++;
            } else {
                $this->errorResults[] = [
                    'row_number' => $this->processedRows,
                    'client_name' => $customerName,
                    'machine' => 'N/A',
                    'status' => 'skipped',
                    'reason' => 'Client already exists with this phone number',
                ];
            }

        } catch (\Exception $e) {
            Log::error("Error processing row {$this->processedRows} for client import {$this->import_id}: " . $e->getMessage());
            $this->failedCount++;
            $this->errorResults[] = [
                'row_number' => $this->processedRows,
                'client_name' => $customerName ?? 'Unknown',
                'machine' => 'N/A',
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
