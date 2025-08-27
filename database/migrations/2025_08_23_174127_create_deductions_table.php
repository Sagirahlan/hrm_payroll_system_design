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
        Schema::create('deductions', function (Blueprint $table) {
            $table->id('deduction_id');
            $table->string('deduction_type');
            $table->decimal('amount', 10, 2);
            $table->string('deduction_period');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->unsignedBigInteger('employee_id');
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
        Schema::dropIfExists('deductions');
    }
};

