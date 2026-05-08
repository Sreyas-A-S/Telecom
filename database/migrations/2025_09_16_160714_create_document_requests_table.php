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
        Schema::create('document_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('remarks')->nullable();
            $table->enum('document_type', ['NOC', 'salary_slip']);
            $table->enum('status', ['pending', 'approved', 'rejected', 'processed', 'forwarded', 'approved and forwarded'])->default('pending');
            $table->date('requested_date');
            $table->foreignId('forwarded_to_employee_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_requests');
    }
};
