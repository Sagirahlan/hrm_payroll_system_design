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
        Schema::table('loans', function (Blueprint $table) {
            // Add total_interest field if it doesn't exist
            if (!Schema::hasColumn('loans', 'total_interest')) {
                $table->decimal('total_interest', 15, 2)->default(0)->after('principal_amount'); // Total interest amount
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['total_interest']);
        });
    }
};
