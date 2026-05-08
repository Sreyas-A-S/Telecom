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
            $table->decimal('image_latitude', 10, 7)->nullable()->after('image_path');
            $table->decimal('image_longitude', 10, 7)->nullable()->after('image_latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_gps_traces', function (Blueprint $table) {
            $table->dropColumn(['image_latitude', 'image_longitude']);
        });
    }
};
