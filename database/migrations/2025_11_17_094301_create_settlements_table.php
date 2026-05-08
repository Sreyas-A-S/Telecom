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
        Schema::create('settlements', function (Blueprint $table) {
            $table->id();
            $table->string('employee_code')->unique();
            $table->string('employee_name');
            $table->integer('age')->nullable();
            $table->string('department')->nullable();
            $table->string('head_office_branch')->nullable();
            $table->string('designation')->nullable();
            $table->date('date_of_joining');
            $table->date('date_of_resignation')->nullable();
            $table->text('reason_for_resignation')->nullable();
            $table->foreignId('dealership_id')->nullable()->constrained('dealerships')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settlements');
    }
};
