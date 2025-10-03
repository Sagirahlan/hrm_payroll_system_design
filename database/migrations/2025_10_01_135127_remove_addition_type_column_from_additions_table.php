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
        Schema::table('additions', function (Blueprint $table) {
            // Remove the old addition_type column since we're using addition_type_id now
            $table->dropColumn('addition_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('additions', function (Blueprint $table) {
            $table->string('addition_type')->nullable();
        });
    }
};
