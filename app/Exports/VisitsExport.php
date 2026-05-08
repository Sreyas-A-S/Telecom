<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VisitsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $counter = 0;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Sl.No.',
            'User Name',
            'Employee Code',
            'Designation',
            'Department',
            'Dealership',
            'Manager',
            'Email',
            'Phone',
            'Task type',
            'Vehicle type',
            'Point Count',
            'Point Info',
            'Visit remarks',
            'Date',
            'Started time',
            'Ended Time',
            'Time Spent',
            'Task Running Duration',
            'Kms Travelled',
            'Travel Expense',
            'Call TA',
            'Client Name',
            'Contact',
            'Location',
            'Status',
            'Remarks'
        ];
    }

    public function map($row): array
    {
        $this->counter++;
        return [
            $this->counter,
            $row['user_name'],
            $row['employee_code'],
            $row['designation'],
            $row['department'],
            $row['dealership'],
            $row['manager'],
            $row['email'],
            $row['phone'],
            $row['task_type'],
            $row['vehicle_type'],
            $row['point_count'],
            $row['point_info'],
            $row['visit_remarks'],
            $row['date'],
            $row['started_time'],
            $row['ended_time'],
            $row['time_spent'],
            $row['task_duration'],
            $row['kms_travelled'] . ' km',
            number_format((float) ($row['travel_expense'] ?? 0), 2),
            number_format((float) ($row['call_ta'] ?? 0), 2),
            $row['client_name'],
            $row['contact'],
            $row['location'],
            $row['status'],
            $row['remarks'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle($sheet->calculateWorksheetDimension())->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);
        return [];
    }
}
