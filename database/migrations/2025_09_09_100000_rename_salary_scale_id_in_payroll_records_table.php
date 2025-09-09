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
            // Drop the old foreign key
            $table->dropForeign(['salary_scale_id']);

            // Rename the column
            $table->renameColumn('salary_scale_id', 'grade_level_id');

            // Add the new foreign key
            $table->foreign('grade_level_id')
                  ->references('id')
                  ->on('grade_levels')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_records', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['grade_level_id']);

            // Rename the column back
            $table->renameColumn('grade_level_id', 'salary_scale_id');

            // Add the old foreign key back
            $table->foreign('salary_scale_id')
                  ->references('id')
                  ->on('grade_levels')
                  ->onDelete('set null');
        });
    }
};
