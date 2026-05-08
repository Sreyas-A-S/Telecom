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
        if (!Schema::hasColumn('user_gps_traces', 'vehicle_type')) {
            Schema::table('user_gps_traces', function (Blueprint $table) {
                $table->string('vehicle_type')->nullable()->after('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('user_gps_traces', 'vehicle_type')) {
            Schema::table('user_gps_traces', function (Blueprint $table) {
                $table->dropColumn('vehicle_type');
            });
        }
    }
};
