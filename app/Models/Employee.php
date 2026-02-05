<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\GradeLevel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\PromotionHistory;

class Employee extends Model
{
    protected $primaryKey = 'employee_id';
    protected $fillable = [
        'first_name', 'surname', 'middle_name', 'gender', 'date_of_birth', 'state_id', 'lga_id', 'ward_id',
        'nationality', 'nin', 'mobile_no', 'email', 'pay_point', 'address', 'date_of_first_appointment', 'cadre_id', 'staff_no',
        'grade_level_id', 'step_id', 'rank_id', 'department_id', 'expected_next_promotion',
        'expected_retirement_date', 'status', 'highest_certificate', 'grade_level_limit', 'appointment_type_id',
        'photo_path', 'years_of_service', 'contract_start_date', 'contract_end_date', 'amount',
        'rsa_balance', 'pfa_contribution_rate', 'pension_administrator', 'rsa_pin',
        'on_probation', 'probation_start_date', 'probation_end_date', 'probation_status', 'probation_notes',
    ];

    public function setDateOfBirthAttribute($value)
    {
        $this->attributes['date_of_birth'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function setDateOfFirstAppointmentAttribute($value)
    {
        $this->attributes['date_of_first_appointment'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function setContractStartDateAttribute($value)
    {
        $this->attributes['contract_start_date'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function setContractEndDateAttribute($value)
    {
        $this->attributes['contract_end_date'] = Carbon::parse($value)->format('Y-m-d');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id', 'state_id');
    }

    public function lga()
    {
        return $this->belongsTo(Lga::class, 'lga_id');
    }

    public function ward()
    {
        return $this->belongsTo(Ward::class, 'ward_id', 'ward_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function cadre()
    {
        return $this->belongsTo(Cadre::class, 'cadre_id');
    }

    // App.Models.Employee.php
    public function gradeLevel()
    {
        return $this->belongsTo(GradeLevel::class, 'grade_level_id', 'id');
    }

    public function step()
    {
        return $this->belongsTo(Step::class, 'step_id', 'id');
    }

    public function rank()
    {
        return $this->belongsTo(Rank::class, 'rank_id');
    }

    public function biometricData()
    {
        return $this->hasOne(BiometricData::class, 'employee_id', 'employee_id');
    }

    public function payrollRecords()
    {
        return $this->hasMany(\App\Models\PayrollRecord::class, 'employee_id', 'employee_id');
    }

        public function nextOfKin()
    {
        return $this->hasOne(NextOfKin::class, 'employee_id');
    }

    public function bank()
    {
        return $this->hasOne(Bank::class, 'employee_id', 'employee_id');
    }
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'employee_id', 'employee_id');
    }
    public function disciplinaryRecords()
{
    return $this->hasMany(DisciplinaryAction::class, 'employee_id', 'employee_id');
}

public function deductions()
{
    return $this->hasMany(Deduction::class, 'employee_id', 'employee_id');
}

public function additions()
{
    return $this->hasMany(Addition::class, 'employee_id', 'employee_id');
}

public function loans()
{
    return $this->hasMany(Loan::class, 'employee_id', 'employee_id');
}

public function hasActiveLoanForAdditionType($additionTypeName)
{
    return $this->loans()
        ->where('loan_type', $additionTypeName)
        ->whereIn('status', ['active', 'completed'])
        ->exists();
}

public function getYearsOfServiceAttribute(): ?int
{
    if (!$this->date_of_first_appointment) {
        return null;
    }

    return Carbon::parse($this->date_of_first_appointment)->diffInYears(now());
}

public function appointmentType()
{
    return $this->belongsTo(AppointmentType::class, 'appointment_type_id');
}

public function retirement()
{
    return $this->hasOne(Retirement::class, 'employee_id', 'employee_id');
}

public function pensioner()
{
    return $this->hasOne(Pensioner::class, 'employee_id', 'employee_id');
}

public function getCalculatedRetirementDateAttribute()
{
    if (!$this->gradeLevel || !$this->gradeLevel->salaryScale) {
        return null;
    }

    $retirementAge = (int) $this->gradeLevel->salaryScale->max_retirement_age;
    $yearsOfService = (int) $this->gradeLevel->salaryScale->max_years_of_service;

    $retirementDateByAge = \Carbon\Carbon::parse($this->date_of_birth)->addYears($retirementAge);
    $retirementDateByService = \Carbon\Carbon::parse($this->date_of_first_appointment)->addYears($yearsOfService);

    return $retirementDateByAge->min($retirementDateByService);
}

    public function promotionHistory()
    {
        return $this->hasMany(PromotionHistory::class, 'employee_id', 'employee_id');
    }

    public function getLastPromotionAttribute()
    {
        return $this->promotionHistory()->latest('created_at')->first();
    }

    public function leaves()
    {
        return $this->hasMany(\App\Models\Models\Leave::class, 'employee_id', 'employee_id');
    }

    /**
     * Check if employee is a casual employee
     */
    public function isCasualEmployee(): bool
    {
        return $this->appointmentType && $this->appointmentType->name === 'Casual';
    }

    /**
     * Get formatted employee details for API responses
     */
    public function getFormattedDetailsAttribute()
    {
        return [
            'employee_id' => $this->employee_id,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'surname' => $this->surname,
            'full_name' => trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->surname),
            'department' => $this->department,
            'gradeLevel' => $this->gradeLevel,
            'step' => $this->step,
            'appointmentType' => $this->appointmentType,
            'status' => $this->status,
            'last_promotion_date' => $this->getLastPromotionAttribute() ? $this->getLastPromotionAttribute()->promotion_date : null,
            'date_of_first_appointment' => $this->date_of_first_appointment,
            'years_of_service' => $this->getYearsOfServiceAttribute(),
            'probation_start_date' => $this->probation_start_date,
            'probation_end_date' => $this->probation_end_date,
            'on_probation' => $this->on_probation,
            'probation_status' => $this->probation_status,
        ];
    }

    /**
     * Check if employee is on probation
     */
    public function isOnProbation(): bool
    {
        return $this->on_probation && $this->probation_status === 'pending';
    }

    /**
     * Check if employee's probation period has ended
     */
    public function hasProbationPeriodEnded(): bool
    {
        if (!$this->on_probation || !$this->probation_end_date) {
            return false;
        }
        return Carbon::parse($this->probation_end_date)->isPast();
    }

    /**
     * Check if employee has completed probation successfully
     */
    public function hasCompletedProbation(): bool
    {
        return $this->probation_status === 'approved';
    }

    /**
     * Calculate remaining probation days based on the probation end date
     */
    public function getRemainingProbationDays(): int
    {
        if (!$this->on_probation || !$this->probation_end_date) {
            return 0;
        }

        $endDate = Carbon::parse($this->probation_end_date);
        $today = Carbon::now();

        if ($endDate->isPast()) {
            return 0;
        }

        return $today->diffInDays($endDate);
    }

    /**
     * Check if employee can be evaluated for probation (3 months have passed since probation start date)
     */
    public function canBeEvaluatedForProbation(): bool
    {
        if (!$this->on_probation || !$this->probation_start_date) {
            return false;
        }

        $probationStartDate = Carbon::parse($this->probation_start_date);
        $today = Carbon::now();

        // Check if at least 3 months have passed since the probation start date
        return $probationStartDate->addMonths(3)->lte($today);
    }

    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->middle_name . ' ' . $this->surname);
    }

}
