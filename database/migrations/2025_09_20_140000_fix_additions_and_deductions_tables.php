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
        Schema::table('deductions', function (Blueprint $table) {
            if (!Schema::hasColumn('deductions', 'amount')) {
                $table->decimal('amount', 10, 2)->after('deduction_type');
            }
            if (!Schema::hasColumn('deductions', 'amount_type')) {
                $table->string('amount_type')->nullable()->after('amount');
            }
        });

        Schema::table('additions', function (Blueprint $table) {
            if (!Schema::hasColumn('additions', 'amount')) {
                $table->decimal('amount', 10, 2)->after('addition_type');
            }
            if (!Schema::hasColumn('additions', 'amount_type')) {
                $table->string('amount_type')->nullable()->after('amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deductions', function (Blueprint $table) {
            if (Schema::hasColumn('deductions', 'amount')) {
                $table->dropColumn('amount');
            }
            if (Schema::hasColumn('deductions', 'amount_type')) {
                $table->dropColumn('amount_type');
            }
        });

        Schema::table('additions', function (Blueprint $table) {
            if (Schema::hasColumn('additions', 'amount')) {
                $table->dropColumn('amount');
            }
            if (Schema::hasColumn('additions', 'amount_type')) {
                $table->dropColumn('amount_type');
            }
        });
    }
};
