<?php

namespace App\Exports;

use App\Models\JobVacancy;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class JobVacanciesExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = JobVacancy::with(['creator']);

        if (isset($this->filters['from_date']) && !empty($this->filters['from_date'])) {
            $query->whereDate('created_at', '>=', $this->filters['from_date']);
        }

        if (isset($this->filters['to_date']) && !empty($this->filters['to_date'])) {
            $query->whereDate('created_at', '<=', $this->filters['to_date']);
        }

        return $query->get()->map(function ($vacancy) {
            return [
                'ID' => $vacancy->id,
                'Title' => $vacancy->title,
                'Status' => $vacancy->status,
                'Views Count' => $vacancy->views_count,
                'Applications Count' => $vacancy->applications()->count(),
                'Created By' => $vacancy->creator->name ?? 'N/A',
                'Created At' => Carbon::parse($vacancy->created_at)->format('Y-m-d H:i:s'),
            ];
        });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Title',
            'Status',
            'Views Count',
            'Applications Count',
            'Created By',
            'Created At',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, // ID
            'B' => 40, // Title
            'C' => 15, // Status
            'D' => 15, // Views Count
            'E' => 20, // Applications Count
            'F' => 25, // Created By
            'G' => 25, // Created At
        ];
    }
}
