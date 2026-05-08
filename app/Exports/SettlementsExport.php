<?php

namespace App\Exports;

use App\Models\Settlement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class SettlementsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Settlement::query();

        if (isset($this->filters['from_date']) && !empty($this->filters['from_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['from_date']);
        }

        if (isset($this->filters['to_date']) && !empty($this->filters['to_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['to_date']);
        }

        return $query->get()->map(function ($settlement) {
            return [
                'ID' => $settlement->id,
                'Employee Name' => $settlement->employee_name,
                'Employee Code' => $settlement->employee_code,
                'Department' => $settlement->department,
                'Designation' => $settlement->designation,
                'Head Office/Branch' => $settlement->head_office_branch,
                'Date of Joining' => $settlement->date_of_joining,
                'Date of Resignation' => $settlement->date_of_resignation,
                'Reason for Resignation' => $settlement->reason_for_resignation,
                'Created At' => Carbon::parse($settlement->created_at)->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Employee Name',
            'Employee Code',
            'Department',
            'Designation',
            'Head Office/Branch',
            'Date of Joining',
            'Date of Resignation',
            'Reason for Resignation',
            'Created At',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 25,
            'C' => 15,
            'D' => 20,
            'E' => 20,
            'F' => 25,
            'G' => 15,
            'H' => 15,
            'I' => 30,
            'J' => 20,
        ];
    }
}
