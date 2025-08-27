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
            $table->unsignedBigInteger('deduction_type_id')->nullable()->after('deduction_type');
            $table->foreign('deduction_type_id')->references('id')->on('deduction_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deductions', function (Blueprint $table) {
            $table->dropForeign(['deduction_type_id']);
            $table->dropColumn('deduction_type_id');
        });
    }
};