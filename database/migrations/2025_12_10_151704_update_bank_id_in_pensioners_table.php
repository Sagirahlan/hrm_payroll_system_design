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
            $table->dropForeign(['bank_id']);
            $table->foreign('bank_id')->references('id')->on('bank_list')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pensioners', function (Blueprint $table) {
            $table->dropForeign(['bank_id']);
            $table->foreign('bank_id')->references('bankid')->on('banks')->onDelete('set null');
        });
    }
};