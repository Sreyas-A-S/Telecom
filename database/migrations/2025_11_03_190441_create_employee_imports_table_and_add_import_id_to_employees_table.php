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
        Schema::create('employee_imports', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->timestamps();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('import_id')->nullable()->after('id');
            $table->foreign('import_id')->references('id')->on('employee_imports')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['import_id']);
            $table->dropColumn('import_id');
        });

        Schema::dropIfExists('employee_imports');
    }
};
