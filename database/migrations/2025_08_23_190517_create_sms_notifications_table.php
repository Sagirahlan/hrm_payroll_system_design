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
        Schema::create('sms_notifications', function (Blueprint $table) {
            $table->id('sms_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('recipient_type');
            $table->unsignedBigInteger('recipient_id');
            $table->text('message');
            $table->string('status')->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_notifications');
    }
};

