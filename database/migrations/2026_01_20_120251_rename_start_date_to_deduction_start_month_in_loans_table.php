<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            // Add new deduction_start_month column as string to store month in Y-m format
            $table->string('deduction_start_month', 7)->nullable()->after('monthly_percentage');
        });

        // Migrate existing start_date data to deduction_start_month format (Y-m)
        DB::table('loans')->whereNotNull('start_date')->update([
            'deduction_start_month' => DB::raw("DATE_FORMAT(start_date, '%Y-%m')")
        ]);

        Schema::table('loans', function (Blueprint $table) {
            // Drop the old start_date column
            $table->dropColumn('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            // Recreate start_date column
            $table->date('start_date')->nullable()->after('monthly_percentage');
        });

        // Migrate deduction_start_month back to start_date (first day of the month)
        DB::table('loans')->whereNotNull('deduction_start_month')->update([
            'start_date' => DB::raw("CONCAT(deduction_start_month, '-01')")
        ]);

        Schema::table('loans', function (Blueprint $table) {
            // Drop deduction_start_month column
            $table->dropColumn('deduction_start_month');
        });
    }
};
