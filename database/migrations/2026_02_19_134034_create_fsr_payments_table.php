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
        Schema::create('fsr_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fsr_report_id')->constrained('fsr_reports')->onDelete('cascade');
            $table->decimal('amount', 15, 2);
            $table->string('payment_mode')->default('cash'); // cash, online, cheque, etc.
            $table->foreignId('collected_by_user_id')->constrained('users');
            $table->timestamp('collected_at')->useCurrent();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fsr_payments');
    }
};
