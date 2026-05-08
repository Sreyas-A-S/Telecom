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
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->string('post_applied_for')->nullable();
            $table->foreignId('dealership_id')->nullable()->constrained('dealerships')->onDelete('set null');
            $table->string('candidate_name')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email_id')->nullable();
            $table->string('educational_qualification')->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->string('current_employer')->nullable();
            $table->decimal('last_current_ctc', 10, 2)->nullable();
            $table->decimal('expected_ctc', 10, 2)->nullable();
            $table->string('notice_period')->nullable();
            $table->integer('communication_skills_rating')->nullable();
            $table->text('communication_skills_remarks')->nullable();
            $table->integer('technical_knowledge_rating')->nullable();
            $table->text('technical_knowledge_remarks')->nullable();
            $table->integer('problem_solving_ability_rating')->nullable();
            $table->text('problem_solving_ability_remarks')->nullable();
            $table->integer('knowledge_of_heavy_equipments_rating')->nullable();
            $table->text('knowledge_of_heavy_equipments_remarks')->nullable();
            $table->integer('relevant_work_experience_rating')->nullable();
            $table->text('relevant_work_experience_remarks')->nullable();
            $table->integer('attitude_and_confidence_rating')->nullable();
            $table->text('attitude_and_confidence_remarks')->nullable();
            $table->integer('adaptability_flexibility_rating')->nullable();
            $table->text('adaptability_flexibility_remarks')->nullable();
            $table->integer('teamwork_collaboration_rating')->nullable();
            $table->text('teamwork_collaboration_remarks')->nullable();
            $table->integer('leadership_potential_rating')->nullable();
            $table->text('leadership_potential_remarks')->nullable();
            $table->integer('willingness_to_travel_relocate_rating')->nullable();
            $table->text('willingness_to_travel_relocate_remarks')->nullable();
            $table->string('interviewer_recommendation')->nullable();
            $table->decimal('salary_offered', 10, 2)->nullable();
            $table->decimal('da', 10, 2)->nullable();
            $table->decimal('ta', 10, 2)->nullable();
            $table->string('location')->nullable();
            $table->string('category')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->dropForeign(['dealership_id']);
            $table->dropColumn('dealership_id');
        });
        Schema::dropIfExists('interviews');
    }
};
