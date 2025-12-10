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
        Schema::create('compute_beneficiaries', function (Blueprint $table) {
            $table->id('id');
            $table->string('fulname');
            $table->integer('lgaid');
            $table->string('gtype'); // RB (Regular Beneficiary) or DG (Death Gratuity)
            $table->string('acc_no')->nullable();
            $table->integer('bankid')->nullable();
            $table->integer('reg_user'); // user ID who registered
            $table->dateTime('reg_date');
            $table->integer('stepid');
            $table->integer('deptid');
            $table->string('mobile')->nullable();
            $table->string('nxtkin_fulname')->nullable();
            $table->string('nxtkin_mobile')->nullable();
            $table->date('appt_date');
            $table->date('dod_r'); // Date of death or retirement
            $table->string('id_no');
            $table->integer('period_yrs');
            $table->integer('period_mnths');
            $table->integer('period_days');
            $table->integer('period_total_mnths');
            $table->decimal('basic_sal_annum', 10, 2);
            $table->decimal('basic_sal_mnth', 10, 2);
            $table->decimal('total_emolument', 10, 2);
            $table->integer('pct_gratuity');
            $table->integer('pct_pension');
            $table->decimal('gratuity_amt', 10, 2);
            $table->decimal('total_death_gratuity', 10, 2);
            $table->decimal('pension_per_annum', 10, 2);
            $table->decimal('pension_per_mnth', 10, 2);
            $table->decimal('accrued_pension', 10, 2);
            $table->integer('accrued_pension_yrs');
            $table->integer('apportion_fg_pct');
            $table->integer('apportion_state_pct');
            $table->integer('apportion_lga_pct');
            $table->decimal('apportion_fg_amt', 10, 2);
            $table->decimal('apportion_state_amt', 10, 2);
            $table->decimal('apportion_lga_amt', 10, 2);
            $table->integer('sscale_circular_id');
            $table->integer('is_elevated_service_yrs');
            $table->integer('service_yrs_for_compute');
            $table->integer('status')->default(0);
            $table->dateTime('approval_date')->nullable();
            $table->string('open_file_no')->nullable();
            $table->string('secret_file_no')->nullable();
            $table->date('dob');
            $table->string('overstay_remark');
            $table->string('rank');
            $table->integer('rankid');
            $table->integer('salary_scale_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compute_beneficiaries');
    }
};
