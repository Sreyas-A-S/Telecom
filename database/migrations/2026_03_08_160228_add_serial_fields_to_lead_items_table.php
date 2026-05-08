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
        Schema::table('lead_items', function (Blueprint $table) {
            $table->string('machine_serial_number')->nullable()->after('model_series_id');
            $table->string('engine_serial_number')->nullable()->after('machine_serial_number');
            $table->string('engine_model')->nullable()->after('engine_serial_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lead_items', function (Blueprint $table) {
            $table->dropColumn(['machine_serial_number', 'engine_serial_number', 'engine_model']);
        });
    }
};
