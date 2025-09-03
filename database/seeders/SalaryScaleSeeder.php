<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalaryScaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salaryScales = [
            [
                'acronym' => 'CONPSS',
                'full_name' => 'Consolidated Public Service Salary Structure',
                'sector_coverage' => 'Core Civil/Public Service workers (ministries, departments, agencies)',
                'grade_levels' => 'GL 01 – GL 17',
                'max_retirement_age' => '60 years',
                'max_years_of_service' => '35 years',
                'notes' => 'Standard for civil service'
            ],
            [
                'acronym' => 'CONHESS',
                'full_name' => 'Consolidated Health Salary Structure',
                'sector_coverage' => 'Health workers (nurses, pharmacists, lab scientists, etc.)',
                'grade_levels' => 'GL 01 – GL 15',
                'max_retirement_age' => '60 years',
                'max_years_of_service' => '35 years',
                'notes' => 'Covers non-doctor health professionals'
            ],
            [
                'acronym' => 'CONMESS',
                'full_name' => 'Consolidated Medical Salary Structure',
                'sector_coverage' => 'Medical Doctors & Dentists',
                'grade_levels' => 'GL 01 – GL 07 (Consultant levels)',
                'max_retirement_age' => '70 years (Professors/Consultants in Tertiary Hospitals) / 65 (others)',
                'max_years_of_service' => '35 years',
                'notes' => 'Extended age for consultants/professors in teaching hospitals'
            ],
            [
                'acronym' => 'CONUASS',
                'full_name' => 'Consolidated University Academic Salary Structure',
                'sector_coverage' => 'University lecturers (Professors, Readers, etc.)',
                'grade_levels' => 'Lecturer I – Professor',
                'max_retirement_age' => '70 years (Professors) / 65 years (others)',
                'max_years_of_service' => '35 years',
                'notes' => 'Professors retire at 70'
            ],
            [
                'acronym' => 'CONTISS II',
                'full_name' => 'Consolidated Tertiary Institutions Salary Structure',
                'sector_coverage' => 'Non-academic staff in Universities/Polytechnics/Colleges',
                'grade_levels' => 'CONTISS 01 – CONTISS 15',
                'max_retirement_age' => '65 years',
                'max_years_of_service' => '35 years',
                'notes' => 'For administrative/technical staff'
            ],
            [
                'acronym' => 'CONRAISS',
                'full_name' => 'Consolidated Research & Allied Institutions Salary Structure',
                'sector_coverage' => 'Research Institutes staff',
                'grade_levels' => 'GL 01 – GL 15',
                'max_retirement_age' => '65 years (Directors/Researchers)',
                'max_years_of_service' => '35 years',
                'notes' => 'Similar to university structures'
            ],
            [
                'acronym' => 'CONJUSS',
                'full_name' => 'Consolidated Judiciary Staff Salary Structure',
                'sector_coverage' => 'Judicial staff (registrars, clerks, etc.)',
                'grade_levels' => 'GL 01 – GL 17',
                'max_retirement_age' => '65 years',
                'max_years_of_service' => '35 years',
                'notes' => 'Judges have special rules'
            ],
            [
                'acronym' => 'JUSS (Judges)',
                'full_name' => 'Judicial Officers Salary Structure',
                'sector_coverage' => 'Judges, Justices, Magistrates',
                'grade_levels' => 'Special scales',
                'max_retirement_age' => '70 years (Supreme/Appeal Court Justices) / 65 years (others)',
                'max_years_of_service' => '35 years',
                'notes' => 'Special provision in constitution'
            ],
            [
                'acronym' => 'CONAFSS',
                'full_name' => 'Consolidated Armed Forces Salary Structure',
                'sector_coverage' => 'Nigerian Army, Navy, Airforce personnel',
                'grade_levels' => 'Ranks-based',
                'max_retirement_age' => '60 years (varies by rank)',
                'max_years_of_service' => '35 years',
                'notes' => 'Officers may have earlier retirement if not promoted'
            ],
            [
                'acronym' => 'CONPASS',
                'full_name' => 'Consolidated Paramilitary Salary Structure',
                'sector_coverage' => 'Immigration, Customs, Civil Defence, FRSC, Prisons, Fire Service',
                'grade_levels' => 'Rank-based (Level 3–17)',
                'max_retirement_age' => '60 years',
                'max_years_of_service' => '35 years',
                'notes' => 'Paramilitary officers'
            ],
            [
                'acronym' => 'CONTOPSAL',
                'full_name' => 'Consolidated Top Public Office Holders’ Salary Structure',
                'sector_coverage' => 'President, VP, Governors, Legislators, Ministers, etc.',
                'grade_levels' => 'N/A (political offices)',
                'max_retirement_age' => 'Depends on tenure',
                'max_years_of_service' => 'N/A',
                'notes' => 'Not based on civil service rules'
            ],
            [
                'acronym' => 'CONSS',
                'full_name' => 'Consolidated Secretarial & Stenographic Salary Structure',
                'sector_coverage' => 'Secretarial cadre staff',
                'grade_levels' => 'GL 01 – GL 14',
                'max_retirement_age' => '60 years',
                'max_years_of_service' => '35 years',
                'notes' => 'Specialized cadre'
            ],
            [
                'acronym' => 'CONICTSS',
                'full_name' => 'Consolidated ICT Salary Structure',
                'sector_coverage' => 'ICT/Computer cadre in public service',
                'grade_levels' => 'GL 01 – GL 17',
                'max_retirement_age' => '60 years',
                'max_years_of_service' => '35 years',
                'notes' => 'For ICT professionals'
            ],
            [
                'acronym' => 'CONPOSS',
                'full_name' => 'Consolidated Police Salary Structure',
                'sector_coverage' => 'Nigerian Police Force',
                'grade_levels' => 'Constable – IG',
                'max_retirement_age' => '60 years (or rank-specific)',
                'max_years_of_service' => '35 years',
                'notes' => 'Compulsory retirement varies by rank'
            ],
        ];

        DB::table('salary_scales')->insert($salaryScales);
    }
}
