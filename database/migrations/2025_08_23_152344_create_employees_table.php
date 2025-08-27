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
        Schema::create('employees', function (Blueprint $table) {
            $table->id('employee_id');
            $table->string('first_name');
            $table->string('surname');
            $table->string('middle_name')->nullable();
            $table->string('gender');
            $table->date('date_of_birth');
            $table->string('state_of_origin');
            $table->string('lga');
            $table->string('ward')->nullable();
            $table->string('nationality');
            $table->string('nin')->nullable();
            $table->string('mobile_no');
            $table->string('email')->unique();
            $table->text('address');
            $table->date('date_of_first_appointment');
            $table->unsignedBigInteger('cadre_id')->nullable();
            $table->string('reg_no')->unique();
            $table->unsignedBigInteger('scale_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->date('expected_next_promotion')->nullable();
            $table->date('expected_retirement_date')->nullable();
            $table->string('status')->default('active');
            $table->string('highest_certificate')->nullable();
            $table->string('grade_level_limit')->nullable();
            $table->string('appointment_type')->nullable();
            $table->string('photo_path')->nullable();
            $table->integer('years_of_service')->nullable();
            $table->timestamps();
            
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

