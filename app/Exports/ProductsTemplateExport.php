<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductsTemplateExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    public function collection()
    {
        return new Collection([
            [
                'sl_no' => '1',
                'dealership' => 'Sample Dealership',
                'machine_model' => 'Model X',
                'price' => '100',
                'hsn' => '1234',
                'sac' => '5678',
                'description' => 'Sample Description',
                'unit_type' => 'PCS',
                'category' => 'Electronics',
                'sub_category' => 'Mobile',
                'tax' => 'GST 18%',
                'brand' => 'Linde'
            ]
        ]);
    }

    public function headings(): array
    {
        return [
            'Sl No',
            'Dealership',
            'Machine Model',
            'Price',
            'HSN',
            'SAC',
            'Description',
            'Unit Type',
            'Category',
            'Sub Category',
            'Tax',
            'Brand',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1    => ['font' => ['bold' => true]],
        ];
    }
}
