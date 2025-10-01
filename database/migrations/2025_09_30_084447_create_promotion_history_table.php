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
        Schema::create('promotion_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('promotion_type')->default('promotion'); // promotion or demotion
            $table->string('previous_grade_level')->nullable();
            $table->string('new_grade_level')->nullable();
            $table->string('previous_step')->nullable();
            $table->string('new_step')->nullable();
            $table->date('promotion_date');
            $table->date('effective_date');
            $table->string('approving_authority')->nullable();
            $table->text('reason')->nullable();
            $table->string('status')->default('approved'); // approved, pending, rejected
            $table->unsignedBigInteger('created_by')->nullable(); // User ID of who created the record
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotion_history');
    }
};
