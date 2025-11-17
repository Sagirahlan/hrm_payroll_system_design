<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Foreign keys were already added in the create_leaves_table migration
        // This migration exists for reference only
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No action needed as foreign keys will be dropped when the table is dropped
    }
};