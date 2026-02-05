<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('ranks')->truncate();
        Schema::enableForeignKeyConstraints();

        $ranks = [
            ['id' => 1, 'name' => 'A C C O ADMIN'],
            ['id' => 2, 'name' => 'A C W S'],
            ['id' => 3, 'name' => 'A E O ACCT'],
            ['id' => 4, 'name' => 'A S T O'],
            ['id' => 5, 'name' => 'A W S'],
            ['id' => 6, 'name' => 'ASST CHIEF SCIENTIFIC OFFICER'],
            ['id' => 7, 'name' => 'Asst Chief Work Sup'],
            ['id' => 8, 'name' => 'Asst Director'],
            ['id' => 9, 'name' => 'C C O'],
            ['id' => 10, 'name' => 'C E O ACCT'],
            ['id' => 11, 'name' => 'C M D'],
            ['id' => 12, 'name' => 'C M R'],
            ['id' => 13, 'name' => 'C O'],
            ['id' => 14, 'name' => 'C Plumber'],
            ['id' => 15, 'name' => 'C T A M'],
            ['id' => 16, 'name' => 'C T ASST ELECt'],
            ['id' => 17, 'name' => 'C T Asst'],
            ['id' => 18, 'name' => 'C T O'],
            ['id' => 19, 'name' => 'C W S'],
            ['id' => 20, 'name' => 'Chairman'],
            ['id' => 21, 'name' => 'Chemical charger'],
            ['id' => 22, 'name' => 'Chief Driver'],
            ['id' => 23, 'name' => 'Chief Elect'],
            ['id' => 24, 'name' => 'Chief Executive Off'],
            ['id' => 25, 'name' => 'Chief Store Officer'],
            ['id' => 26, 'name' => 'Chief Works Supt'],
            ['id' => 27, 'name' => 'Cleaner'],
            ['id' => 28, 'name' => 'Confidential Sec 1'],
            ['id' => 29, 'name' => 'Contract'],
            ['id' => 30, 'name' => 'Cumputer'],
            ['id' => 31, 'name' => 'Director'],
            ['id' => 32, 'name' => 'Electrical Engr 2'],
            ['id' => 33, 'name' => 'Electrician'],
            ['id' => 34, 'name' => 'Foreman'],
            ['id' => 35, 'name' => 'H T O ELECT'],
            ['id' => 36, 'name' => 'High Works Supt'],
            ['id' => 37, 'name' => 'Labourer'],
            ['id' => 38, 'name' => 'M Readaer'],
            ['id' => 39, 'name' => 'M/DRIVER'],
            ['id' => 40, 'name' => 'Meter Reader'],
            ['id' => 41, 'name' => 'Motor Driver'],
            ['id' => 42, 'name' => 'O S O 1'],
            ['id' => 43, 'name' => 'P A'],
            ['id' => 44, 'name' => 'P C E 2'],
            ['id' => 45, 'name' => 'P C O 1'],
            ['id' => 46, 'name' => 'P E O'],
            ['id' => 47, 'name' => 'P E O 1'],
            ['id' => 48, 'name' => 'P L O 2'],
            ['id' => 49, 'name' => 'P S O 1'],
            ['id' => 50, 'name' => 'P T O'],
            ['id' => 51, 'name' => 'P T O 1 MECH'],
            ['id' => 52, 'name' => 'P o'],
            ['id' => 53, 'name' => 'P w S'],
            ['id' => 54, 'name' => 'PO'],
            ['id' => 55, 'name' => 'Plumbber'],
            ['id' => 56, 'name' => 'Prin Civil Eng Grd 1'],
            ['id' => 57, 'name' => 'Prin Exet Off Acct'],
            ['id' => 58, 'name' => 'Prin Stor Officer 1'],
            ['id' => 59, 'name' => 'Prin Works Supt'],
            ['id' => 60, 'name' => 'R TECH ASST GR'],
            ['id' => 61, 'name' => 'S C ENG'],
            ['id' => 62, 'name' => 'S C O'],
            ['id' => 63, 'name' => 'S D P O'],
            ['id' => 64, 'name' => 'S E O'],
            ['id' => 65, 'name' => 'S E O ACC'],
            ['id' => 66, 'name' => 'S L T'],
            ['id' => 67, 'name' => 'S M'],
            ['id' => 68, 'name' => 'S M 1'],
            ['id' => 69, 'name' => 'S M R'],
            ['id' => 70, 'name' => 'S M/READER'],
            ['id' => 71, 'name' => 'S O'],
            ['id' => 72, 'name' => 'S O 1'],
            ['id' => 73, 'name' => 'S S M'],
            ['id' => 74, 'name' => 'S W W Attendat'],
            ['id' => 75, 'name' => 'STORE KEEPER'],
            ['id' => 76, 'name' => 'Saeniop Officer'],
            ['id' => 77, 'name' => 'Scientifc Officer Gr'],
            ['id' => 78, 'name' => 'Scientific Officer 2'],
            ['id' => 79, 'name' => 'Senior Craftsman'],
            ['id' => 80, 'name' => 'Senior Foreman'],
            ['id' => 81, 'name' => 'Senior Tech Asst g 1'],
            ['id' => 82, 'name' => 'Snr Foreman'],
            ['id' => 83, 'name' => 'T A 1'],
            ['id' => 84, 'name' => 'W SUP'],
            ['id' => 85, 'name' => 'W/man'],
            ['id' => 86, 'name' => 'WC'],
            ['id' => 87, 'name' => 'ata processing Asst'],
            ['id' => 88, 'name' => 'chief clarical officer'],
            ['id' => 89, 'name' => 'nil'],
            ['id' => 90, 'name' => 'or Commercial Offi 65D'],
            ['id' => 91, 'name' => 'plumber'],
            ['id' => 92, 'name' => 'sst Chief Works su'],
            ['id' => 93, 'name' => 't Technical Officer'],
        ];

        foreach ($ranks as $rank) {
             DB::table('ranks')->updateOrInsert(
                ['id' => $rank['id']],
                ['name' => $rank['name']]
            );
        }
    }
}
