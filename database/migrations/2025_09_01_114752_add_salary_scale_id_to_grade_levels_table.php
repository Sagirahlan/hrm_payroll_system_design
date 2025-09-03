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
            $table->unsignedBigInteger('salary_scale_id')->nullable();
            $table->foreign('salary_scale_id')->references('id')->on('salary_scales')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grade_levels', function (Blueprint $table) {
            $table->dropForeign(['salary_scale_id']);
            $table->dropColumn('salary_scale_id');
        });
    }
};
