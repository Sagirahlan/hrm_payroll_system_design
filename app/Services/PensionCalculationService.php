<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PensionCalculationService
{
    /**
     * Calculate the date span between two dates (appointment date to retirement date)
     */
    public function getDateSpan(Carbon $startDate, Carbon $endDate)
    {
        $diff = $startDate->diff($endDate);

        return [
            'years' => $diff->y,
            'months' => $diff->m,
            'days' => $diff->d
        ];
    }

    /**
     * Get total months of service between two dates
     */
    public function getTotalMonths(Carbon $startDate, Carbon $endDate)
    {
        $diff = $startDate->diff($endDate);
        return ($diff->y * 12) + $diff->m + ($diff->d > 0 ? 1 : 0);
    }

    /**
     * Get staff's basic salary at retirement date
     */
    public function getStaffBasicSalaryAtRetirement($stepId, Carbon $retirementDate, $salaryScaleId)
    {
        // Get the step details
        $step = DB::table('gl_steps')
            ->where('stepid', $stepId)
            ->first();

        if (!$step) {
            return [
                'salary_scale_circular_id' => 0,
                'rate_per_annum' => 0,
                'rate_per_mnth' => 0
            ];
        }

        // Get the salary scale circular that was effective at retirement date
        $salaryScaleCircular = DB::table('salary_scale_circulars')
            ->where('salary_scale_id', $salaryScaleId)
            ->where('effective_date', '<=', $retirementDate)
            ->orderBy('effective_date', 'desc')
            ->first();

        if (!$salaryScaleCircular) {
            return [
                'salary_scale_circular_id' => 0,
                'rate_per_annum' => 0,
                'rate_per_mnth' => 0
            ];
        }

        // Calculate the rate based on step and salary circular
        $ratePerAnnum = $this->calculateSalaryRate($step, $salaryScaleCircular);

        return [
            'salary_scale_circular_id' => $salaryScaleCircular->id,
            'rate_per_annum' => $ratePerAnnum,
            'rate_per_mnth' => $ratePerAnnum / 12
        ];
    }

    /**
     * Calculate salary rate based on step and salary scale circular
     */
    private function calculateSalaryRate($step, $salaryScaleCircular)
    {
        // This is a simplified calculation - you should adjust this based on your business logic
        // Typically, this would involve looking up rates from the salary scale circular data
        return $step->rate ?? 0; // Using the step rate directly as an example
    }

    /**
     * Get computation years based on service elevation
     */
    public function getComputationYears($isElevated, $serviceYears)
    {
        // If service years should be elevated (6+ months in final year)
        if ($isElevated) {
            return $serviceYears + 1;
        }
        return $serviceYears;
    }

    /**
     * Get computation percentages based on years of service
     */
    public function getComputationPercentages($yearsOfService)
    {
        // Define your pension computation logic here
        // These are example values - you should adjust them based on your business rules
        
        // Example: For every 1 year of service, gratuity increases by 10%, max 20 years = 200%
        $gratuityPct = min($yearsOfService * 10, 200); // Max 200% gratuity
        
        // Example: Pension is typically 50% of basic salary after 15+ years
        $pensionPct = min(($yearsOfService / 2), 50); // Max 50% pension

        return [
            'gratuity_pct' => $gratuityPct,
            'pension_pct' => $pensionPct
        ];
    }

    /**
     * Calculate overstay based on age and service span
     */
    public function calculateOverstay($ageSpan, $dateSpan)
    {
        // Default retirement age might be 60
        $retirementAge = 60;
        $currentAge = $ageSpan['years'];

        if ($currentAge > $retirementAge) {
            $overstayYears = $currentAge - $retirementAge;
            return "Overstay of {$overstayYears} year(s)";
        }

        return "Normal service";
    }
}