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
        Schema::table('services', function (Blueprint $table) {
            $table->string('machine_status')->nullable()->after('referral_id');
            $table->string('type_of_service')->nullable()->after('machine_status');
            $table->string('contact_info')->nullable()->after('type_of_service');

            $table->foreignId('model_series_id')->nullable()->constrained('model_series')->onDelete('set null')->after('product_model_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['model_series_id']);
            $table->dropColumn('model_series_id');
            $table->dropColumn('machine_status');
            $table->dropColumn('type_of_service');
            $table->dropColumn('contact_info');
        });
    }
};
