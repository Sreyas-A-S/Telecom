<?php

namespace App\Exports;

use App\Models\MenuGroup;
use App\Models\Permission;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PermissionsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents
{
    protected $roleId;
    protected $menuGroups;
    protected $groupCounts = [];

    public function __construct($roleId)
    {
        $this->roleId = $roleId;
        $this->menuGroups = MenuGroup::with(['menus'])->orderBy('id')->get();
    }

    public function collection()
    {
        $rows = collect();
        $this->groupCounts = [];

        foreach ($this->menuGroups as $group) {
            $count = 0;
            foreach ($group->menus as $menu) {
                $permission = Permission::where('role_id', $this->roleId)
                    ->where('menu_id', $menu->id)
                    ->first();

                $rows->push((object)[
                    'group_name' => $group->name,
                    'menu_name' => $menu->name,
                    'can_create' => $permission ? $permission->can_create : false,
                    'can_read' => $permission ? $permission->can_read : false,
                    'can_update' => $permission ? $permission->can_update : false,
                    'can_delete' => $permission ? $permission->can_delete : false,
                ]);
                $count++;
            }
            if ($count > 0) {
                // Store the row count for this group to merge cells later
                $this->groupCounts[] = $count;
            }
        }
        return $rows;
    }

    public function headings(): array
    {
        return [
            'Module',
            'Menu',
            'Create',
            'Read',
            'Update',
            'Delete',
        ];
    }

    public function map($row): array
    {
        return [
            $row->group_name,
            $row->menu_name,
            $row->can_create ? 'Yes' : 'No',
            $row->can_read ? 'Yes' : 'No',
            $row->can_update ? 'Yes' : 'No',
            $row->can_delete ? 'Yes' : 'No',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Row 1: Header - White Text on Blue Background
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4F81BD']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
            // Columns C-F: Centered Text
            'C:F' => [
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $rowCount = $this->collection()->count();
                $lastRow = $rowCount + 1; // +1 for header

                // Data Validation for Yes/No
                $validation = $sheet->getCell('C2')->getDataValidation();
                $validation->setType(DataValidation::TYPE_LIST);
                $validation->setErrorStyle(DataValidation::STYLE_INFORMATION);
                $validation->setAllowBlank(false);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setFormula1('"Yes,No"');

                for ($i = 2; $i <= $lastRow; $i++) {
                    $sheet->getCell("C$i")->setDataValidation(clone $validation);
                    $sheet->getCell("D$i")->setDataValidation(clone $validation);
                    $sheet->getCell("E$i")->setDataValidation(clone $validation);
                    $sheet->getCell("F$i")->setDataValidation(clone $validation);
                }

                // Apply grouping styles and merging
                $currentRow = 2;
                foreach ($this->groupCounts as $count) {
                    $endRow = $currentRow + $count - 1;

                    // Merge Module Column (A)
                    if ($count > 1) {
                        $sheet->mergeCells("A{$currentRow}:A{$endRow}");
                    }

                    // Vertical Center Alignment for Merged Module Name
                    $sheet->getStyle("A{$currentRow}:A{$endRow}")
                        ->getAlignment()
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    // Add Thick Bottom Border to separate groups
                    $sheet->getStyle("A{$endRow}:F{$endRow}")
                        ->getBorders()
                        ->getBottom()
                        ->setBorderStyle(Border::BORDER_THICK);

                    $currentRow += $count;
                }

                // Add border to the whole table
                $sheet->getStyle("A1:F{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Re-apply thick bottom border to groups (because all borders thin might override)
                $currentRow = 2;
                foreach ($this->groupCounts as $count) {
                    $endRow = $currentRow + $count - 1;
                    $sheet->getStyle("A{$endRow}:F{$endRow}")
                        ->getBorders()
                        ->getBottom()
                        ->setBorderStyle(Border::BORDER_THICK);
                    $currentRow += $count;
                }
            },
        ];
    }
}
