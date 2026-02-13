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
        // Add 'progressive' to the calculation_type enum
        DB::statement("ALTER TABLE deduction_types MODIFY COLUMN calculation_type ENUM('fixed_amount', 'percentage', 'progressive')");

        // Update PAYE to use progressive calculation type
        DB::table('deduction_types')
            ->where('code', 'PAYE')
            ->update(['calculation_type' => 'progressive']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert PAYE back to percentage
        DB::table('deduction_types')
            ->where('code', 'PAYE')
            ->update(['calculation_type' => 'percentage']);

        // Remove 'progressive' from enum
        DB::statement("ALTER TABLE deduction_types MODIFY COLUMN calculation_type ENUM('fixed_amount', 'percentage')");
    }
};
