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
        Schema::create('wards', function (Blueprint $table) {
            $table->id('ward_id');
            $table->string('ward_name');
            $table->unsignedBigInteger('lga_id');
            $table->timestamps();
            
            // Add foreign key constraint
            $table->foreign('lga_id')->references('id')->on('lgas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wards');
    }
};