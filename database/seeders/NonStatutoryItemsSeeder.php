<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NonStatutoryItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Non-statutory additions
        $additionTypes = [
            [
                'name' => 'Transport Allowance',
                'code' => 'TA',
                'description' => 'Allowance for transportation.',
                'is_statutory' => false,
                'calculation_type' => 'fixed_amount',
                'rate_or_amount' => 5000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Housing Allowance',
                'code' => 'HA',
                'description' => 'Allowance for housing.',
                'is_statutory' => false,
                'calculation_type' => 'fixed_amount',
                'rate_or_amount' => 10000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($additionTypes as $type) {
            DB::table('addition_types')->updateOrInsert(
                ['code' => $type['code']], // Unique identifier
                $type
            );
        }

        // Non-statutory deductions
        $deductionTypes = [
            [
                'name' => 'Staff Loan',
                'code' => 'SL',
                'description' => 'Deduction for staff loan.',
                'is_statutory' => false,
                'calculation_type' => 'fixed_amount',
                'rate_or_amount' => 2000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cooperative Contribution',
                'code' => 'CC',
                'description' => 'Deduction for cooperative contribution.',
                'is_statutory' => false,
                'calculation_type' => 'fixed_amount',
                'rate_or_amount' => 3000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($deductionTypes as $type) {
            DB::table('deduction_types')->updateOrInsert(
                ['code' => $type['code']], // Unique identifier
                $type
            );
        }
    }
}
