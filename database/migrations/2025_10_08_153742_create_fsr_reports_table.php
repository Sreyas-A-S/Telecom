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
        Schema::create('fsr_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->text('on_site_assessment')->nullable();
            $table->text('analysis_of_cause')->nullable();
            $table->text('actions_taken')->nullable();
            $table->string('payment_status')->nullable()->default('pending');
            $table->string('status')->nullable()->default('pending');
            $table->foreignId('submitted_by_user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fsr_reports');
    }
};