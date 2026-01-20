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
        Schema::table('pensioners', function (Blueprint $table) {
            $table->date('expected_retirement_date')->nullable()->after('date_of_retirement');
            $table->integer('overstayed_days')->default(0)->after('expected_retirement_date');
            $table->decimal('overstayed_deduction_amount', 15, 2)->default(0)->after('overstayed_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pensioners', function (Blueprint $table) {
            $table->dropColumn(['expected_retirement_date', 'overstayed_days', 'overstayed_deduction_amount']);
        });
    }
};
