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
        // Update payment_type for 'Regular' records based on employee appointment type
        // Assume 'Contract' appointment type has name 'Contract'.
        // If not 'Contract', assume 'Permanent'.
        
        // We use a raw query update with join
        DB::statement("
            UPDATE payroll_records p
            INNER JOIN employees e ON p.employee_id = e.employee_id
            LEFT JOIN appointment_types at ON e.appointment_type_id = at.id
            SET p.payment_type = CASE 
                WHEN at.name = 'Contract' THEN 'Contract'
                ELSE 'Permanent'
            END
            WHERE p.payment_type = 'Regular'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert Permanent/Contract back to Regular
        DB::table('payroll_records')
            ->whereIn('payment_type', ['Permanent', 'Contract'])
            ->update(['payment_type' => 'Regular']);
    }
};
