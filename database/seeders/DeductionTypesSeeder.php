<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeductionTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deductionTypes = [
            [
                'name' => 'Special Loan',
                'code' => 'SPL',
                'description' => 'Company-issued special loan deduction',
                'is_statutory' => 0,
                'calculation_type' => 'fixed_amount',
                'rate_or_amount' => null,
            ],
            [
                'name' => 'Casual Staff Personal Loan',
                'code' => 'CSPL',
                'description' => 'Loan repayment for casual staff',
                'is_statutory' => 0,
                'calculation_type' => 'fixed_amount',
                'rate_or_amount' => null,
            ],
            [
                'name' => 'Health Contribution',
                'code' => 'HC',
                'description' => 'Health scheme contribution',
                'is_statutory' => 1,
                'calculation_type' => 'percentage',
                'rate_or_amount' => 0.015,
            ],
            [
                'name' => 'NHF Deduction',
                'code' => 'NHF',
                'description' => 'National Housing Fund deduction',
                'is_statutory' => 1,
                'calculation_type' => 'percentage',
                'rate_or_amount' => 0.025,
            ],
            [
                'name' => 'NHF Renovation Loan',
                'code' => 'NHFRL',
                'description' => 'NHF renovation loan repayment',
                'is_statutory' => 0,
                'calculation_type' => 'fixed_amount',
                'rate_or_amount' => null,
            ],
            [
                'name' => 'PAYE',
                'code' => 'PAYE',
                'description' => 'Pay As You Earn tax deduction',
                'is_statutory' => 1,
                'calculation_type' => 'percentage',
                'rate_or_amount' => null,
            ],
            [
                'name' => 'Permanent Staff Personal Loan',
                'code' => 'PSPL',
                'description' => 'Loan repayment for permanent staff',
                'is_statutory' => 0,
                'calculation_type' => 'fixed_amount',
                'rate_or_amount' => null,
            ],
            [
                'name' => 'Refurbishing Loan',
                'code' => 'RFL',
                'description' => 'Loan for refurbishing staff quarters',
                'is_statutory' => 0,
                'calculation_type' => 'fixed_amount',
                'rate_or_amount' => null,
            ],
            [
                'name' => 'Rent Deduction',
                'code' => 'RENT',
                'description' => 'Staff housing rent deduction',
                'is_statutory' => 0,
                'calculation_type' => 'fixed_amount',
                'rate_or_amount' => null,
            ],
            [
                'name' => 'Second NHF Deduction',
                'code' => 'NHF2',
                'description' => 'Additional NHF contribution',
                'is_statutory' => 1,
                'calculation_type' => 'fixed_amount',
                'rate_or_amount' => null,
            ],
            [
                'name' => 'State Contributory Pension Scheme',
                'code' => 'PENSION',
                'description' => 'Pension scheme deduction (Employee 8%)',
                'is_statutory' => 1,
                'calculation_type' => 'percentage',
                'rate_or_amount' => 0.08,
            ],
            [
                'name' => 'Union Dues',
                'code' => 'UNION',
                'description' => 'Union membership deduction',
                'is_statutory' => 1,
                'calculation_type' => 'percentage',
                'rate_or_amount' => 0.01,
            ],
            [
                'name' => 'ITF Deduction',
                'code' => 'ITF',
                'description' => 'Industrial Training Fund contribution (Employer statutory, not deducted from staff)',
                'is_statutory' => 1,
                'calculation_type' => 'percentage',
                'rate_or_amount' => 0.01,
            ],
            [
                'name' => 'KTSWB July 2025 NHF Deduction',
                'code' => 'KTSWB',
                'description' => 'Special July 2025 NHF deduction to FMBN',
                'is_statutory' => 0,
                'calculation_type' => 'fixed_amount',
                'rate_or_amount' => null,
            ],
        ];

        foreach ($deductionTypes as $type) {
            DB::table('deduction_types')->updateOrInsert(['code' => $type['code']], $type);
        }
    }
}