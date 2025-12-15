<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComputePercentageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('compute_percentage')->truncate();

        $data = [];

        for ($year = 1; $year <= 35; $year++) {
            $gratuity = 0;
            $pension = 0;

            // Gratuity calculation logic (Standard Public Service Estimation)
            // Usually starts from 5 years
            if ($year >= 5) {
                // Base 100% at 5 years (example), increasing by 8% yearly
                if ($year == 5) {
                    $gratuity = 100;
                } else {
                    $gratuity = 100 + (($year - 5) * 8); 
                }
            }

            // Pension calculation logic (Standard Public Service Estimation)
            // Usually starts from 10 or 15 years. Using 10 years as referenced in user error
            if ($year >= 10) {
                // Base 30% at 10 years, increasing by 2% yearly up to 80% max
                $pension = 30 + (($year - 10) * 2);
            }
            
            // Allow pension to exceed if rules differ, but typicall capped at 80%
            // However, let's keep it simple linear for now as per formula

            $data[] = [
                'years_of_service' => $year,
                'gratuity_pct' => $gratuity,
                'pension_pct' => $pension,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Add 0 years just in case
        $data[] = [
             'years_of_service' => 0,
             'gratuity_pct' => 0,
             'pension_pct' => 0,
             'created_at' => now(),
             'updated_at' => now(),
        ];

        DB::table('compute_percentage')->insert($data);
    }
}
