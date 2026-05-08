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
        Schema::create('dealership_setting', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dealership_id')->constrained()->onDelete('cascade');
            $table->foreignId('setting_id')->constrained()->onDelete('cascade');
            $table->boolean('enabled')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dealership_setting');
    }
};
