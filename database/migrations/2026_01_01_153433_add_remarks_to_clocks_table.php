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
        Schema::table('clocks', function (Blueprint $table) {
            $table->text('remarks')->nullable()->after('clock_out_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clocks', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
};
