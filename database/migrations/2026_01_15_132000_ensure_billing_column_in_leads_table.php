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
        // Check if billing column exists before adding it
        if (!Schema::hasColumn('leads', 'billing')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->string('billing')->nullable()->after('stage');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('billing');
        });
    }
};
