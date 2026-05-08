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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade')->unique(); 
            $table->string('email')->unique(); 
            $table->timestamp('email_verified_at')->nullable();

            $table->string('password'); 
            $table->rememberToken();
            $table->string('profile_pic')->nullable();
            $table->string('designation')->nullable(); // Changed from foreignId to string
            $table->foreignId('department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->string('employee_id')->unique();
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null');
            $table->foreignId('dealership_id')->nullable()->constrained('dealerships')->onDelete('set null');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');
            $table->string('country')->nullable();
            $table->string('mobile')->nullable();
            $table->string('gender')->nullable(); 
            $table->date('joining_date')->nullable();
            $table->date('dob')->nullable();
            $table->unsignedBigInteger('reporting_to')->nullable(); 
            $table->text('address')->nullable();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->timestamp('task_started_time')->nullable();
            $table->string('task_status')->nullable();
            $table->boolean('is_tracking_on')->default(false)->nullable();
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('is_tracking_on');
        });
    }
};