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
        Schema::create('pensioners', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id')->unique(); // Keep employee_id from original employee record
            $table->string('full_name');
            $table->string('surname');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->date('date_of_birth');
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_first_appointment');
            $table->date('date_of_retirement');
            $table->string('retirement_reason')->nullable();
            $table->string('retirement_type')->default('RB'); // RB (Retirement Benefits) or DG (Death Gratuity)
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('rank_id')->nullable();
            $table->unsignedBigInteger('step_id')->nullable();
            $table->unsignedBigInteger('grade_level_id')->nullable();
            $table->unsignedBigInteger('salary_scale_id')->nullable();
            $table->unsignedBigInteger('local_gov_area_id')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->decimal('pension_amount', 15, 2)->default(0);
            $table->decimal('gratuity_amount', 15, 2)->default(0);
            $table->decimal('total_death_gratuity', 15, 2)->default(0);
            $table->decimal('years_of_service', 8, 2)->default(0);
            $table->decimal('pension_percentage', 8, 2)->default(0);
            $table->decimal('gratuity_percentage', 8, 2)->default(0);
            $table->text('address')->nullable();
            $table->string('next_of_kin_name')->nullable();
            $table->string('next_of_kin_phone')->nullable();
            $table->text('next_of_kin_address')->nullable();
            $table->string('status')->default('Active'); // Active, Terminated, etc.
            $table->unsignedBigInteger('retirement_id')->nullable(); // Link to retirement record
            $table->unsignedBigInteger('beneficiary_computation_id')->nullable(); // Link to beneficiary computation
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->softDeletes(); // For soft deletes
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('department_id')->references('department_id')->on('departments')->onDelete('set null');
            $table->foreign('rank_id')->references('id')->on('ranks')->onDelete('set null');
            $table->foreign('step_id')->references('id')->on('steps')->onDelete('set null');
            $table->foreign('grade_level_id')->references('id')->on('grade_levels')->onDelete('set null');
            $table->foreign('salary_scale_id')->references('id')->on('salary_scales')->onDelete('set null');
            $table->foreign('local_gov_area_id')->references('id')->on('lgas')->onDelete('set null');
            $table->foreign('bank_id')->references('bank_id')->on('banks')->onDelete('set null');
            $table->foreign('retirement_id')->references('id')->on('retirements')->onDelete('set null');
            $table->foreign('beneficiary_computation_id')->references('id')->on('compute_beneficiaries')->onDelete('set null');
            $table->foreign('created_by')->references('user_id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('user_id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pensioners');
    }
};
