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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id('transaction_id');
            $table->unsignedBigInteger('payroll_id');
            $table->unsignedBigInteger('employee_id');
            $table->decimal('amount', 10, 2);
            $table->string('bank_code')->nullable();
            $table->string('account_name');
            $table->string('account_number');
            $table->date('payment_date');
            $table->string('status')->default('pending');
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->foreign('payroll_id')->references('payroll_id')->on('payroll_records')->onDelete('cascade');
            $table->foreign('approver_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};

