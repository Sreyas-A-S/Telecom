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
        Schema::table('tasks', function (Blueprint $table) {
            // Drop the old foreign key referencing users
            $table->dropForeign('tasks_assigned_to_foreign');

            // Add the new foreign key referencing employees
            $table->foreign('assigned_to')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['assigned_to']);

            // Re-add the old foreign key referencing users
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }
};
