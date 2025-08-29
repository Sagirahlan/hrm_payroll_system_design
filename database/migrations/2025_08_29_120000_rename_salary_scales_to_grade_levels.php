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
        Schema::rename('salary_scales', 'grade_levels');

        Schema::table('grade_levels', function (Blueprint $table) {
            $table->renameColumn('scale_id', 'id');
            $table->renameColumn('scale_name', 'name');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('scale_id', 'grade_level_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->renameColumn('grade_level_id', 'scale_id');
        });

        Schema::table('grade_levels', function (Blueprint $table) {
            $table->renameColumn('id', 'scale_id');
            $table->renameColumn('name', 'scale_name');
        });

        Schema::rename('grade_levels', 'salary_scales');
    }
};
