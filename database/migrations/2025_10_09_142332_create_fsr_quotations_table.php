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
        Schema::create('fsr_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fsr_id')->constrained('fsr_reports')->onDelete('cascade');
            $table->foreignId('part_id')->constrained('parts')->onDelete('cascade');
            $table->integer('quoted_quantity');
            $table->integer('approved_quantity')->nullable();
            $table->decimal('quoted_unit_price', 10, 2);
            $table->string('status')->default('pending'); // e.g., 'pending', 'approved', 'rejected'
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fsr_quotations');
    }
};