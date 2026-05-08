<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Get the IDs of the groups
        $hrGroup = DB::table('menu_groups')->where('name', 'Human Resources')->first();
        $monitoringGroup = DB::table('menu_groups')->where('name', 'Monitoring')->first();

        if ($hrGroup && $monitoringGroup) {
            // 2. Find the 'Subordinates Timeline' menu
            $menu = DB::table('menus')->where('name', 'Subordinates Timeline')->first();

            if ($menu && $menu->menu_group_id == $hrGroup->id) {
                // 3. Move it to Monitoring
                DB::table('menus')
                    ->where('id', $menu->id)
                    ->update(['menu_group_id' => $monitoringGroup->id, 'updated_at' => now()]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Optional: Move back to HR if needed, but usually down() should revert the up()
        $hrGroup = DB::table('menu_groups')->where('name', 'Human Resources')->first();
        $monitoringGroup = DB::table('menu_groups')->where('name', 'Monitoring')->first();

        if ($hrGroup && $monitoringGroup) {
            $menu = DB::table('menus')->where('name', 'Subordinates Timeline')->first();

            if ($menu && $menu->menu_group_id == $monitoringGroup->id) {
                DB::table('menus')
                    ->where('id', $menu->id)
                    ->update(['menu_group_id' => $hrGroup->id, 'updated_at' => now()]);
            }
        }
    }
};
