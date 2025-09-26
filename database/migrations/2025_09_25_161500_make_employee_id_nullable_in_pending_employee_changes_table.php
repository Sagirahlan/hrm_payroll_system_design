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
        Schema::table('pending_employee_changes', function (Blueprint $table) {
            // Foreign key constraint must be dropped before modifying the column
            $table->dropForeign(['employee_id']);
            
            // Now, change the column to be nullable
            $table->unsignedBigInteger('employee_id')->nullable()->change();
            
            // Re-add the foreign key constraint
            $table->foreign('employee_id')
                  ->references('employee_id')
                  ->on('employees')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pending_employee_changes', function (Blueprint $table) {
            // Drop the foreign key
            $table->dropForeign(['employee_id']);

            // Change the column back to not nullable
            $table->unsignedBigInteger('employee_id')->nullable(false)->change();

            // Re-add the foreign key
            $table->foreign('employee_id')
                  ->references('employee_id')
                  ->on('employees')
                  ->onDelete('cascade');
        });
    }
};