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
        Schema::table('additions', function (Blueprint $table) {
            $table->dropColumn('amount');
            $table->dropColumn('amount_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('additions', function (Blueprint $table) {
            $table->string('amount');
            $table->string('amount_type')->default('fixed');
        });
    }
};
