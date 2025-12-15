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
        Schema::create('compute_percentage', function (Blueprint $table) {
            $table->id();
            $table->integer('years_of_service')->unique();
            $table->decimal('gratuity_pct', 8, 2)->default(0);
            $table->decimal('pension_pct', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compute_percentage');
    }
};
