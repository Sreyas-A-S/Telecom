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
        Schema::table('performance_reviews', function (Blueprint $col) {
            $col->string('review_year')->after('review_period')->nullable();
            $col->unsignedBigInteger('updated_by')->after('reviewer_id')->nullable();
            $col->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('performance_reviews', function (Blueprint $col) {
            $col->dropForeign(['updated_by']);
            $col->dropColumn(['review_year', 'updated_by']);
        });
    }
};
