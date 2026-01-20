<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PensionCalculationService
{
    /**
     * Get date span between two dates (years, months, days)
     */
    public function getDateSpan(Carbon $fromDate, Carbon $toDate): array
    {
        $years = 0;
        $months = 0;
        $days = 0;

        $tempDate = clone $toDate;

        // Calculate years
        while ($tempDate->copy()->subYear() >= $fromDate) {
            $years++;
            $tempDate->subYear();
        }

        // Calculate months
        while ($tempDate->copy()->subMonth() >= $fromDate) {
            $months++;
            $tempDate->subMonth();
        }

        // Calculate days
        while ($tempDate->copy()->subDay() >= $fromDate) {
            $days++;
            $tempDate->subDay();
        }

        return [
            'years' => $years,
            'months' => $months,
            'days' => $days,
        ];
    }

    /**
     * Get total months between two dates
     */
    public function getTotalMonths(Carbon $fromDate, Carbon $toDate): int
    {
        return $fromDate->diffInMonths($toDate);
    }

    /**
     * Get staff basic salary at retirement from archive
     */
    public function getStaffBasicSalaryAtRetirement(string $stepId, Carbon $retirementDate, string $salaryScaleId): array
    {
        $retirementDateStr = $retirementDate->format('Y-m-d');
        $salaryScaleCircularId = $this->getStaffSalaryScaleCircularID($retirementDateStr);

        if ($salaryScaleCircularId <= 0) {
            return [
                'salary_scale_circular_id' => 0,
                'rate_per_mnth' => 0,
                'rate_per_annum' => 0,
            ];
        }

        $salary = DB::table('salary_scale_archives')
            ->where('sscale_circular_id', $salaryScaleCircularId)
            ->where('stepid', $stepId)
            ->where('salary_scale_id', $salaryScaleId)
            ->first(['rate_per_mnth', 'rate_per_annum']);

        if (!$salary) {
            return [
                'salary_scale_circular_id' => $salaryScaleCircularId,
                'rate_per_mnth' => 0,
                'rate_per_annum' => 0,
            ];
        }

        return [
            'salary_scale_circular_id' => $salaryScaleCircularId,
            'rate_per_mnth' => (float) ($salary->rate_per_mnth ?? 0),
            'rate_per_annum' => (float) ($salary->rate_per_annum ?? 0),
        ];
    }

    /**
     * Get salary scale circular ID for a given retirement date
     */
    public function getStaffSalaryScaleCircularID(string $retirementDate): int
    {
        $result = DB::selectOne("
            SELECT 
                (SELECT id 
                 FROM salary_scale_circulars 
                 WHERE ? >= effect_from AND is_current = 1 
                 LIMIT 1) AS is_current_sscale_id,
                (SELECT id 
                 FROM salary_scale_circulars 
                 WHERE effect_to >= ? AND effect_from <= ? AND is_current = 0 
                 LIMIT 1) AS others_sscale_id
        ", [$retirementDate, $retirementDate, $retirementDate]);

        if ($result) {
            // Check current circular first
            if (!empty($result->is_current_sscale_id) && (int)$result->is_current_sscale_id > 0) {
                return (int)$result->is_current_sscale_id;
            }
            
            // Check old circulars
            if (!empty($result->others_sscale_id) && (int)$result->others_sscale_id > 0) {
                return (int)$result->others_sscale_id;
            }
        }

        return 0;
    }

    /**
     * Get computation years based on elevated status and years of service
     */
    public function getComputationYears(int $isElevated, int $yearsOfService): int
    {
        if ($isElevated == 1) {
            if ($yearsOfService < 35) {
                return $yearsOfService + 1; // add 1 year
            } else {
                return 35; // cap at 35 years
            }
        } else {
            if ($yearsOfService < 35) {
                return $yearsOfService;
            } else {
                return 35; // cap at 35 years
            }
        }
    }

    /**
     * Get computation percentages (gratuity and pension)
     */
    public function getComputationPercentages(int $yearsOfService): array
    {
        $percentage = DB::table('compute_percentage')
            ->where('years_of_service', $yearsOfService)
            ->first(['gratuity_pct', 'pension_pct']);

        if (!$percentage) {
            return [
                'gratuity_pct' => 0,
                'pension_pct' => 0,
            ];
        }

        return [
            'gratuity_pct' => (int)$percentage->gratuity_pct,
            'pension_pct' => (int)$percentage->pension_pct,
        ];
    }

    /**
     * Calculate overstay remark based on age and service
     */
    public function calculateOverstay(array $ageSpan, array $serviceSpan): string
    {
        $overstayRemark = '';

        // Check overstay by age (60 years)
        if ($ageSpan['years'] >= 60 && ($ageSpan['months'] > 0 || $ageSpan['days'] > 0)) {
            $overstayRemark = 'Over Stayed by Age: ' . 
                ($ageSpan['years'] - 60) . ' Years ' . 
                $ageSpan['months'] . ' Months ' . 
                $ageSpan['days'] . ' Days';
        }

        // Check overstay by service (35 years)
        $totalServiceDays = ($serviceSpan['years'] * 365) + ($serviceSpan['months'] * 30) + $serviceSpan['days'];
        $maxServiceDays = 35 * 365;

        if ($totalServiceDays > $maxServiceDays) {
            $overstayYears = $serviceSpan['years'] - 35;
            $overstayRemark .= ($overstayRemark ? ' ' : '') . 
                'Over Stayed by Service: ' . 
                $overstayYears . ' Years ' . 
                $serviceSpan['months'] . ' Months ' . 
                $serviceSpan['days'] . ' Days';
        }

        return $overstayRemark;
    }

    /**
     * Calculate overstay deduction amount
     */
    public function calculateOverstayDeduction(array $ageSpan, array $serviceSpan, float $monthlySalary): float
    {
        $deductionAmount = 0;

        // Check overstay by age (60 years)
        if ($ageSpan['years'] >= 60 && ($ageSpan['months'] > 0 || $ageSpan['days'] > 0)) {
            // Calculate overstay duration in months
            $yearsOver = $ageSpan['years'] - 60;
            $monthsOver = $yearsOver * 12 + $ageSpan['months'] + ($ageSpan['days'] / 30.44);
            $deductionAmount = $monthsOver * $monthlySalary;
        }

        // Check overstay by service (35 years)
        // We take the higher of the two if both apply? Or cumulative?
        // Usually, retirement is triggered by whichever comes FIRST. 
        // So overstay is the duration AFTER the valid retirement date.
        // It's safer to rely on the "Expected Date" logic used in Controller, but here we only have spans.
        // Let's refine this. The most accurate way is Date Comparison (Actual - Expected).
        // Reuse the logic I put in PayrollController but strictly date-based.
        // Wait, passing dates is better than spans for this.
        
        return 0; // Placeholder, I will use a different signature or logic
    }

    /**
     * Calculate Expected Retirement Date based on age and service
     * Returns the earlier of: 60 years from DOB or 35 years from DOFA
     */
    public function calculateExpectedRetirementDate(\Carbon\Carbon $dob, \Carbon\Carbon $dofa, $salaryScale = null): \Carbon\Carbon
    {
        // Default retirement age and years of service
        $retirementAge = 60;
        $maxYearsOfService = 35;
        
        // If salary scale is provided, use its values
        if ($salaryScale) {
            $retirementAge = (int) ($salaryScale->max_retirement_age ?? 60);
            $maxYearsOfService = (int) ($salaryScale->max_years_of_service ?? 35);
        }
        
        // Calculate retirement date by age
        $expectedByAge = $dob->copy()->addYears($retirementAge);
        
        // Calculate retirement date by service
        $expectedByService = $dofa->copy()->addYears($maxYearsOfService);
        
        // Return the earlier of the two
        return $expectedByAge->min($expectedByService);
    }

    /**
     * Calculate Overstay Amount based on dates with 25-day grace period
     * 
     * Grace Period Rules:
     * - If overstayed days <= 25: No deduction
     * - If overstayed days > 25: Deduction applies to ALL days from expected retirement date
     * 
     * @param \Carbon\Carbon $dob Date of Birth
     * @param \Carbon\Carbon $dofa Date of First Appointment
     * @param \Carbon\Carbon $retirementDate Actual Retirement Date
     * @param float $monthlySalary Monthly Salary
     * @param int $gracePeriodDays Grace period in days (default: 25)
     * @return array ['amount' => float, 'days' => int, 'expected_date' => Carbon, 'grace_applied' => bool]
     */
    public function calculateOverstayAmount(
        \Carbon\Carbon $dob, 
        \Carbon\Carbon $dofa, 
        \Carbon\Carbon $retirementDate, 
        float $monthlySalary,
        int $gracePeriodDays = 25
    ): array
    {
         // Calculate expected retirement date (60 years age or 35 years service, whichever comes first)
         $expectedByAge = $dob->copy()->addYears(60);
         $expectedByService = $dofa->copy()->addYears(35);
         
         $expectedRetirement = $expectedByAge->min($expectedByService);
         
         \Illuminate\Support\Facades\Log::info("Calculate Overstay: DOB={$dob}, DOFA={$dofa}, Retirement={$retirementDate}, Salary={$monthlySalary}");
         \Illuminate\Support\Facades\Log::info("Expected Retirement: {$expectedRetirement}");

         if ($retirementDate->gt($expectedRetirement)) {
             // Calculate days overstayed
             $daysOverstayed = $retirementDate->diffInDays($expectedRetirement, false);
             $daysOverstayed = abs($daysOverstayed);
             
             \Illuminate\Support\Facades\Log::info("Days Overstayed: {$daysOverstayed}, Grace Period: {$gracePeriodDays}");
             
             // Apply grace period logic
             if ($daysOverstayed <= $gracePeriodDays) {
                 // Within grace period - no deduction
                 \Illuminate\Support\Facades\Log::info("Within grace period - no deduction applied");
                 return [
                     'amount' => 0,
                     'days' => $daysOverstayed,
                     'expected_date' => $expectedRetirement,
                     'grace_applied' => true,
                     'grace_waived' => true
                 ];
             }
             
             // Beyond grace period - deduction applies to ALL days from expected retirement date
             $monthsOverstayed = $daysOverstayed / 30.44; // Average days per month
             $deductionAmount = round($monthlySalary * $monthsOverstayed, 2);
             
             \Illuminate\Support\Facades\Log::info("Beyond grace period - Deduction for all {$daysOverstayed} days: {$deductionAmount}");

             if ($monthlySalary > 0 && $monthsOverstayed > 0) {
                 return [
                    'amount' => $deductionAmount,
                    'days' => $daysOverstayed,
                    'expected_date' => $expectedRetirement,
                    'grace_applied' => true,
                    'grace_waived' => false
                 ];
             }
         }
         
         // No overstay
         return [
             'amount' => 0, 
             'days' => 0,
             'expected_date' => $expectedRetirement,
             'grace_applied' => false,
             'grace_waived' => false
         ];
    }
}
