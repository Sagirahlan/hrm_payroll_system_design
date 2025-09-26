<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all existing payroll records that are 'Pending' to 'Pending Review'
        // This is to start the new approval workflow from the beginning
        DB::table('payroll_records')
            ->where('status', 'Pending')
            ->update(['status' => 'Pending Review']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the status changes back to original
        DB::table('payroll_records')
            ->where('status', 'Pending Review')
            ->update(['status' => 'Pending']);
    }
};
