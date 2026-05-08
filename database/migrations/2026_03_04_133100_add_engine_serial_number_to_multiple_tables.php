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
        Schema::table('leads', function (Blueprint $table) {
            $table->string('engine_serial_number')->nullable()->after('engine_model');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('engine_serial_number')->nullable()->after('engine_model');
        });

        Schema::table('client_products', function (Blueprint $table) {
            $table->string('engine_serial_number')->nullable()->after('engine_model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('engine_serial_number');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('engine_serial_number');
        });

        Schema::table('client_products', function (Blueprint $table) {
            $table->dropColumn('engine_serial_number');
        });
    }
};
