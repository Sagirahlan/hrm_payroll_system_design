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
        Schema::create('biometric_data', function (Blueprint $table) {
            $table->id('biometric_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('nin')->nullable();
            $table->text('fingerprint_data')->nullable();
            $table->string('verification_status')->default('pending');
            $table->timestamp('verification_date')->nullable();
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biometric_data');
    }
};

