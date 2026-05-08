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
        Schema::table('package_kits', function (Blueprint $table) {
            $table->foreignId('dealership_id')->nullable()->constrained('dealerships')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('package_kits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('dealership_id');
        });
    }
};
