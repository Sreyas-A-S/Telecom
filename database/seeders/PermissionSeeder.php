<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission; // Import the Permission model

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $permissions = [
        //     'view_roles',
        //     'create_roles',
        //     'edit_roles',
        //     'delete_roles',
        //     'assign_permissions',
        //     'view_users',
        //     'create_users',
        //     'edit_users',
        //     'delete_users',
        //     'view_package_kits',
        //     'view_parts',
        // ];

        // foreach ($permissions as $permission) {
        //     Permission::firstOrCreate(['name' => $permission]);
        // }
    }
}