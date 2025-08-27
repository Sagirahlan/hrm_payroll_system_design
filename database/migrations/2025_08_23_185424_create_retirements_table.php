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
        Schema::create('retirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->date('retirement_date');
            $table->date('notification_date');
            $table->decimal('gratuity_amount', 10, 2)->nullable();
            $table->string('status')->default('pending');
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
        Schema::dropIfExists('retirements');
    }
};

