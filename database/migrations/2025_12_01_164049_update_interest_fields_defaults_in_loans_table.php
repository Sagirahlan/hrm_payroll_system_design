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
        // Since the defaults are already set in previous migrations to 0,
        // we don't need to make any schema changes.
        // This migration primarily serves as documentation that interest rate
        // and total interest are no longer collected in the form but are kept
        // in the database with default values of 0.

        // We'll update any existing records that might have NULL values to 0
        \DB::statement("UPDATE loans SET interest_rate = 0 WHERE interest_rate IS NULL");
        \DB::statement("UPDATE loans SET total_interest = 0 WHERE total_interest IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No significant changes to revert since we're just updating values, not schema
    }
};
