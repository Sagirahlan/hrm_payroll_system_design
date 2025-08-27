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
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->string('action');
            $table->text('description')->nullable();
            $table->timestamp('action_timestamp')->useCurrent();
            $table->json('log_data')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('role_id')->references('role_id')->on('user_roles')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};

