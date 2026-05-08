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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('latitude')->nullable()->after('gps_location');
            $table->string('longitude')->nullable()->after('latitude');
            $table->unsignedBigInteger('state_id')->nullable()->after('longitude');
            $table->unsignedBigInteger('district_id')->nullable()->after('state_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'state_id', 'district_id']);
        });
    }
};
