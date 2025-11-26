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
            $table->date('probation_start_date')->nullable();
            $table->date('probation_end_date')->nullable();
            $table->boolean('on_probation')->default(false);
            $table->string('probation_status')->default('pending'); // pending, approved, rejected
            $table->text('probation_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'probation_start_date',
                'probation_end_date',
                'on_probation',
                'probation_status',
                'probation_notes'
            ]);
        });
    }
};
