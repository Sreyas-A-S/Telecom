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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('set null');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->unsignedBigInteger('product_model_id')->nullable();
            $table->foreign('product_model_id')->references('id')->on('product_models')->onDelete('set null');
            $table->foreignId('dealership_id')->nullable()->constrained('dealerships');
            $table->foreignId('service_engineer_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->foreignId('service_engineer_id_2')->nullable()->constrained('employees')->onDelete('set null');
            $table->string('requested_location')->nullable();
            $table->string('referral_id')->unique()->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
