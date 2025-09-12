<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\SalaryScale;
use App\Models\GradeLevel;
use App\Models\Step;

class SalaryScaleGradeLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing grade levels and steps
        DB::table('steps')->delete();
        DB::table('grade_levels')->delete();
        
        // Create or update the HAP salary scale
        $hapScale = SalaryScale::updateOrCreate(
            ['acronym' => 'HAP'],
            [
                'full_name' => 'Harmonized Academic Pay',
                'sector_coverage' => 'Academic staff in universities and tertiary institutions',
                'grade_levels' => 'GL 01 – GL 17',
                'max_retirement_age' => '65 years',
                'max_years_of_service' => '35 years',
                'notes' => 'Harmonized salary structure for academic staff'
            ]
        );
        
        // Create grade levels for HAP with the exact salary data
        $this->createHAPGradeLevelsWithExactData($hapScale->id);
    }
    
    private function createHAPGradeLevelsWithExactData($salaryScaleId)
    {
        // Exact salary data for HAP from your provided table
        $gradeLevelData = [
            // GL1
            ['grade_level' => 'GL01', 'step_level' => 1, 'basic_salary' => 70000.00],
            ['grade_level' => 'GL01', 'step_level' => 2, 'basic_salary' => 70367.50],
            ['grade_level' => 'GL01', 'step_level' => 3, 'basic_salary' => 70735.00],
            ['grade_level' => 'GL01', 'step_level' => 4, 'basic_salary' => 71102.50],
            ['grade_level' => 'GL01', 'step_level' => 5, 'basic_salary' => 71470.00],
            ['grade_level' => 'GL01', 'step_level' => 6, 'basic_salary' => 71837.50],
            ['grade_level' => 'GL01', 'step_level' => 7, 'basic_salary' => 72205.00],
            ['grade_level' => 'GL01', 'step_level' => 8, 'basic_salary' => 72572.50],
            ['grade_level' => 'GL01', 'step_level' => 9, 'basic_salary' => 72940.00],
            ['grade_level' => 'GL01', 'step_level' => 10, 'basic_salary' => 73307.50],
            ['grade_level' => 'GL01', 'step_level' => 11, 'basic_salary' => 73675.50],
            ['grade_level' => 'GL01', 'step_level' => 12, 'basic_salary' => 74042.50],
            ['grade_level' => 'GL01', 'step_level' => 13, 'basic_salary' => 74410.00],
            ['grade_level' => 'GL01', 'step_level' => 14, 'basic_salary' => 74777.50],
            ['grade_level' => 'GL01', 'step_level' => 15, 'basic_salary' => 75145.00],
            
            // GL2
            ['grade_level' => 'GL02', 'step_level' => 1, 'basic_salary' => 70500.00],
            ['grade_level' => 'GL02', 'step_level' => 2, 'basic_salary' => 70964.17],
            ['grade_level' => 'GL02', 'step_level' => 3, 'basic_salary' => 71428.34],
            ['grade_level' => 'GL02', 'step_level' => 4, 'basic_salary' => 71892.51],
            ['grade_level' => 'GL02', 'step_level' => 5, 'basic_salary' => 72356.32],
            ['grade_level' => 'GL02', 'step_level' => 6, 'basic_salary' => 72820.85],
            ['grade_level' => 'GL02', 'step_level' => 7, 'basic_salary' => 73285.02],
            ['grade_level' => 'GL02', 'step_level' => 8, 'basic_salary' => 73749.19],
            ['grade_level' => 'GL02', 'step_level' => 9, 'basic_salary' => 74213.36],
            ['grade_level' => 'GL02', 'step_level' => 10, 'basic_salary' => 74677.47],
            ['grade_level' => 'GL02', 'step_level' => 11, 'basic_salary' => 75141.70],
            ['grade_level' => 'GL02', 'step_level' => 12, 'basic_salary' => 75605.87],
            ['grade_level' => 'GL02', 'step_level' => 13, 'basic_salary' => 76070.04],
            ['grade_level' => 'GL02', 'step_level' => 14, 'basic_salary' => 76534.21],
            ['grade_level' => 'GL02', 'step_level' => 15, 'basic_salary' => 76998.38],
            
            // GL3
            ['grade_level' => 'GL03', 'step_level' => 1, 'basic_salary' => 71200.00],
            ['grade_level' => 'GL03', 'step_level' => 2, 'basic_salary' => 71790.93],
            ['grade_level' => 'GL03', 'step_level' => 3, 'basic_salary' => 72381.66],
            ['grade_level' => 'GL03', 'step_level' => 4, 'basic_salary' => 72972.49],
            ['grade_level' => 'GL03', 'step_level' => 5, 'basic_salary' => 73563.32],
            ['grade_level' => 'GL03', 'step_level' => 6, 'basic_salary' => 74154.15],
            ['grade_level' => 'GL03', 'step_level' => 7, 'basic_salary' => 74744.98],
            ['grade_level' => 'GL03', 'step_level' => 8, 'basic_salary' => 75335.81],
            ['grade_level' => 'GL03', 'step_level' => 9, 'basic_salary' => 75926.64],
            ['grade_level' => 'GL03', 'step_level' => 10, 'basic_salary' => 76517.47],
            ['grade_level' => 'GL03', 'step_level' => 11, 'basic_salary' => 77108.30],
            ['grade_level' => 'GL03', 'step_level' => 12, 'basic_salary' => 77699.13],
            ['grade_level' => 'GL03', 'step_level' => 13, 'basic_salary' => 78289.96],
            ['grade_level' => 'GL03', 'step_level' => 14, 'basic_salary' => 78880.79],
            ['grade_level' => 'GL03', 'step_level' => 15, 'basic_salary' => 79471.62],
            
            // GL4
            ['grade_level' => 'GL04', 'step_level' => 1, 'basic_salary' => 72100.00],
            ['grade_level' => 'GL04', 'step_level' => 2, 'basic_salary' => 72809.83],
            ['grade_level' => 'GL04', 'step_level' => 3, 'basic_salary' => 73519.66],
            ['grade_level' => 'GL04', 'step_level' => 4, 'basic_salary' => 74229.49],
            ['grade_level' => 'GL04', 'step_level' => 5, 'basic_salary' => 74939.32],
            ['grade_level' => 'GL04', 'step_level' => 6, 'basic_salary' => 75649.15],
            ['grade_level' => 'GL04', 'step_level' => 7, 'basic_salary' => 76358.98],
            ['grade_level' => 'GL04', 'step_level' => 8, 'basic_salary' => 77068.81],
            ['grade_level' => 'GL04', 'step_level' => 9, 'basic_salary' => 77778.64],
            ['grade_level' => 'GL04', 'step_level' => 10, 'basic_salary' => 78488.47],
            ['grade_level' => 'GL04', 'step_level' => 11, 'basic_salary' => 79198.30],
            ['grade_level' => 'GL04', 'step_level' => 12, 'basic_salary' => 79908.13],
            ['grade_level' => 'GL04', 'step_level' => 13, 'basic_salary' => 80617.96],
            ['grade_level' => 'GL04', 'step_level' => 14, 'basic_salary' => 81327.79],
            ['grade_level' => 'GL04', 'step_level' => 15, 'basic_salary' => 82037.62],
            
            // GL5
            ['grade_level' => 'GL05', 'step_level' => 1, 'basic_salary' => 73200.00],
            ['grade_level' => 'GL05', 'step_level' => 2, 'basic_salary' => 74047.17],
            ['grade_level' => 'GL05', 'step_level' => 3, 'basic_salary' => 74894.34],
            ['grade_level' => 'GL05', 'step_level' => 4, 'basic_salary' => 75741.51],
            ['grade_level' => 'GL05', 'step_level' => 6, 'basic_salary' => 77435.85],
            ['grade_level' => 'GL05', 'step_level' => 7, 'basic_salary' => 78283.02],
            ['grade_level' => 'GL05', 'step_level' => 8, 'basic_salary' => 79130.19],
            ['grade_level' => 'GL05', 'step_level' => 9, 'basic_salary' => 79977.36],
            ['grade_level' => 'GL05', 'step_level' => 10, 'basic_salary' => 80824.53],
            ['grade_level' => 'GL05', 'step_level' => 11, 'basic_salary' => 81671.70],
            ['grade_level' => 'GL05', 'step_level' => 12, 'basic_salary' => 82518.87],
            ['grade_level' => 'GL05', 'step_level' => 13, 'basic_salary' => 83366.04],
            ['grade_level' => 'GL05', 'step_level' => 14, 'basic_salary' => 84213.21],
            ['grade_level' => 'GL05', 'step_level' => 15, 'basic_salary' => 85060.38],
            
            // GL6
            ['grade_level' => 'GL06', 'step_level' => 1, 'basic_salary' => 74500.00],
            ['grade_level' => 'GL06', 'step_level' => 2, 'basic_salary' => 75505.17],
            ['grade_level' => 'GL06', 'step_level' => 3, 'basic_salary' => 76510.34],
            ['grade_level' => 'GL06', 'step_level' => 4, 'basic_salary' => 77515.51],
            ['grade_level' => 'GL06', 'step_level' => 5, 'basic_salary' => 78520.68],
            ['grade_level' => 'GL06', 'step_level' => 6, 'basic_salary' => 79525.85],
            ['grade_level' => 'GL06', 'step_level' => 7, 'basic_salary' => 80531.02],
            ['grade_level' => 'GL06', 'step_level' => 8, 'basic_salary' => 81536.19],
            ['grade_level' => 'GL06', 'step_level' => 9, 'basic_salary' => 82541.36],
            ['grade_level' => 'GL06', 'step_level' => 10, 'basic_salary' => 83546.53],
            ['grade_level' => 'GL06', 'step_level' => 11, 'basic_salary' => 84551.70],
            ['grade_level' => 'GL06', 'step_level' => 12, 'basic_salary' => 85556.87],
            ['grade_level' => 'GL06', 'step_level' => 13, 'basic_salary' => 86562.04],
            ['grade_level' => 'GL06', 'step_level' => 14, 'basic_salary' => 87567.21],
            ['grade_level' => 'GL06', 'step_level' => 15, 'basic_salary' => 88572.38],
            
            // GL7
            ['grade_level' => 'GL07', 'step_level' => 1, 'basic_salary' => 90066.29],
            ['grade_level' => 'GL07', 'step_level' => 2, 'basic_salary' => 91347.71],
            ['grade_level' => 'GL07', 'step_level' => 3, 'basic_salary' => 92629.13],
            ['grade_level' => 'GL07', 'step_level' => 4, 'basic_salary' => 93910.55],
            ['grade_level' => 'GL07', 'step_level' => 5, 'basic_salary' => 95191.97],
            ['grade_level' => 'GL07', 'step_level' => 6, 'basic_salary' => 96473.39],
            ['grade_level' => 'GL07', 'step_level' => 7, 'basic_salary' => 97754.81],
            ['grade_level' => 'GL07', 'step_level' => 8, 'basic_salary' => 99036.23],
            ['grade_level' => 'GL07', 'step_level' => 9, 'basic_salary' => 100317.65],
            ['grade_level' => 'GL07', 'step_level' => 10, 'basic_salary' => 101599.07],
            ['grade_level' => 'GL07', 'step_level' => 11, 'basic_salary' => 102880.49],
            ['grade_level' => 'GL07', 'step_level' => 12, 'basic_salary' => 104161.91],
            ['grade_level' => 'GL07', 'step_level' => 13, 'basic_salary' => 105443.33],
            ['grade_level' => 'GL07', 'step_level' => 14, 'basic_salary' => 106724.75],
            ['grade_level' => 'GL07', 'step_level' => 15, 'basic_salary' => 108006.17],
            
            // GL8
            ['grade_level' => 'GL08', 'step_level' => 1, 'basic_salary' => 100074.75],
            ['grade_level' => 'GL08', 'step_level' => 2, 'basic_salary' => 101600.00],
            ['grade_level' => 'GL08', 'step_level' => 3, 'basic_salary' => 103125.25],
            ['grade_level' => 'GL08', 'step_level' => 4, 'basic_salary' => 104650.50],
            ['grade_level' => 'GL08', 'step_level' => 5, 'basic_salary' => 106175.75],
            ['grade_level' => 'GL08', 'step_level' => 6, 'basic_salary' => 107701.00],
            ['grade_level' => 'GL08', 'step_level' => 7, 'basic_salary' => 109226.25],
            ['grade_level' => 'GL08', 'step_level' => 8, 'basic_salary' => 110751.50],
            ['grade_level' => 'GL08', 'step_level' => 9, 'basic_salary' => 112276.75],
            ['grade_level' => 'GL08', 'step_level' => 10, 'basic_salary' => 113802.00],
            ['grade_level' => 'GL08', 'step_level' => 11, 'basic_salary' => 115327.25],
            ['grade_level' => 'GL08', 'step_level' => 12, 'basic_salary' => 116852.50],
            ['grade_level' => 'GL08', 'step_level' => 13, 'basic_salary' => 118377.75],
            ['grade_level' => 'GL08', 'step_level' => 14, 'basic_salary' => 119903.00],
            ['grade_level' => 'GL08', 'step_level' => 15, 'basic_salary' => 121428.25],
            
            // GL9
            ['grade_level' => 'GL09', 'step_level' => 1, 'basic_salary' => 108679.23],
            ['grade_level' => 'GL09', 'step_level' => 2, 'basic_salary' => 110495.15],
            ['grade_level' => 'GL09', 'step_level' => 3, 'basic_salary' => 112311.07],
            ['grade_level' => 'GL09', 'step_level' => 4, 'basic_salary' => 114126.99],
            ['grade_level' => 'GL09', 'step_level' => 5, 'basic_salary' => 115942.91],
            ['grade_level' => 'GL09', 'step_level' => 6, 'basic_salary' => 117758.83],
            ['grade_level' => 'GL09', 'step_level' => 7, 'basic_salary' => 119574.75],
            ['grade_level' => 'GL09', 'step_level' => 8, 'basic_salary' => 121390.67],
            ['grade_level' => 'GL09', 'step_level' => 9, 'basic_salary' => 123206.59],
            ['grade_level' => 'GL09', 'step_level' => 10, 'basic_salary' => 125022.51],
            ['grade_level' => 'GL09', 'step_level' => 11, 'basic_salary' => 126838.43],
            ['grade_level' => 'GL09', 'step_level' => 12, 'basic_salary' => 128654.35],
            ['grade_level' => 'GL09', 'step_level' => 13, 'basic_salary' => 130470.27],
            ['grade_level' => 'GL09', 'step_level' => 14, 'basic_salary' => 132286.19],
            ['grade_level' => 'GL09', 'step_level' => 15, 'basic_salary' => 134102.11],
            
            // GL10
            ['grade_level' => 'GL10', 'step_level' => 1, 'basic_salary' => 117490.91],
            ['grade_level' => 'GL10', 'step_level' => 2, 'basic_salary' => 119829.41],
            ['grade_level' => 'GL10', 'step_level' => 3, 'basic_salary' => 122167.91],
            ['grade_level' => 'GL10', 'step_level' => 4, 'basic_salary' => 124506.41],
            ['grade_level' => 'GL10', 'step_level' => 5, 'basic_salary' => 126844.91],
            ['grade_level' => 'GL10', 'step_level' => 6, 'basic_salary' => 129183.41],
            ['grade_level' => 'GL10', 'step_level' => 7, 'basic_salary' => 131521.91],
            ['grade_level' => 'GL10', 'step_level' => 8, 'basic_salary' => 133860.41],
            ['grade_level' => 'GL10', 'step_level' => 9, 'basic_salary' => 136198.91],
            ['grade_level' => 'GL10', 'step_level' => 10, 'basic_salary' => 138537.41],
            ['grade_level' => 'GL10', 'step_level' => 11, 'basic_salary' => 140875.91],
            ['grade_level' => 'GL10', 'step_level' => 12, 'basic_salary' => 143214.41],
            ['grade_level' => 'GL10', 'step_level' => 13, 'basic_salary' => 145552.91],
            ['grade_level' => 'GL10', 'step_level' => 14, 'basic_salary' => 147891.41],
            ['grade_level' => 'GL10', 'step_level' => 15, 'basic_salary' => 150229.91],
            
            // GL11 (11 steps)
            ['grade_level' => 'GL11', 'step_level' => 1, 'basic_salary' => 127726.70],
            ['grade_level' => 'GL11', 'step_level' => 2, 'basic_salary' => 130824.20],
            ['grade_level' => 'GL11', 'step_level' => 3, 'basic_salary' => 133921.70],
            ['grade_level' => 'GL11', 'step_level' => 4, 'basic_salary' => 137019.20],
            ['grade_level' => 'GL11', 'step_level' => 5, 'basic_salary' => 140116.70],
            ['grade_level' => 'GL11', 'step_level' => 6, 'basic_salary' => 143214.20],
            ['grade_level' => 'GL11', 'step_level' => 7, 'basic_salary' => 146311.70],
            ['grade_level' => 'GL11', 'step_level' => 8, 'basic_salary' => 149409.20],
            ['grade_level' => 'GL11', 'step_level' => 9, 'basic_salary' => 152506.70],
            ['grade_level' => 'GL11', 'step_level' => 10, 'basic_salary' => 155604.20],
            ['grade_level' => 'GL11', 'step_level' => 11, 'basic_salary' => 158701.70],
            
            // GL12 (11 steps)
            ['grade_level' => 'GL12', 'step_level' => 1, 'basic_salary' => 136658.39],
            ['grade_level' => 'GL12', 'step_level' => 2, 'basic_salary' => 139933.06],
            ['grade_level' => 'GL12', 'step_level' => 3, 'basic_salary' => 143207.73],
            ['grade_level' => 'GL12', 'step_level' => 4, 'basic_salary' => 146482.40],
            ['grade_level' => 'GL12', 'step_level' => 5, 'basic_salary' => 149757.07],
            ['grade_level' => 'GL12', 'step_level' => 6, 'basic_salary' => 153031.74],
            ['grade_level' => 'GL12', 'step_level' => 7, 'basic_salary' => 156306.41],
            ['grade_level' => 'GL12', 'step_level' => 8, 'basic_salary' => 159581.08],
            ['grade_level' => 'GL12', 'step_level' => 9, 'basic_salary' => 162855.75],
            ['grade_level' => 'GL12', 'step_level' => 10, 'basic_salary' => 166130.42],
            ['grade_level' => 'GL12', 'step_level' => 11, 'basic_salary' => 169405.09],
            
            // GL13 (11 steps)
            ['grade_level' => 'GL13', 'step_level' => 1, 'basic_salary' => 155631.30],
            ['grade_level' => 'GL13', 'step_level' => 2, 'basic_salary' => 159156.72],
            ['grade_level' => 'GL13', 'step_level' => 3, 'basic_salary' => 162682.14],
            ['grade_level' => 'GL13', 'step_level' => 4, 'basic_salary' => 166207.56],
            ['grade_level' => 'GL13', 'step_level' => 5, 'basic_salary' => 169732.98],
            ['grade_level' => 'GL13', 'step_level' => 6, 'basic_salary' => 173258.40],
            ['grade_level' => 'GL13', 'step_level' => 7, 'basic_salary' => 176783.82],
            ['grade_level' => 'GL13', 'step_level' => 8, 'basic_salary' => 180309.24],
            ['grade_level' => 'GL13', 'step_level' => 9, 'basic_salary' => 183834.66],
            ['grade_level' => 'GL13', 'step_level' => 10, 'basic_salary' => 187360.08],
            ['grade_level' => 'GL13', 'step_level' => 11, 'basic_salary' => 190885.50],
            
            // GL14 (9 steps)
            ['grade_level' => 'GL14', 'step_level' => 1, 'basic_salary' => 186400.30],
            ['grade_level' => 'GL14', 'step_level' => 2, 'basic_salary' => 191393.13],
            ['grade_level' => 'GL14', 'step_level' => 3, 'basic_salary' => 196385.96],
            ['grade_level' => 'GL14', 'step_level' => 4, 'basic_salary' => 201378.79],
            ['grade_level' => 'GL14', 'step_level' => 5, 'basic_salary' => 206371.62],
            ['grade_level' => 'GL14', 'step_level' => 6, 'basic_salary' => 211364.45],
            ['grade_level' => 'GL14', 'step_level' => 7, 'basic_salary' => 216357.28],
            ['grade_level' => 'GL14', 'step_level' => 8, 'basic_salary' => 221350.11],
            ['grade_level' => 'GL14', 'step_level' => 9, 'basic_salary' => 226342.94],
            
            // GL15 (9 steps)
            ['grade_level' => 'GL15', 'step_level' => 1, 'basic_salary' => 216181.56],
            ['grade_level' => 'GL15', 'step_level' => 2, 'basic_salary' => 222181.98],
            ['grade_level' => 'GL15', 'step_level' => 3, 'basic_salary' => 228182.40],
            ['grade_level' => 'GL15', 'step_level' => 4, 'basic_salary' => 234182.82],
            ['grade_level' => 'GL15', 'step_level' => 5, 'basic_salary' => 240183.24],
            ['grade_level' => 'GL15', 'step_level' => 6, 'basic_salary' => 246183.66],
            ['grade_level' => 'GL15', 'step_level' => 7, 'basic_salary' => 252184.08],
            ['grade_level' => 'GL15', 'step_level' => 8, 'basic_salary' => 258184.50],
            ['grade_level' => 'GL15', 'step_level' => 9, 'basic_salary' => 264184.92],
            
            // GL16 (9 steps)
            ['grade_level' => 'GL16', 'step_level' => 1, 'basic_salary' => 347314.50],
            ['grade_level' => 'GL16', 'step_level' => 2, 'basic_salary' => 358149.84],
            ['grade_level' => 'GL16', 'step_level' => 3, 'basic_salary' => 368985.18],
            ['grade_level' => 'GL16', 'step_level' => 4, 'basic_salary' => 379820.52],
            ['grade_level' => 'GL16', 'step_level' => 5, 'basic_salary' => 390655.86],
            ['grade_level' => 'GL16', 'step_level' => 6, 'basic_salary' => 401491.20],
            ['grade_level' => 'GL16', 'step_level' => 7, 'basic_salary' => 412326.54],
            ['grade_level' => 'GL16', 'step_level' => 8, 'basic_salary' => 423161.88],
            ['grade_level' => 'GL16', 'step_level' => 9, 'basic_salary' => 433997.22]
        ];
        
        $groupedData = collect($gradeLevelData)->groupBy('grade_level');

        foreach ($groupedData as $gradeLevelName => $steps) {
            $gradeLevel = GradeLevel::create([
                'name' => $gradeLevelName,
                'salary_scale_id' => $salaryScaleId,
                'description' => "Grade Level {$gradeLevelName} for Harmonized Academic Pay",
                // Add other grade level fields if necessary
            ]);

            foreach ($steps as $stepData) {
                Step::create([
                    'name' => 'Step ' . $stepData['step_level'],
                    'grade_level_id' => $gradeLevel->id,
                    // You might want to store basic_salary on the step level
                    // 'basic_salary' => $stepData['basic_salary'], 
                ]);
            }
        }
    }
}
