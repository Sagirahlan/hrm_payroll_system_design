<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            UPDATE payment_transactions pt 
            JOIN payroll_records pr ON pt.payroll_id = pr.payroll_id 
            SET pt.status = pr.status
            WHERE pt.status IS NULL 
               OR pt.status = "pending" 
               OR pt.status = "successful"
               OR pt.status = "failed"
        ');
    }

    public function down(): void
    {
        // No rollback needed - this is a one-time data sync
    }
};
