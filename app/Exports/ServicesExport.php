<?php

namespace App\Exports;

use App\Models\Service;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ServicesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithTitle, WithStyles, WithColumnWidths
{
    protected $assignmentStatus;
    protected $dealershipId;
    protected $zoneId;
    protected $rowNumber = 0;

    public function __construct($assignmentStatus = 'all', $dealershipId = null, $zoneId = null)
    {
        $this->assignmentStatus = $assignmentStatus;
        $this->dealershipId = $dealershipId;
        $this->zoneId = $zoneId;
    }

    public function query()
    {
        $query = Service::with(['client', 'product', 'productModel', 'modelSeries', 'serviceEngineer.user', 'serviceEngineer2.user', 'dealership', 'zone']);

        if ($this->assignmentStatus === 'assigned') {
            $query->where(function($q) {
                $q->whereNotNull('service_engineer_id')
                  ->orWhereNotNull('service_engineer_id_2');
            });
        } elseif ($this->assignmentStatus === 'unassigned') {
            $query->whereNull('service_engineer_id')
                  ->whereNull('service_engineer_id_2');
        }

        if ($this->dealershipId) {
            $query->where('dealership_id', $this->dealershipId);
        }

        if ($this->zoneId) {
            $query->where('zone_id', $this->zoneId);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'Sl No',
            'Customer Name',
            'Zone',
            'Machine Model',
            'Machine Serial Number',
            'Product',
            'DOC - (Date of Commissioning)',
            'Machine Status',
            'Type of Service',
            'Nature of Complaints',
            'Contact Person',
            'Contact',
            'Failure Date',
            'Failure HMR',
            'Revenue',
            'Requested Location',
            'Service Engineer (Mobile, Email or Emp ID)',
            'Service Engineer 2 (Mobile, Email or Emp ID)',
            'Call Status',
            'Call Remarks',
            'Referral ID',
            'Created At',
            'Updated At',
        ];
    }

    public function map($service): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $service->client ? $service->client->name : 'N/A',
            $service->zone ? $service->zone->name : 'N/A',
            $service->productModel ? $service->productModel->name : 'N/A',
            $service->modelSeries ? $service->modelSeries->name : 'N/A',
            $service->product ? $service->product->name : 'N/A',
            $service->doc,
            $this->humanize($service->machine_status),
            $this->humanize($service->type_of_service),
            $service->description,
            $service->contact_person,
            $service->contact_info,
            $service->failure_date,
            $service->failure_hmr,
            $service->price,
            $service->requested_location,
            $service->serviceEngineer ? ($service->serviceEngineer->user->name ?? $service->serviceEngineer->employee_id) : 'N/A',
            $service->serviceEngineer2 ? ($service->serviceEngineer2->user->name ?? $service->serviceEngineer2->employee_id) : 'N/A',
            $this->humanize($service->call_status),
            $service->call_remarks,
            $service->referral_id,
            $service->created_at ? $service->created_at->format('Y-m-d H:i:s') : 'N/A',
            $service->updated_at ? $service->updated_at->format('Y-m-d H:i:s') : 'N/A',
        ];
    }

    private function humanize($value)
    {
        if (!$value) return 'N/A';
        return ucwords(str_replace('_', ' ', $value));
    }

    public function title(): string
    {
        return ucfirst($this->assignmentStatus) . ' Services Export';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D3D3D3'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,  // Sl No
            'B' => 30,  // Customer Name
            'C' => 20,  // Zone
            'D' => 30,  // Machine Model
            'E' => 30,  // Machine Serial Number
            'F' => 30,  // Product
            'G' => 35,  // DOC
            'H' => 25,  // Machine Status
            'I' => 25,  // Type of Service
            'J' => 50,  // Nature of Complaints
            'K' => 30,  // Contact Person
            'L' => 25,  // Contact
            'M' => 20,  // Failure Date
            'N' => 20,  // Failure HMR
            'O' => 15,  // Revenue
            'P' => 40,  // Requested Location
            'Q' => 52,  // Service Engineer 1
            'R' => 52,  // Service Engineer 2
            'S' => 20,  // Call Status
            'T' => 50,  // Call Remarks
            'U' => 20,  // Referral ID
            'V' => 25,  // Created At
            'W' => 25,  // Updated At
        ];
    }
}
