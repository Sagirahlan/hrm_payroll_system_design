<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WardsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing LGAs
        $lgas = DB::table('lgas')->pluck('id');
        
        // If no LGAs exist, create some sample ones
        if ($lgas->isEmpty()) {
            $lgas = collect([1, 2, 3, 4, 5]);
        }
        
        // Sample ward names
        $wardNames = [
            'Ward A', 'Ward B', 'Ward C', 'Ward D', 'Ward E',
            'Ward F', 'Ward G', 'Ward H', 'Ward I', 'Ward J',
            'Central Ward', 'North Ward', 'South Ward', 'East Ward', 'West Ward',
            'Urban Ward', 'Rural Ward', 'Market Ward', 'Industrial Ward', 'Residential Ward'
        ];
        
        // Generate wards for each LGA
        $wards = [];
        $wardId = 1;
        
        foreach ($lgas as $lgaId) {
            // Create 3-5 wards for each LGA
            $wardCount = rand(3, 5);
            
            for ($i = 0; $i < $wardCount; $i++) {
                $wards[] = [
                    'ward_id' => $wardId++,
                    'ward_name' => $wardNames[array_rand($wardNames)] . ' ' . ($i + 1),
                    'lga_id' => $lgaId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        // Insert wards
        DB::table('wards')->insert($wards);
    }
}