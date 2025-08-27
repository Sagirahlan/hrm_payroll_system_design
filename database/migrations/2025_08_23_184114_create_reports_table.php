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
        Schema::create('reports', function (Blueprint $table) {
            $table->id('report_id');
            $table->string('report_type');
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->dateTime('generated_date');
            $table->json('report_data')->nullable();
            $table->string('export_format')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('generated_by')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};

