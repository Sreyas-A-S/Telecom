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
        Schema::create('calls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caller_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('receiver_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('external_number')->nullable();
            $table->enum('status', ['ringing', 'active', 'ended', 'missed', 'forwarded'])->default('ringing');
            $table->enum('direction', ['inbound', 'outbound']);
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->string('recording_url')->nullable();
            $table->string('call_sid')->nullable()->comment('Provider unique ID (Agora/Twilio)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calls');
    }
};
