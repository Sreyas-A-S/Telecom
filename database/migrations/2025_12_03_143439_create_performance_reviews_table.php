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
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained('users')->onDelete('cascade');
            $table->date('review_date');
            $table->string('review_period')->nullable();

            // Ratings and Remarks
            $table->integer('communication_skills_rating')->nullable();
            $table->text('communication_skills_remarks')->nullable();

            $table->integer('technical_knowledge_rating')->nullable();
            $table->text('technical_knowledge_remarks')->nullable();

            $table->integer('problem_solving_ability_rating')->nullable();
            $table->text('problem_solving_ability_remarks')->nullable();

            $table->integer('teamwork_collaboration_rating')->nullable();
            $table->text('teamwork_collaboration_remarks')->nullable();

            $table->integer('leadership_potential_rating')->nullable();
            $table->text('leadership_potential_remarks')->nullable();

            $table->integer('adaptability_flexibility_rating')->nullable();
            $table->text('adaptability_flexibility_remarks')->nullable();

            $table->integer('attitude_and_confidence_rating')->nullable();
            $table->text('attitude_and_confidence_remarks')->nullable();

            $table->integer('punctuality_rating')->nullable();
            $table->text('punctuality_remarks')->nullable();

            $table->integer('productivity_rating')->nullable();
            $table->text('productivity_remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_reviews');
    }
};
