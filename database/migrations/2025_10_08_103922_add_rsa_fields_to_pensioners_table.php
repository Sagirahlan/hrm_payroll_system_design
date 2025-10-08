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
        Schema::table('pensioners', function (Blueprint $table) {
            $table->decimal('rsa_balance_at_retirement', 15, 2)->default(0)->after('pension_amount');
            $table->decimal('lump_sum_amount', 15, 2)->default(0)->after('rsa_balance_at_retirement');
            $table->string('pension_type')->default('PW')->after('lump_sum_amount'); // 'PW' for Programmed Withdrawal, 'Annuity'
            $table->integer('expected_lifespan_months')->default(240)->after('pension_type'); // Default to 20 years (240 months) for PW calculation
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pensioners', function (Blueprint $table) {
            $table->dropColumn([
                'rsa_balance_at_retirement',
                'lump_sum_amount',
                'pension_type',
                'expected_lifespan_months'
            ]);
        });
    }
};
