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
        Schema::table('grade_levels', function (Blueprint $table) {
            $table->integer('retirement_age')->nullable();
            $table->integer('max_years_of_service')->nullable();
            $table->string('salary_scale_acronym')->nullable();
            $table->string('salary_scale_full_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grade_levels', function (Blueprint $table) {
            $table->dropColumn(['retirement_age', 'max_years_of_service', 'salary_scale_acronym', 'salary_scale_full_name']);
        });
    }
};