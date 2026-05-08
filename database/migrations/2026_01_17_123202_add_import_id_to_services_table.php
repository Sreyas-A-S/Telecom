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
            $table->string('import_id')->nullable()->after('id');
            // Foreign key is optional but good practice, though user might not want constraints if they delete logs but keep services.
            // EmployeeImportController deletes services when undoing import.
            // I'll skip FK constraint to be safe and simple like EmployeeImport which seemingly doesn't enforce it (check if needed).
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('import_id');
        });
    }
};
