<?php

namespace App\Exports;

use App\Models\Clock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class AttendanceExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        // Join with employees table to get employee name
        $query = Clock::join('employees', 'clocks.employee_id', '=', 'employees.id')
            ->select('clocks.*', 'employees.name as employee_name');

        if (isset($this->filters['from_date']) && !empty($this->filters['from_date'])) {
            $query->whereDate('clock_in_time', '>=', $this->filters['from_date']);
        }

        if (isset($this->filters['to_date']) && !empty($this->filters['to_date'])) {
            $query->whereDate('clock_in_time', '<=', $this->filters['to_date']);
        }

        if (isset($this->filters['employee_id']) && !empty($this->filters['employee_id'])) {
            $query->where('clocks.employee_id', $this->filters['employee_id']);
        }

        if (isset($this->filters['dealership_id']) && !empty($this->filters['dealership_id'])) {
            $query->where('employees.dealership_id', $this->filters['dealership_id']);
        }

        if (isset($this->filters['department_id']) && !empty($this->filters['department_id'])) {
            $query->where('employees.department_id', $this->filters['department_id']);
        }

        return $query->get()->map(function ($clock) {
            $status = 'Present'; // Basic logic, can be refined based on remarks or time
            return [
                'ID' => $clock->id,
                'Employee Name' => $clock->employee_name,
                'Date' => Carbon::parse($clock->clock_in_time)->toDateString(),
                'Clock In' => Carbon::parse($clock->clock_in_time)->format('h:i A'),
                'Clock Out' => $clock->clock_out_time ? Carbon::parse($clock->clock_out_time)->format('h:i A') : 'N/A',
                'Status' => $status,
                'Remarks' => $clock->remarks ?? 'N/A',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Employee Name',
            'Date',
            'Clock In',
            'Clock Out',
            'Status',
            'Remarks',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 25,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 15,
            'G' => 30,
        ];
    }
}
