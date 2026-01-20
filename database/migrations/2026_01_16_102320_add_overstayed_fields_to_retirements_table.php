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
            $table->date('expected_retirement_date')->nullable()->after('retirement_date');
            $table->integer('overstayed_days')->default(0)->after('expected_retirement_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('retirements', function (Blueprint $table) {
            $table->dropColumn(['expected_retirement_date', 'overstayed_days']);
        });
    }
};
