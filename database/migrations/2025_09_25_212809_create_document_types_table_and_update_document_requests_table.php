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
        // Create document_types table
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Modify document_requests table
        Schema::table('document_requests', function (Blueprint $table) {
            // Drop the existing enum column
            $table->dropColumn('document_type');

            // Add the new foreign key column
            $table->foreignId('document_type_id')->nullable()->constrained('document_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_requests', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['document_type_id']);
            // Drop the column
            $table->dropColumn('document_type_id');
            // Re-add the original enum column
            $table->enum('document_type', ['NOC', 'salary_slip'])->default('NOC'); // Revert to original enum
        });

        // Drop document_types table
        Schema::dropIfExists('document_types');
    }
};