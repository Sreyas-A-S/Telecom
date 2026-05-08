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
            $table->string('contact_person')->nullable()->after('contact_info');
            $table->date('doc')->nullable()->after('model_series_id'); // Date of Commissioning
            $table->date('failure_date')->nullable()->after('doc');
            $table->string('failure_hmr')->nullable()->after('failure_date'); // Failure Hour Meter Reading
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['contact_person', 'doc', 'failure_date', 'failure_hmr']);
        });
    }
};
