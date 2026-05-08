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
        Schema::create('loss_orders', function (Blueprint $table) {
            $table->id();
            $table->string('month');
            $table->unsignedBigInteger('dealership_id');
            $table->unsignedBigInteger('selected_dealership_id')->nullable();
            $table->string('product_name');
            $table->decimal('tonnage', 8, 2)->nullable();
            $table->string('product_model_name')->nullable();
            $table->string('model_series_name')->nullable();
            $table->string('customer')->nullable();
            $table->string('segment')->nullable();
            $table->string('application')->nullable();
            $table->string('financier')->nullable();
            $table->string('district')->nullable();
            $table->string('category')->nullable();
            $table->string('participation')->nullable();
            $table->text('reasons_for_loss')->nullable();
            $table->text('remarks')->nullable();
            $table->string('engineer_name')->nullable();
            $table->timestamps();

            $table->foreign('dealership_id')->references('id')->on('dealerships')->onDelete('cascade');
            $table->foreign('selected_dealership_id')->references('id')->on('dealerships')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loss_orders');
    }
};
