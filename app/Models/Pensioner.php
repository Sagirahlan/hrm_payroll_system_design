<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pensioner extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pensioners';

    protected $fillable = [
        'employee_id',
        'full_name',
        'surname',
        'first_name',
        'middle_name',
        'email',
        'phone_number',
        'date_of_birth',
        'place_of_birth',
        'date_of_first_appointment',
        'date_of_retirement',
        'retirement_reason',
        'retirement_type', // RB (Retirement Benefits) or DG (Death Gratuity)
        'department_id',
        'rank_id',
        'step_id',
        'grade_level_id',
        'salary_scale_id',
        'local_gov_area_id',
        'bank_id',
        'account_number',
        'account_name',
        'pension_amount',
        'gratuity_amount',
        'total_death_gratuity',
        'years_of_service',
        'pension_percentage',
        'gratuity_percentage',
        'address',
        'next_of_kin_name',
        'next_of_kin_phone',
        'next_of_kin_address',
        'status', // Active, Terminated, etc.
        'retirement_id',
        'beneficiary_computation_id',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_first_appointment' => 'date',
        'date_of_retirement' => 'date',
        'pension_amount' => 'decimal:2',
        'gratuity_amount' => 'decimal:2',
        'total_death_gratuity' => 'decimal:2',
        'years_of_service' => 'decimal:2',
        'pension_percentage' => 'decimal:2',
        'gratuity_percentage' => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function department()
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id');
    }

    public function rank()
    {
        return $this->belongsTo(\App\Models\Rank::class, 'rank_id');
    }

    public function step()
    {
        return $this->belongsTo(\App\Models\Step::class, 'step_id');
    }

    public function gradeLevel()
    {
        return $this->belongsTo(\App\Models\GradeLevel::class, 'grade_level_id');
    }

    public function salaryScale()
    {
        return $this->belongsTo(\App\Models\SalaryScale::class, 'salary_scale_id');
    }

    public function localGovArea()
    {
        return $this->belongsTo(\App\Models\LGA::class, 'local_gov_area_id');
    }

    public function bank()
    {
        return $this->belongsTo(\App\Models\BankList::class, 'bank_id');
    }

    public function retirement()
    {
        return $this->belongsTo(\App\Models\Retirement::class, 'retirement_id');
    }

    public function beneficiaryComputation()
    {
        return $this->belongsTo(\App\Models\ComputeBeneficiary::class, 'beneficiary_computation_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}