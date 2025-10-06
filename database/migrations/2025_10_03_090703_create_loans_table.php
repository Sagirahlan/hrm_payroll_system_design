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
        // This migration is empty because the loans table was already created with the correct structure
        // in the 2025_10_03_085914_create_loans_table.php migration
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is empty because the loans table was already created with the correct structure
        // in the 2025_10_03_085914_create_loans_table.php migration
    }
};
