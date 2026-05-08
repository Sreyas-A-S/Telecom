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
        Schema::table('fsr_reports', function (Blueprint $table) {
            if (Schema::hasColumn('fsr_reports', 'parts_required')) {
                $table->dropColumn('parts_required');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fsr_reports', function (Blueprint $table) {
            // Re-add the column if rolling back, assuming it was JSON
            $table->json('parts_required')->nullable();
        });
    }
};