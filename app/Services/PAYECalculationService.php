<?php

namespace App\Services;

class PAYECalculationService
{
    /**
     * Nigerian PAYE progressive tax brackets.
     * Each entry: [bracket_limit, rate]
     */
    protected static array $taxBrackets = [
        [70000,      0.0164],  // 1.64% on first ₦70,000
        [50000,      0.0426],  // 4.26% on next ₦50,000
        [100000,     0.0852],  // 8.52% on next ₦100,000
        [200000,     0.1278],  // 12.78% on next ₦200,000
        [500000,     0.15],    // 15.00% on next ₦500,000
        [999999999,  0.163],   // 16.3% on remaining income
    ];

    /**
     * Compute PAYE using progressive tax brackets.
     *
     * @param float $salary The employee's basic salary (annual or monthly depending on context)
     * @return float The computed PAYE amount rounded to 2 decimal places
     */
    public static function compute(float $salary): float
    {
        $taxableIncome = $salary;
        $paye = 0;

        foreach (self::$taxBrackets as [$limit, $rate]) {
            if ($taxableIncome <= 0) {
                break;
            }

            $amount = min($taxableIncome, $limit);
            $paye += $amount * $rate;
            $taxableIncome -= $amount;
        }

        return round($paye, 2);
    }
}
