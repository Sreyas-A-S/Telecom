<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class BirthdaysExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Employee::whereNotNull('dob')->with('department');

        // Logic for "Birthdays within a range" usually ignores the year.
        // However, if the user picks a specific year range in the UI (e.g. 2023-01-01 to 2023-12-31),
        // we might want to just show employees whose birthday falls in that month/day range.
        // For simplicity, let's filter by month and day if range is provided.

        if (isset($this->filters['from_date']) && isset($this->filters['to_date'])) {
            $from = Carbon::parse($this->filters['from_date']);
            $to = Carbon::parse($this->filters['to_date']);

            // Filter where Month-Day is between the range.
            // This is complex in SQL if range wraps year end. Assuming standard same-year range.
            $query->whereRaw("DATE_FORMAT(dob, '%m-%d') BETWEEN ? AND ?", [$from->format('m-d'), $to->format('m-d')]);
        }

        return $query->get()->map(function ($employee) {
            return [
                'ID' => $employee->id,
                'Employee Name' => $employee->name,
                'Date of Birth' => $employee->dob, // Assuming Y-m-d
                'Department' => $employee->department ? $employee->department->name : 'N/A',
                'Designation' => $employee->designation ?? 'N/A',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Employee Name',
            'Date of Birth',
            'Department',
            'Designation',
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
            'D' => 25,
            'E' => 25,
        ];
    }
}
