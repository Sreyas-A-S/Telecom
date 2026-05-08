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
        Schema::table('interviews', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id')->unique();
        });

        // Backfill existing records - DISABLED per user request (existing records should not have links)
        /*
        $interviews = \Illuminate\Support\Facades\DB::table('interviews')->whereNull('uuid')->get();
        foreach ($interviews as $interview) {
            \Illuminate\Support\Facades\DB::table('interviews')
                ->where('id', $interview->id)
                ->update(['uuid' => (string) \Illuminate\Support\Str::uuid()]);
        }
        */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
