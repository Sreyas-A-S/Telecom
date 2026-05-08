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
        Schema::table('user_gps_traces', function (Blueprint $table) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE user_gps_traces MODIFY COLUMN status ENUM('active', 'inactive', 'halt') DEFAULT 'active'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_gps_traces', function (Blueprint $table) {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE user_gps_traces MODIFY COLUMN status ENUM('active', 'inactive') DEFAULT 'active'");
        });
    }
};
