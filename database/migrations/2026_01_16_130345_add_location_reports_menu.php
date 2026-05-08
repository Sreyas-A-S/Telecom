<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure 'Monitoring' group exists or create it
        $menuGroup = \App\Models\MenuGroup::firstOrCreate(['name' => 'Monitoring']);

        // Assign 'read' permission to Admin role (assuming ID 1)
        // Check if admin role exists to be safe
        $adminRole = \App\Models\Role::where('role', 'Admin')->first();
        if (!$adminRole) {
            $adminRole = \App\Models\Role::find(1);
        }

        if ($adminRole) {
            \App\Models\Permission::create([
                'role_id' => $adminRole->id,
                'menu_id' => $menu->id,
                'can_create' => 0,
                'can_read' => 1,
                'can_update' => 0,
                'can_delete' => 0,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
