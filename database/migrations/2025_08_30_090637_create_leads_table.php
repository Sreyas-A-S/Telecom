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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable(); // Added client_id, removed foreign key constraint
            $table->string('salutation')->nullable();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('location')->nullable(); // Changed from address
            $table->string('map_location')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->foreignId('dealership_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('agent_type')->nullable();
            $table->unsignedBigInteger('agent_id')->nullable();
            $table->decimal('lead_value', 10, 2)->nullable(); // Assuming 10 total digits, 2 after decimal
            $table->boolean('allow_follow_up')->default(false);
            $table->string('status')->default('pending');
            $table->integer('chance_of_success')->nullable();
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade'); // Assuming product_id can be nullable
            $table->foreignId('product_model_id')->nullable()->constrained()->onDelete('cascade'); // Added product_model_id
            $table->integer('quantity')->nullable(); // Added quantity
            $table->string('financier')->nullable(); 
            $table->string('type')->nullable(); 
            $table->string('login_status')->nullable(); 
            $table->string('stage')->nullable(); 
            $table->string('billing')->nullable(); 
            $table->text('remarks')->nullable(); 
            $table->index(['agent_type', 'agent_id']);
            $table->foreignId('lead_source_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('lead_category_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('last_status_before_conversion')->nullable(); // New column to store lead status before conversion
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('last_status_before_conversion'); // Drop the new column
            $table->dropColumn('map_location');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });
        Schema::dropIfExists('leads');
    }
};