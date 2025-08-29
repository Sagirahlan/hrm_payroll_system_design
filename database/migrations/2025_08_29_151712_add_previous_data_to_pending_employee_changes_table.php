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
            $table->json('previous_data')->after('data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pending_employee_changes', function (Blueprint $table) {
            $table->dropColumn('previous_data');
        });
    }
};
