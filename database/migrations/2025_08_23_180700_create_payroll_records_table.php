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
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id('payroll_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('salary_scale_id')->nullable();
            $table->decimal('basic_salary', 10, 2);
            $table->string('status')->default('pending');
            $table->decimal('total_additions', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            $table->decimal('net_salary', 10, 2);
            $table->date('payment_date')->nullable();
            $table->date('payroll_month');
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->foreign('salary_scale_id')->references('scale_id')->on('salary_scales')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
    }
};

