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
        // 1. Ensure the 'Human Resources' group exists
        $group = DB::table('menu_groups')->where('name', 'Human Resources')->first();
        if (!$group) {
            $groupId = DB::table('menu_groups')->insertGetId([
                'name' => 'Human Resources',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            $groupId = $group->id;
        }

        // 2. Insert or update the 'Attendance of All' menu
        DB::table('menus')->updateOrInsert(
            ['name' => 'Attendance of All'],
            ['menu_group_id' => $groupId, 'created_at' => now(), 'updated_at' => now()]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('menus')->where('name', 'Attendance of All')->delete();
    }
};
