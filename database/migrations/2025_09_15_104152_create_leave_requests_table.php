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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('leave_type');
            $table->datetime('start_date');
            $table->datetime('end_date')->nullable();
            $table->string('duration')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'cancelled by admin', 'approved and forwarded'])->default('pending');
            $table->string('attachment')->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('forwarded_to_employee_id')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_compensatory')->default(false);
            $table->date('compensatory_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['forwarded_to_employee_id']);
            $table->dropColumn('forwarded_to_employee_id');
        });
        Schema::dropIfExists('leave_requests');
    }
};
