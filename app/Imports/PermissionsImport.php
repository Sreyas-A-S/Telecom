<?php

namespace App\Imports;

use App\Models\Menu;
use App\Models\Permission;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PermissionsImport implements ToCollection, WithHeadingRow
{
    protected $roleId;

    public function __construct($roleId)
    {
        $this->roleId = $roleId;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Expecting headings: module, menu, create, read, update, delete
            // Normalize keys to lowercase just in case

            $menuName = $row['menu'] ?? null;
            if (!$menuName) continue;

            $menu = Menu::where('name', $menuName)->first();
            if ($menu) {
                Permission::updateOrCreate(
                    [
                        'role_id' => $this->roleId,
                        'menu_id' => $menu->id,
                    ],
                    [
                        'can_create' => $this->parseBoolean($row['create'] ?? false),
                        'can_read'   => $this->parseBoolean($row['read'] ?? false),
                        'can_update' => $this->parseBoolean($row['update'] ?? false),
                        'can_delete' => $this->parseBoolean($row['delete'] ?? false),
                    ]
                );
            }
        }
    }

    private function parseBoolean($value)
    {
        if (is_bool($value)) return $value;
        $value = trim($value); // Do not lowercase immediately, check unicode first
        // Check for Unicode checkmarks or standard boolean strings
        // U+2713 (✓), U+2714 (✔)
        if ($value === '✔' || $value === '✓') return true;

        $value = strtolower($value);
        return in_array($value, ['1', 'true', 'yes', 'y', 'on']);
    }
}
