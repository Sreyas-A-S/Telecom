<?php

namespace App\Exports;

use App\Models\PerformanceReview;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class PerformanceReviewsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = PerformanceReview::with(['employee.employee', 'reviewer.employee']);

        if (isset($this->filters['from_date']) && !empty($this->filters['from_date'])) {
            $query->whereDate('review_date', '>=', $this->filters['from_date']);
        }

        if (isset($this->filters['to_date']) && !empty($this->filters['to_date'])) {
            $query->whereDate('review_date', '<=', $this->filters['to_date']);
        }

        return $query->get()->map(function ($review) {
            return [
                'ID' => $review->id,
                'Employee Name' => $review->employee && $review->employee->employee ? $review->employee->employee->name : 'N/A',
                'Reviewer Name' => $review->reviewer && $review->reviewer->employee ? $review->reviewer->employee->name : 'N/A',
                'Review Date' => Carbon::parse($review->review_date)->format('Y-m-d'),
                'Period' => $review->review_period,
                // Assuming score/rating fields exist, otherwise adjust.
                // Checking Model/Controller earlier, it has review_period.
                'Created At' => Carbon::parse($review->created_at)->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Employee Name',
            'Reviewer Name',
            'Review Date',
            'Period',
            'Created At',
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
            'C' => 25,
            'D' => 15,
            'E' => 20,
            'F' => 25,
        ];
    }
}
