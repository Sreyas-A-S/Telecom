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
        \Illuminate\Support\Facades\DB::table('interviews')->update(['uuid' => null]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a destructive operation that cannot be easily reversed without backups or regeneration logic.
        // We could regenerate UUIDs for all, provided that's the desired state.
        $interviews = \Illuminate\Support\Facades\DB::table('interviews')->whereNull('uuid')->get();
        foreach ($interviews as $interview) {
            \Illuminate\Support\Facades\DB::table('interviews')
                ->where('id', $interview->id)
                ->update(['uuid' => (string) \Illuminate\Support\Str::uuid()]);
        }
    }
};
