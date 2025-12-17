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
        // Update payment_type to 'Pension' for all payroll records where remarks like 'Pension for%'
        DB::table('payroll_records')
            ->where('remarks', 'like', 'Pension for%')
            ->update(['payment_type' => 'Pension']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to 'Regular' (optional, but good practice)
        DB::table('payroll_records')
            ->where('remarks', 'like', 'Pension for%')
            ->update(['payment_type' => 'Regular']);
    }
};
