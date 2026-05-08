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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('marital_status')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mother_name')->nullable();
            $table->string('spouse_name')->nullable();
            $table->string('shirt_size')->nullable();
            $table->string('tshirt_size')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('pf_no')->nullable();
            $table->string('esi_no')->nullable();
            $table->string('lwf_no')->nullable();
            $table->string('aadhar_no')->nullable();
            $table->string('pan_no')->nullable();
            $table->string('branch')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'marital_status',
                'emergency_contact',
                'father_name',
                'mother_name',
                'spouse_name',
                'shirt_size',
                'tshirt_size',
                'blood_group',
                'bank_name',
                'account_number',
                'ifsc_code',
                'pf_no',
                'esi_no',
                'lwf_no',
                'aadhar_no',
                'pan_no',
                'branch',
            ]);
        });
    }
};
