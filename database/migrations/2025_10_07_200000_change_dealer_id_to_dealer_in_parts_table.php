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
        Schema::table('parts', function (Blueprint $table) {
            $table->dropForeign('parts_dealer_id_foreign');
            $table->renameColumn('dealer_id', 'dealer');
        });

        Schema::table('parts', function (Blueprint $table) {
            $table->string('dealer')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropColumn('dealer'); // Drop the 'dealer' string column
        });

        Schema::table('parts', function (Blueprint $table) {
            $table->foreignId('dealer_id')->nullable()->constrained('employees')->onDelete('set null'); // Re-add the original foreignId column
        });
    }
};
