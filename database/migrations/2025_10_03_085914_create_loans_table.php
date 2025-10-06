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
        Schema::create('loans', function (Blueprint $table) {
            $table->id('loan_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('loan_type');
            $table->decimal('principal_amount', 15, 2);
            $table->decimal('monthly_deduction', 15, 2);
            $table->integer('total_months');
            $table->integer('remaining_months');
            $table->decimal('monthly_percentage', 5, 2)->nullable(); // Optional: if deduction is based on percentage of salary
            $table->date('start_date');
            $table->date('end_date')->nullable(); // Will be calculated based on total_months
            $table->decimal('total_repaid', 15, 2)->default(0);
            $table->decimal('remaining_balance', 15, 2);
            $table->string('status')->default('active'); // active, completed, cancelled
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
