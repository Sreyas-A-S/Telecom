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

class UpdateClientProductsTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle, WithStyles, WithColumnWidths
{
    public function title(): string
    {
        return 'Update Products Import Sheet';
    }

    public function collection()
    {
        return collect([
            [
                'sl_no' => '1',
                'name' => 'John Doe',
                'phone_number' => '9876543210',
                'email' => 'john@example.com',
                'machine' => 'Excavator',
                'machine_model' => 'EX-200',
                'doc' => '2024-01-20',
                'engine_model' => '6BG1T',
                'engine_serial_number' => 'SN123456',
                'machine_serial_number' => 'MSN654321', // The new machine serial number
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'Sl No',
            'Name',
            'Phone Number',
            'Email',
            'Machine',
            'Machine Model',
            'DOC',
            'Engine Model',
            'Engine Serial Number',
            'Machine Serial Number',
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
            'B' => 30, // Name
            'C' => 25, // Phone Number
            'D' => 35, // Email
            'E' => 30, // Machine
            'F' => 30, // Machine Model
            'G' => 20, // DOC
            'H' => 30, // Engine Model
            'I' => 30, // Engine Serial Number
            'J' => 30, // Machine Serial Number
        ];
    }
}
