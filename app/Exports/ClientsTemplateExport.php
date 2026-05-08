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

class ClientsTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithTitle, WithStyles, WithColumnWidths
{
    public function title(): string
    {
        return 'Client Import sheet';
    }

    public function collection()
    {
        return collect([
            [
                'sl_no' => '1',
                'salutation' => 'Mr.',
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone_number' => '9876543210',
                'address' => '123 Main St, Springfield',
                'state' => 'Kerala',
                'district' => 'Ernakulam',
                'lead_source' => 'Website',
                'lead_category' => 'Hot',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'Sl No',
            'Salutation',
            'Name',
            'Email',
            'Phone Number',
            'Address',
            'State',
            'District',
            'Lead Source',
            'Lead Category',
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
            'C' => 30, // Name
            'D' => 35, // Email
            'E' => 20, // Phone Number
            'F' => 50, // Address
            'G' => 20, // State
            'H' => 20, // District
        ];
    }
}
