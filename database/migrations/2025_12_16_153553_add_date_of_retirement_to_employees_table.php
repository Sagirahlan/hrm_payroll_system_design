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
        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'date_of_retirement')) {
                $table->date('date_of_retirement')->nullable()->after('date_of_first_appointment');
            }
        });

        // Backfill data from retirements table
        // Ensure that we only update where there is a matching retirement record
        // Raw SQL for efficiency and simplicity in migration
        DB::statement("
            UPDATE employees e
            INNER JOIN retirements r ON e.employee_id = r.employee_id
            SET e.date_of_retirement = r.retirement_date
            WHERE e.date_of_retirement IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'date_of_retirement')) {
                $table->dropColumn('date_of_retirement');
            }
        });
    }
};
