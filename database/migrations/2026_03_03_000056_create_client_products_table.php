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
        Schema::create('client_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('product_model_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('model_series_id')->nullable()->constrained()->onDelete('set null');
            $table->date('doc')->nullable();
            $table->string('engine_model')->nullable();
            $table->foreignId('dealership_id')->nullable()->constrained()->onDelete('set null');
            $table->uuid('import_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_products');
    }
};
