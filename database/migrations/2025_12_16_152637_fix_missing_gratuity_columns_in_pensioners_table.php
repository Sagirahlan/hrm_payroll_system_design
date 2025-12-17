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
            if (!Schema::hasColumn('pensioners', 'is_gratuity_paid')) {
                $table->boolean('is_gratuity_paid')->default(false)->after('gratuity_amount');
            }
            if (!Schema::hasColumn('pensioners', 'gratuity_paid_date')) {
                $table->date('gratuity_paid_date')->nullable()->after('is_gratuity_paid');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pensioners', function (Blueprint $table) {
            if (Schema::hasColumn('pensioners', 'is_gratuity_paid')) {
                $table->dropColumn('is_gratuity_paid');
            }
            if (Schema::hasColumn('pensioners', 'gratuity_paid_date')) {
                $table->dropColumn('gratuity_paid_date');
            }
        });
    }
};
