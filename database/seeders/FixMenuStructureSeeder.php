<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Menu;
use App\Models\MenuGroup;
use App\Models\Permission;

class FixMenuStructureSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Permission::truncate();
        Menu::truncate();
        MenuGroup::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->call(MenuGroupSeeder::class);
        $this->call(MenuSeeder::class);
        $this->call(MenuPermissionSeeder::class);
    }
}
