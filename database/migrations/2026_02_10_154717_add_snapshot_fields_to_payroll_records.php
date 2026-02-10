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
        Schema::table('payroll_records', function (Blueprint $table) {
            $table->unsignedBigInteger('step_id')->nullable()->after('grade_level_id');
            $table->unsignedBigInteger('rank_id')->nullable()->after('step_id');
            $table->unsignedBigInteger('department_id')->nullable()->after('rank_id');

            // Optional: Indexing for better search performance
            $table->index('step_id');
            $table->index('rank_id');
            $table->index('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_records', function (Blueprint $table) {
            $table->dropColumn(['step_id', 'rank_id', 'department_id']);
        });
    }
};
