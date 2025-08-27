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
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('employee_id')->references('employee_id')->on('employees')->onDelete('cascade');
            $table->foreign('role_id')->references('role_id')->on('user_roles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};

