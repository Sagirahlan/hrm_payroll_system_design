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
        Schema::create('loan_deductions', function (Blueprint $table) {
            $table->id('loan_deduction_id');
            $table->unsignedBigInteger('loan_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('payroll_id')->nullable(); // Link to specific payroll record
            $table->decimal('amount_deducted', 10, 2);
            $table->decimal('remaining_balance', 15, 2);
            $table->integer('month_number'); // Which month of the loan repayment
            $table->string('payroll_month'); // Format: YYYY-MM
            $table->date('deduction_date');
            $table->string('status')->default('completed'); // completed, pending
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('loan_id')->references('loan_id')->on('loans')->onDelete('cascade');
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->foreign('payroll_id')->references('payroll_id')->on('payroll_records')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_deductions');
    }
};
