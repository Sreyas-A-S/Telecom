<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure the 'Monitoring' group exists (for Timeline)
        $monitoringGroup = DB::table('menu_groups')->where('name', 'Monitoring')->first();
        if (!$monitoringGroup) {
            $monitoringGroupId = DB::table('menu_groups')->insertGetId([
                'name' => 'Monitoring',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            $monitoringGroupId = $monitoringGroup->id;
        }

        // 2. Ensure the 'Reports' group exists (for Task Reports)
        $reportsGroup = DB::table('menu_groups')->where('name', 'Reports')->first();
        if (!$reportsGroup) {
            $reportsGroupId = DB::table('menu_groups')->insertGetId([
                'name' => 'Reports',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            $reportsGroupId = $reportsGroup->id;
        }

        // 3. Insert or update the 'Timeline of All' menu
        DB::table('menus')->updateOrInsert(
            ['name' => 'Timeline of All'],
            ['menu_group_id' => $monitoringGroupId, 'created_at' => now(), 'updated_at' => now()]
        );

        // 4. Insert or update the 'Task Report of All' menu
        DB::table('menus')->updateOrInsert(
            ['name' => 'Task Report of All'],
            ['menu_group_id' => $reportsGroupId, 'created_at' => now(), 'updated_at' => now()]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('menus')->whereIn('name', ['Timeline of All', 'Task Report of All'])->delete();
    }
};
