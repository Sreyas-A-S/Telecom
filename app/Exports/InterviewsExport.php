<?php

namespace App\Exports;

use App\Models\Interview;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class InterviewsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Interview::with(['employee', 'client', 'dealership', 'jobVacancy']);

        if (isset($this->filters['from_date']) && !empty($this->filters['from_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['from_date']);
        }

        if (isset($this->filters['to_date']) && !empty($this->filters['to_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['to_date']);
        }

        return $query->get()->map(function ($interview) {
            return [
                'ID' => $interview->id,
                'Candidate Name' => $interview->candidate_name,
                'Post Applied For' => $interview->post_applied_for,
                'Interview Date' => Carbon::parse($interview->created_at)->format('Y-m-d H:i:s'),
                'Round' => $interview->interview_round,
                'Status' => $interview->status,
                'Interviewer' => $interview->employee ? $interview->employee->name : 'N/A',
                'Job Vacancy' => $interview->jobVacancy ? $interview->jobVacancy->title : 'N/A',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Candidate Name',
            'Post Applied For',
            'Interview Date',
            'Round',
            'Status',
            'Interviewer',
            'Job Vacancy',
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
            'D' => 20,
            'E' => 15,
            'F' => 15,
            'G' => 25,
            'H' => 25,
        ];
    }
}
