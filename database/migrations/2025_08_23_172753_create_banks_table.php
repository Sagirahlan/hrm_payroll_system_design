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
        Schema::create('banks', function (Blueprint $table) {
            $table->id('bank_id');
            $table->string('bank_name');
            $table->string('bank_code')->nullable();
            $table->unsignedBigInteger('employee_id');
            $table->string('account_name');
            $table->string('account_no');
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
        Schema::dropIfExists('banks');
    }
};

