<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $menus = Menu::all();

        // foreach ($menus as $menu) {
        //     $permissionData = [
        //         'can_create' => false,
        //         'can_read' => false,
        //         'can_update' => false,
        //         'can_delete' => false,
        //     ];

        //     // Special handling for "Package Kits" and "Parts"
        //     if ($menu->name === 'Package Kits' || $menu->name === 'Parts') {
        //         $permissionData['can_read'] = true;
        //     }

        //     Permission::firstOrCreate(
        //         ['menu_id' => $menu->id], // Find by menu_id
        //         $permissionData
        //     );
        // }
    }
}
