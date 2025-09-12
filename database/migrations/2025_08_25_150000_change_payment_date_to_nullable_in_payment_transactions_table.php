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
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->date('payment_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('payment_transactions')->whereNull('payment_date')->update(['payment_date' => now()]);

        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->date('payment_date')->nullable(false)->change();
        });
    }
};
