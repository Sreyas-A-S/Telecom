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
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('job_vacancy_id');
            $table->unsignedBigInteger('referrer_id')->nullable();

            // Candidate Details
            $table->string('candidate_name');
            $table->string('email_id');
            $table->string('contact_number');
            $table->string('educational_qualification')->nullable();
            $table->string('years_of_experience')->nullable();
            $table->string('current_employer')->nullable();
            $table->string('last_current_ctc')->nullable();
            $table->string('expected_ctc')->nullable();
            $table->string('notice_period')->nullable();
            $table->string('location')->nullable();
            $table->string('post_applied_for')->nullable();

            // JSON column for custom fields & files
            $table->json('custom_form_responses')->nullable();

            $table->string('status')->default('Applied');
            $table->timestamps();

            $table->foreign('job_vacancy_id')->references('id')->on('job_vacancies')->onDelete('cascade');
            $table->foreign('referrer_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
