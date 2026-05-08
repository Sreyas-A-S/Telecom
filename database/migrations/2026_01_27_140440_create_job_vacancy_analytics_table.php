<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('job_vacancy_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_vacancy_id')->constrained('job_vacancies')->onDelete('cascade');
            $table->unsignedBigInteger('user_id')->nullable()->comment('User who performed the action (e.g., copied link)');
            $table->unsignedBigInteger('referrer_id')->nullable()->comment('User who referred the visitor (for views)');
            $table->string('action'); // 'view', 'copy_link'
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_vacancy_analytics');
    }
};
