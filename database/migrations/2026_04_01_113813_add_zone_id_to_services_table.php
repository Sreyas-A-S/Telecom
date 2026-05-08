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
        Schema::table('services', function (Blueprint $row) {
            $row->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null')->after('dealership_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $row) {
            $row->dropForeign(['zone_id']);
            $row->dropColumn('zone_id');
        });
    }
};
