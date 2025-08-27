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
            // Drop the existing columns
            $table->dropColumn(['state_of_origin', 'lga', 'ward']);
            
            // Add new columns with foreign keys
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('lga_id')->nullable();
            $table->unsignedBigInteger('ward_id')->nullable();
            
            // Add foreign key constraints
            $table->foreign('state_id')->references('state_id')->on('states')->onDelete('set null');
            $table->foreign('lga_id')->references('id')->on('lgas')->onDelete('set null');
            $table->foreign('ward_id')->references('ward_id')->on('wards')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop the foreign key constraints
            $table->dropForeign(['state_id']);
            $table->dropForeign(['lga_id']);
            $table->dropForeign(['ward_id']);
            
            // Drop the ID columns
            $table->dropColumn(['state_id', 'lga_id', 'ward_id']);
            
            // Add back the original columns
            $table->string('state_of_origin');
            $table->string('lga');
            $table->string('ward')->nullable();
        });
    }
};