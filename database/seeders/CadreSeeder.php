<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cadre;

class CadreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cadres = [
            ['name' => 'Administrative', 'description' => 'Policy and admin officers'],
            ['name' => 'Professional', 'description' => 'Doctors, Engineers, Lawyers, etc.'],
            ['name' => 'Executive', 'description' => 'Programme implementers'],
            ['name' => 'Secretarial/Clerical', 'description' => 'Secretaries, Clerks'],
            ['name' => 'Technical', 'description' => 'Technical officers'],
            ['name' => 'Support/General Duties', 'description' => 'Drivers, Messengers, Cleaners'],
            ['name' => 'Medical & Health', 'description' => 'Doctors, Nurses, Pharmacists'],
            ['name' => 'Teaching/Academic', 'description' => 'Lecturers, Teachers'],
            ['name' => 'Legal', 'description' => 'Lawyers, State Counsel'],
            ['name' => 'Accounting/Audit', 'description' => 'Accountants, Auditors'],
            ['name' => 'Scientific', 'description' => 'Researchers, ICT officers'],
            ['name' => 'Agricultural', 'description' => 'Agric officers, Vet doctors'],
            ['name' => 'Information/Communication', 'description' => 'PROs, Journalists'],
            ['name' => 'Library', 'description' => 'Librarians, Archivists'],
            ['name' => 'Security/Protocol', 'description' => 'Security & protocol staff'],
        ];

        foreach ($cadres as $cadre) {
            Cadre::create($cadre);
        }
    }
}