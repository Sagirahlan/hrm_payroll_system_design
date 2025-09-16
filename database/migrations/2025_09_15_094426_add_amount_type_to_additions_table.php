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
            $table->string('amount_type')->default('fixed')->after('amount');
            $table->string('amount')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('additions', function (Blueprint $table) {
            $table->dropColumn('amount_type');
            $table->decimal('amount', 10, 2)->change();
        });
    }
};
