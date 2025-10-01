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
        if (Schema::hasColumn('payroll_records', 'gross_salary')) {
            Schema::table('payroll_records', function (Blueprint $table) {
                $table->dropColumn('gross_salary');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_records', function (Blueprint $table) {
            $table->decimal('gross_salary', 10, 2)->default(0)->after('basic_salary');
        });
    }
};
