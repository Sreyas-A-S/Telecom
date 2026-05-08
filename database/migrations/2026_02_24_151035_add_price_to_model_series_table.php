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
        Schema::table('model_series', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->nullable()->after('product_model_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('model_series', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
};
