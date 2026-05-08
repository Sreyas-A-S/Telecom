<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ServicesTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle, WithStyles, WithColumnWidths
{
    public function collection()
    {
        return collect([
            [
                '1',
                'John Doe Customer',
                'North',
                'EX-X1-Base',
                'Series-A',
                'Excavator X1',
                '2024-01-01',
                'warranty',
                'free_service',
                'Routine checkup for the product',
                'Manager Bob',
                '9876543210',
                '2024-01-15',
                '1500',
                '5000',
                'Site-A, Industrial Estate',
                'EMP001',
                'EMP002',
                'opened',
                'Initial call received',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'Sl No',
            'Customer Name',
            'Zone',
            'Machine Model',
            'Machine Serial Number',
            'Product',
            'DOC - (Date of Commissioning)',
            'Machine Status',
            'Type of Service',
            'Nature of Complaints',
            'Contact Person',
            'Contact',
            'Failure Date',
            'Failure HMR',
            'Revenue',
            'Requested Location',
            'Service Engineer (Mobile, Email or Emp ID)',
            'Service Engineer 2 (Mobile, Email or Emp ID)',
            'Call Status',
            'Call Remarks',
        ];
    }

    public function title(): string
    {
        return 'Service Import Template Updated';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFEEEEEE'],
                ],
            ],
            'F' => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            'G' => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
            'I' => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, // Sl No
            'B' => 30, // Customer Name
            'C' => 30, // Machine Model
            'D' => 30, // Machine Serial Number
            'E' => 30, // Product
            'F' => 35, // DOC - (Date of Commissioning)
            'G' => 25, // Machine Status
            'H' => 25, // Type of Service
            'I' => 50, // Nature of Complaints
            'J' => 30, // Contact Person
            'K' => 25, // Contact
            'L' => 20, // Failure Date
            'M' => 20, // Failure HMR
            'N' => 15, // Revenue
            'O' => 40, // Requested Location
            'P' => 52, // Service Engineer
            'Q' => 52, // Service Engineer 2
            'R' => 20, // Call Status
            'S' => 50, // Call Remarks
        ];
    }
}
