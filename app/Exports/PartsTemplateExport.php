<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PartsTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return collect([
            [
                'sl_no' => '1',
                'machine_model' => 'EX-X1-Base',
                'part_number' => 'PN-001',
                'material_description' => 'Sample Part Description',
                'unit_price' => '100.00',
                'hsn' => '123456',
                'dealer' => 'Sample Dealer',
                'bin' => 'A-01',
                'stock_quantity' => '50',
                'tax' => 'GST 18%',
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'Sl No',
            'Machine Model',
            'Part Number',
            'Material Description',
            'Unit Price',
            'HSN',
            'Dealer',
            'Bin',
            'Stock Quantity',
            'Tax',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1    => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FFEEEEEE'],
                ],
            ],
        ];
    }
}
