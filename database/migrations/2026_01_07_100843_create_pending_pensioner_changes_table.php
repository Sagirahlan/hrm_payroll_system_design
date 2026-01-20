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
        Schema::create('pending_pensioner_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pensioner_id');
            $table->unsignedBigInteger('requested_by');
            $table->string('change_type'); // create, update, delete
            $table->json('data'); // new data for the pensioner
            $table->json('previous_data')->nullable(); // previous data for comparison
            $table->text('reason')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();

            $table->foreign('pensioner_id')->references('id')->on('pensioners')->onDelete('cascade');
            $table->foreign('requested_by')->references('user_id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_pensioner_changes');
    }
};
