<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_scale_archives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sscale_circular_id')->nullable();
            $table->unsignedBigInteger('stepid')->nullable();
            $table->unsignedBigInteger('salary_scale_id')->nullable();
            $table->decimal('rate_per_mnth', 15, 2)->default(0);
            $table->decimal('rate_per_annum', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('sscale_circular_id')->references('id')->on('salary_scale_circulars')->onDelete('cascade');
            $table->foreign('stepid')->references('id')->on('steps')->onDelete('cascade');
            $table->foreign('salary_scale_id')->references('id')->on('salary_scales')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salary_scale_archives');
    }
};