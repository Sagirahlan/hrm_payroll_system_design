<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds unique constraints on employee_id for banks and next_of_kin tables
     * to prevent duplicate records per employee. Cleans up existing duplicates first.
     */
    public function up(): void
    {
        // Clean up duplicate bank records — keep only the most recent one per employee
        $duplicateBanks = DB::table('banks')
            ->select('employee_id')
            ->groupBy('employee_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('employee_id');

        foreach ($duplicateBanks as $employeeId) {
            $latestBankId = DB::table('banks')
                ->where('employee_id', $employeeId)
                ->orderByDesc('updated_at')
                ->orderByDesc('bank_id')
                ->value('bank_id');

            DB::table('banks')
                ->where('employee_id', $employeeId)
                ->where('bank_id', '!=', $latestBankId)
                ->delete();
        }

        // Clean up duplicate next_of_kin records — keep only the most recent one per employee
        $duplicateKins = DB::table('next_of_kin')
            ->select('employee_id')
            ->groupBy('employee_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('employee_id');

        foreach ($duplicateKins as $employeeId) {
            $latestKinId = DB::table('next_of_kin')
                ->where('employee_id', $employeeId)
                ->orderByDesc('updated_at')
                ->orderByDesc('kin_id')
                ->value('kin_id');

            DB::table('next_of_kin')
                ->where('employee_id', $employeeId)
                ->where('kin_id', '!=', $latestKinId)
                ->delete();
        }

        // Now add unique constraints
        Schema::table('banks', function (Blueprint $table) {
            $table->unique('employee_id', 'banks_employee_id_unique');
        });

        Schema::table('next_of_kin', function (Blueprint $table) {
            $table->unique('employee_id', 'next_of_kin_employee_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->dropUnique('banks_employee_id_unique');
        });

        Schema::table('next_of_kin', function (Blueprint $table) {
            $table->dropUnique('next_of_kin_employee_id_unique');
        });
    }
};
