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
        Schema::create('pay_slips', function (Blueprint $table) {
            $table->id('payslip_id');
            $table->unsignedBigInteger('payroll_id');
            $table->unsignedBigInteger('employee_id');
            $table->date('generated_date');
            $table->string('pdf_path')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('payroll_id')->references('payroll_id')->on('payroll_records')->onDelete('cascade');
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_slips');
    }
};

