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
        Schema::create('next_of_kin', function (Blueprint $table) {
            $table->id('kin_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('name');
            $table->string('relationship');
            $table->string('mobile_no');
            $table->text('address');
            $table->string('occupation')->nullable();
            $table->string('place_of_work')->nullable();
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('next_of_kin');
    }
};

