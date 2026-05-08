<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class GeneralReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;
    protected $type;

    public function __construct($data, $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    public function collection()
    {
        return $this->data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]],
        ];
    }

    public function headings(): array
    {
        switch ($this->type) {
            case 'leave':
                return ['ID', 'Employee', 'Type', 'Start Date', 'End Date', 'Reason', 'Status', 'Created At'];
            case 'expense':
                return ['ID', 'Employee', 'Type', 'Date', 'Amount', 'Description', 'Status', 'Created At'];
            case 'legacy_expense':
                return ['ID', 'Employee', 'Type', 'Date', 'Amount', 'Approved Amount', 'Description', 'Status', 'Created At'];
            case 'document':
                return ['ID', 'Employee', 'Document Type', 'Requested Date', 'Remarks', 'Status', 'Created At'];
            case 'loan':
                return ['ID', 'Employee', 'Amount', 'Requested On', 'Status', 'Created At'];
            default:
                return [];
        }
    }

    public function map($row): array
    {
        $employeeName = $row->user->name ?? 'N/A';
        $createdAt = $row->created_at ? $row->created_at->format('Y-m-d H:i') : '';

        switch ($this->type) {
            case 'leave':
                return [
                    $row->id,
                    $employeeName,
                    $row->leave_type,
                    $row->start_date ? Carbon::parse($row->start_date)->format('Y-m-d') : '',
                    $row->end_date ? Carbon::parse($row->end_date)->format('Y-m-d') : '',
                    $row->reason,
                    $row->status,
                    $createdAt
                ];
            case 'expense':
                return [
                    $row->id,
                    $employeeName,
                    $row->expense_type,
                    $row->date ? Carbon::parse($row->date)->format('Y-m-d') : '',
                    $row->amount,
                    $row->description,
                    $row->status,
                    $createdAt
                ];
            case 'legacy_expense':
                return [
                    $row->id,
                    $employeeName,
                    $row->expense_type,
                    $row->date ? Carbon::parse($row->date)->format('Y-m-d') : '',
                    $row->amount,
                    $row->approved_amount ?? 0,
                    $row->description,
                    $row->status,
                    $createdAt
                ];
            case 'document':
                return [
                    $row->id,
                    $employeeName,
                    $row->documentType->name ?? 'N/A',
                    $row->requested_date ? Carbon::parse($row->requested_date)->format('Y-m-d') : '',
                    $row->remarks,
                    $row->status,
                    $createdAt
                ];
            case 'loan':
                return [
                    $row->id,
                    $employeeName,
                    $row->amount,
                    $row->requested_on ? Carbon::parse($row->requested_on)->format('Y-m-d') : '',
                    $row->status,
                    $createdAt
                ];
            default:
                return [];
        }
    }
}
