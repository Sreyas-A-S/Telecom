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
            // $table->unsignedBigInteger('client_id')->nullable()->after('id');   
            // The client_id column is already added in 2025_08_30_090637_create_leads_table.php
            // No action needed here to add the column.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            //  $table->dropColumn('client_id');   
            // The client_id column is handled by 2025_08_30_090637_create_leads_table.php
            // No action needed here to drop the column.
        });
    }
};
