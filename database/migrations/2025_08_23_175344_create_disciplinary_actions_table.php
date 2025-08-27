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
        Schema::create('disciplinary_actions', function (Blueprint $table) {
            $table->id('action_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('action_type');
            $table->text('description');
            $table->date('action_date');
            $table->date('resolution_date')->nullable();
            $table->string('status')->default('open');
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
        Schema::dropIfExists('disciplinary_actions');
    }
};

