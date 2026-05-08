<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ClientProductsTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle, WithStyles, WithColumnWidths
{
    public function title(): string
    {
        return 'Client Products Import sheet';
    }

    public function collection()
    {
        return collect([
            [
                'sl_no' => '1',
                'phone_number' => '9876543210',
                'email' => 'john@example.com',
                'machine' => 'Excavator',
                'machine_model' => 'EX-200',
                'doc' => '2024-01-20',
                'engine_model' => '6BG1T',
                'engine_serial_number' => 'SN123456',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'Sl No',
            'Phone Number',
            'Email',
            'Machine',
            'Machine Model',
            'DOC',
            'Engine Model',
            'Engine Serial Number',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFEEEEEE'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, // Sl No
            'B' => 25, // Phone Number
            'C' => 35, // Email
            'D' => 30, // Machine
            'E' => 30, // Machine Model
            'F' => 20, // DOC
            'G' => 30, // Engine Model
            'H' => 30, // Engine Serial Number
        ];
    }
}
