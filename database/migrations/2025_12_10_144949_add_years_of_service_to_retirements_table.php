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
        Schema::table('retirements', function (Blueprint $table) {
            $table->decimal('years_of_service', 8, 2)->nullable()->after('retire_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retirements', function (Blueprint $table) {
            $table->dropColumn('years_of_service');
        });
    }
};
