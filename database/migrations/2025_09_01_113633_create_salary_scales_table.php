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
        Schema::create('salary_scales', function (Blueprint $table) {
            $table->id();
            $table->string('acronym')->unique(); // Salary Scale (Acronym)
            $table->string('full_name'); // Full Name
            $table->text('sector_coverage'); // Sector / Coverage
            $table->string('grade_levels'); // Grade Levels
            $table->string('max_retirement_age'); // Max Retirement Age
            $table->string('max_years_of_service'); // Max Years of Service
            $table->text('notes')->nullable(); // Notes / Special Provisions
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_scales');
    }
};
