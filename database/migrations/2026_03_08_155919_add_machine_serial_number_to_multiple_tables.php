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
        if (!Schema::hasColumn('leads', 'machine_serial_number')) {
            Schema::table('leads', function (Blueprint $table) {
                $table->string('machine_serial_number')->nullable()->after('product_model_id');
            });
        }

        if (!Schema::hasColumn('services', 'machine_serial_number')) {
            Schema::table('services', function (Blueprint $table) {
                $table->string('machine_serial_number')->nullable()->after('product_model_id');
            });
        }

        if (!Schema::hasColumn('client_products', 'machine_serial_number')) {
            Schema::table('client_products', function (Blueprint $table) {
                $table->string('machine_serial_number')->nullable()->after('model_series_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('machine_serial_number');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('machine_serial_number');
        });

        Schema::table('client_products', function (Blueprint $table) {
            $table->dropColumn('machine_serial_number');
        });
    }
};
