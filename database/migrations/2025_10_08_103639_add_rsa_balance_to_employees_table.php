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
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('rsa_balance', 15, 2)->default(0)->after('expected_retirement_date');
            $table->decimal('pfa_contribution_rate', 8, 2)->default(18.00)->after('rsa_balance'); // Total contribution rate: 18% (8% employee + 10% employer)
            $table->string('pension_administrator')->nullable()->after('pfa_contribution_rate');
            $table->string('rsa_pin')->nullable()->after('pension_administrator'); // RSA account number/PIN
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'rsa_balance',
                'pfa_contribution_rate',
                'pension_administrator',
                'rsa_pin'
            ]);
        });
    }
};
